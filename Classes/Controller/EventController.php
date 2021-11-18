<?php

namespace Priorist\EdmTypo3\Controller;

use Psr\Http\Message\ResponseInterface;

class EventController extends AbstractController
{
	/**
	 * List all upcoming events
	 */
	public function listAction(): ResponseInterface
	{
		var_dump($this->client);

		// Assign plugin settings from TypoScript to view
		$settings = $this->settings;
		$this->view->assign('settings', $settings);

		// Assign content object to view
		$this->view->assign('data', $this->configurationManager->getContentObject()->data);

		// Get filter settings from backend
		$filters = $this->getPluginFilter();

		$eventParams = [
			'exclude_non_bookable_children' => 'true',
			'serializer_format' => 'website_list'
		];

		$eventParams = $this->getListFilterForEventParams($filters, $eventParams);

		$limit = $filters['limit'] ?: '1000';
		$showAll = $filters['showAll'];

		try {
			if ($showAll === '1') {
				$events = $this->getClient()->getRestClient()->fetchCollection('events', $eventParams); // TODO: Methode in AIS SDK für findAll
			} else {
				$events = $this->getClient()->event->findUpcoming($eventParams);
				$events = $this->sanitizeEvents($events);
			}
		} catch (\Throwable $e) {
			$this->view->assign('internalError', true);
		}

		$groupedEvents = array_splice($this->getEventsGroupedByEventBase($events), 0, $limit);

		// Assign events from EDM to view
		$this->view->assign('groupedEvents', $groupedEvents);

		return $this->htmlResponse();
	}

	/**
	 * List all upcoming events for search
	 */
	public function searchAction(): ResponseInterface
	{
		$limit = $this->settings['eventSearchLimit'] ?: '1000';

		$eventParams = [
			'page_size' => $limit,
			'exclude_non_bookable_children' => 'true',
			'serializer_format' => 'website_list'
		];

		try {
			$events = $this->getClient()->event->findUpcoming($eventParams);
		} catch (\Throwable $e) {
			$this->view->assign('internalError', true);
		}

		$sanitizedEvents = $this->sanitizeEvents($events);
		$groupedEvents = $this->getEventsGroupedByEventBase($sanitizedEvents);
		$categoryTree = $this->createCategoryTreeByEvents($groupedEvents);

		$this->view->assign('filterData', $this->prepareFilterDataForEvents($sanitizedEvents, $categoryTree));
		$this->view->assign('groupedEvents', $groupedEvents);
		$this->view->assign('categoryTree', $categoryTree);

		return $this->htmlResponse();
	}

	/**
	 * Detailed view of an event
	 */
	public function detailAction(): ResponseInterface
	{
		if ($_GET['showAll'] === 'true') {
			$showAll = true;
		} else {
			$showAll = false;
		}

		$this->abstractDetailAction($showAll);

		return $this->htmlResponse();
	}

	protected function abstractDetailAction(bool $showAll)
	{
		$settings = $this->settings;
		$this->view->assign('data', $this->configurationManager->getContentObject()->data);

		if (isset($_GET['eventId']) && $this->request->hasArgument('eventBaseSlug')) {
			$this->getEventsBasedOnId($settings, $showAll);
		} else if ($this->request->hasArgument('eventBaseSlug')) {
			$this->getEventsBasedOnSlug($settings, $showAll);
		}
	}

	protected function createCategoryTreeByEvents(array $events)
	{
		$categoryTree = array();

		foreach ($events as $event) {
			foreach ($event['categories'] as $category) {
				if ($category['parent_category']) {
					if (!isset($categoryTree[$category['parent_category']])) {
						$categoryTree[$category['parent_category']] = array();
						$categoryTree[$category['parent_category']]['name'] = $category['parent_category_name'];
						$categoryTree[$category['parent_category']]['categories'] = array();
					}
					if (!isset($categoryTree[$category['parent_category']]['categories'][$category['id']])) {
						$categoryTree[$category['parent_category']]['categories'][$category['id']] = $category['name'];
						ksort($categoryTree[$category['parent_category']]['categories']);
					}
				} else if (!isset($categoryTree[$category['id']])) {
					$categoryTree[$category['id']]['name'] = $category['name'];
					$categoryTree[$category['id']]['categories'] = array();
				}
			}
		}

		uasort($categoryTree, function ($a, $b) {
			return $a['name'] <=> $b['name'];
		});

		return $categoryTree;
	}

	/**
	 * Prepare filter data for events
	 */
	protected function prepareFilterDataForEvents(array $events, array $categoryTree, array $locations = [])
	{
		$categoryData = array();
		$formatData = array();
		$locationData = array();
		$allTitles = "";
		$allContents = "";

		foreach ($events as $event) {
			// fill categories
			foreach ($categoryTree as $key => $parentCategory) {
				if (!isset($categoryData[$key])) {
					$categoryData[$key]['name'] = $parentCategory['name'];
					$categoryData[$key]['id'] = $key;

					foreach ($parentCategory['categories'] as $key => $childCategory) {
						if (!isset($categoryData[$key])) {
							$categoryData[$key]['name'] = $childCategory;
							$categoryData[$key]['id'] = $key;
						}
					}
				}
			}

			// fill formats
			if (!isset($formatData[$event['event_base']['event_type']])) {
				$formatData[$event['event_base']['event_type']]['name'] = $event['event_base']['event_type_name'];
				$formatData[$event['event_base']['event_type']]['id'] = $event['event_base']['event_type'];
			}

			// fill locations
			if (!isset($locationData[$event['location']['id']])) {
				$locationData[$event['location']['id']]['name'] = $event['location']['name'];
				$locationData[$event['location']['id']]['id'] = $event['location']['id'];
			}

			// fill title & summary
			$allTitles .= strip_tags($event['meta']['event_base_name']) . " ";
			$allContents .= strip_tags($event['event_base']['summary']) . " ";
		}

		$search = array('"', "'", "/\r|\n/");
		$replace = array("", "", "");
		$allTitles = str_replace($search, $replace, $allTitles);
		$allContents = str_replace($search, $replace, $allContents);

		// Sort formats alphabetically
		asort($formatData);

		$cities = $this->prepareCitiesForFilterData($locationData, $locations);

		$filterData = array(
			'category' => array(
				'labelSingular' => 'Thema',
				'labelPlural' => 'Themen',
				'data' => array_values($categoryData),
			),
			'format' => array(
				'labelSingular' => 'Format',
				'labelPlural' => 'Formate',
				'data' => array_values($formatData),
			),
			'location' => array(
				'labelSingular' => 'Ort',
				'labelPlural' => 'Orte',
				'data' => array_values($locationData),
				'cities' => array_values(array_unique($cities)),
			),
			'date' => array(
				'labelSingular' => 'Zeitraum',
				'labelPlural' => 'Zeitraum',
			),
			'title' => array(
				'labelSingular' => 'Im Titel',
				'labelPlural' => 'Titel',
			),
			'content' => array(
				'labelSingular' => 'Im Inhalt',
				'labelPlural' => 'Inhalt',
			),
			'allTitles' => strtolower(htmlentities($allTitles)),
			'allContents' => strtolower(htmlentities($allContents)),
		);

		return $filterData;
	}

	/**
	 * Only add relevant events
	 */
	protected function sanitizeEvents($events)
	{
		$sanitizedEvents = [];
		$today = strtotime(date('Y-m-d'));

		// only add events that have price information and that have not started yet
		foreach ($events as $key => $event) {
			if (strtotime($event['first_day']) > $today && !empty($event['prices'])) {
				$sanitizedEvents[] = $event;
			}
		}

		return $sanitizedEvents;
	}

	/**
	 * Aggregate events to event bases
	 */
	protected function getEventsGroupedByEventBase(array $events)
	{
		$eventBases = [];
		$tempPriceArray = [];
		$priceCountArray = [];

		foreach ($events as $event) {
			$eventBaseId = $event['event_base']['id'];
			$priceCount = 0;

			if (!isset($eventBases[$eventBaseId])) {
				$eventBases[$eventBaseId] = $event['event_base'];
				unset($eventBases[$eventBaseId]['events']);
			}

			unset($event['event_base']);

			$eventBases[$eventBaseId]['events'][] = $event;

			// add locations
			if (!isset($eventBases[$eventBaseId]['locations'][$event['location']['id']])) {
				$eventBases[$eventBaseId]['locations'][$event['location']['id']] = array(
					'name' => $event['location']['name'],
					'id' => $event['location']['id'],
					'city' => explode(' - ', $event['location']['name'])[0],
				);
			}

			// set first day and start dates
			if (!isset($eventBases[$eventBaseId]['first_day']) || strtotime($event['first_day']) < $eventBases[$eventBaseId]['first_day']) {
				$eventBases[$eventBaseId]['first_day'] = $event['first_day'];
			}
			$eventBases[$eventBaseId]['start_dates'][] = $event['first_day'];

			// set min and max dates
			if (!isset($eventBases[$eventBaseId]['min_dates']) || count($event['dates']) < $eventBases[$eventBaseId]['min_dates']) {
				$eventBases[$eventBaseId]['min_dates'] = count($event['dates']);
			}
			if (!isset($eventBases[$eventBaseId]['max_dates']) || count($event['dates']) > $eventBases[$eventBaseId]['min_dates']) {
				$eventBases[$eventBaseId]['max_dates'] = count($event['dates']);
			}

			// prepare price data
			foreach ($event['prices'] as $price) {
				$tempPriceArray[$eventBaseId][] = $price['amount'];
				$priceCount++;
			}

			if (!isset($priceCountArray[$eventBaseId])) {
				$priceCountArray[$eventBaseId] = $priceCount;
			} else {
				$priceCountArray[$eventBaseId] += $priceCount;
			}

			sort($tempPriceArray[$eventBaseId]);
		}

		foreach ($eventBases as $key => &$eventBase) {
			// check if event has new tag
			if (is_array($eventBase['tags']) && in_array('Neu', $eventBase['tags'])) {
				$eventBase['is_new'] = true;
			} else {
				$eventBase['is_new'] = false;
			}

			// set lowest price
			if (!isset($eventBase['lowest_price'])) {
				$eventBase['lowest_price'] = $tempPriceArray[$key][0];
			}

			// set price count
			if (!isset($eventBase['price_count'])) {
				$eventBase['price_count'] = $priceCountArray[$key];
			}
		}

		return $eventBases;
	}

	protected function getListFilterForEventParams(array $filters, array $eventParams)
	{
		$eventIds = $filters['eventIds'];
		$eventBaseIds = $filters['eventBaseIds'];
		$categoryIds = $filters['categoryIds'];
		$eventTypeId = $filters['eventTypeId'];
		$limit = $filters['limit'] ?: '1000';
		$context = $filters['context'];
		$location = $filters['location'];
		$isBookable = $filters['isBookable'];
		$dateFrom = $filters['dateFrom'];
		$dateTo = $filters['dateTo'];

		// Apply filters if any are set
		if ($filters) {
			if ($eventIds) {
				$eventParams['id'] = explode(',', $eventIds);
			}
			if ($categoryIds) {
				$eventParams['event_base__categories'] = explode(',', $categoryIds);
			}
			if ($eventBaseIds) {
				$eventParams['event_base'] = explode(',', $eventBaseIds);
			}
			if ($limit) {
				// Increase limit manually to pull enough events to fulfill limit requirements
				// for event bases in 99.9% of cases without pulling all events
				$eventParams['page_size'] = $limit + 15;
			}
			if ($eventTypeId && $eventTypeId !== 0) {
				$eventParams['event_base__event_type'] = explode(',', $eventTypeId);
			}
			if ($context && $context !== 0) {
				$eventParams['event_base__context'] = $context;
			}
			if ($location && $location !== 0) {
				$eventParams['location'] = explode(',', $location);
			}
			if ($isBookable) {
				$eventParams['is_bookable'] = true;
			}
			if ($dateFrom) {
				$eventParams['first_day__gte'] = $dateFrom;
			}
			if ($dateTo) {
				$eventParams['first_day__lte'] = $dateTo;
			}
		}

		return $eventParams;
	}

	protected function getEventsBasedOnId(array $settings, bool $showAll)
	{
		$eventId = $_GET['eventId'];
		$eventBaseSlug = $this->request->getArgument('eventBaseSlug');

		try {
			$eventBase = $this->getClient()->eventBase->findBySlug($eventBaseSlug);
		} catch (\Throwable $e) {
			$this->view->assign('internalError', true);
			return;
		}

		if ($eventBase !== NULL) {
			$eventBaseId = $eventBase['id'];
			$eventParams = [
				'expand' => '~all,event_base.contact_person,event_base.group_children',
			];

			// Get events from EDM, transform them to array and assign the results to FE
			try {
				$currentEvent = $this->getClient()->event->findById($eventId, $eventParams);
			} catch (\Throwable $e) {
				var_dump($e->getMessage());
				$this->view->assign('internalError', true);
				return;
			}

			$events = $this->getEventsFromEventBase($eventBaseId, $showAll);

			$eventCities = $this->getLocationCities($currentEvent, true);
			if ($showAll === true) {
				$sanitizedEvents = $events;
			} else {
				$sanitizedEvents = $this->sanitizeEvents($events);
			}

			$currentEvent = $this->prepareEventPriceData($currentEvent);

			if (!isset($sanitizedEvents)) {
				$this->view->assign('noEventAvailable', true);
			}

			$this->view->assign('cities', $eventCities);
			$this->view->assign('currentEvent', $currentEvent);
			$this->view->assign('events', $sanitizedEvents);
			$this->view->assign('eventBase', $eventBase);
		} else {
			$this->redirectTo404($settings);
		}
	}

	protected function getEventsBasedOnSlug(array $settings, bool $showAll)
	{
		// Get 'eventBaseSlug' parameter from URL
		$eventBaseSlug = $this->request->getArgument('eventBaseSlug');

		// Get event base from EDM and assign it to FE
		$eventBase = $this->getEventBaseFromSlug($eventBaseSlug);

		if ($eventBase !== NULL) {
			$eventBaseId = $eventBase['id'];

			// Get events from EDM, transform them to array and assign the results to FE
			$events = $this->getEventsFromEventBase($eventBaseId, $showAll);

			$eventArray = $events->toArray();
			$events = $eventArray['results'];

			if ($showAll === true) {
				$sanitizedEvents = $events;
			} else {
				$sanitizedEvents = $this->sanitizeEvents($events);
			}

			$eventCities = $this->getLocationCities($events, true);

			foreach ($sanitizedEvents as &$event) {
				$event = $this->prepareEventPriceData($event);
			}

			if (count($sanitizedEvents) == 0) {
				$this->view->assign('noEventAvailable', true);
			}

			$this->view->assign('cities', $eventCities);
			$this->view->assign('events', $sanitizedEvents);
			$this->view->assign('eventBase', $eventBase);
		} else {
			$this->redirectTo404($settings);
		}
	}

	protected function getEventBaseFromSlug(string $slug)
	{
		try {
			$eventBase = $this->getClient()->eventBase->findBySlug($slug);
		} catch (\Throwable $e) {
			$this->view->assign('internalError', true);
			return;
		}

		return $eventBase;
	}

	protected function getEventsFromEventBase(int $eventBaseId, bool $showAll) {
		$eventParams = [
			'expand' => '~all,event_base.contact_person,event_base.group_children',
			'event_base' => $eventBaseId
		];

		// Get events from EDM, transform them to array and assign the results to FE
		try {
			if ($showAll === true) {
				$events = $this->getClient()->getRestClient()->fetchCollection('events', $eventParams); // TODO: Methode in AIS SDK für findAll
			} else {
				$events = $this->getClient()->event->findUpcoming($eventParams);
			}
		} catch (\Throwable $e) {
			$this->view->assign('internalError', true);
			return;
		}

		return $events;
	}

	protected function prepareEventPriceData(array $event)
	{
		$priceCount = 0;

		if (isset($event['prices'])) {
			// sort prices ascending from lowest to highest amount
			usort($event['prices'], function ($item1, $item2) {
				return $item1['amount'] <=> $item2['amount'];
			});

			// add lowest price to event object
			$event['lowest_price'] = $event['prices'][0]['amount'];
			$priceCount = count($event['prices']);
		}

		$event['price_count'] = $priceCount;

		return $event;
	}

	protected function redirectTo404($settings)
	{
		// redirect to 404 error page if no event in EDM is found
		$pageUid = intval($settings['pageuids']['404'], 10);
		$uriBuilder = $this->uriBuilder;
		$uri = $uriBuilder
			->setTargetPageUid($pageUid)
			->build();
		$this->redirectToUri($uri, 0, 404);
	}

	protected function prepareCitiesForFilterData($locationData = [], $locations = [])
	{
		$cities = [];
		foreach ($locationData as $location) {
			$cities[] = $location['address']['city'];
		}

		// Sort cities alphabetically
		asort($cities);

		return $cities;
	}

	protected function getLocationCities($events, $isResultsArray = false)
	{
		$cities = [];

		foreach ($events as $event) {
			$cities[] = $event['location']['address']['city'];
		}

		return $cities;
	}
}
