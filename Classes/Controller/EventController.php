<?php

namespace Priorist\EdmTypo3\Controller;

use Priorist\EDM\Client\Rest\ClientException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class EventController extends AbstractController
{
	/**
	 * List all upcoming events
	 */
	public function listAction(): ResponseInterface
	{
		// Assign plugin settings from TypoScript to view
		$settings = $this->settings;
		$this->view->assign('settings', $settings);

		// Assign content object to view
		$this->view->assign('data', $this->request->getAttribute('currentContentObject')->data);

		// Get filter settings from backend
		$filters = $this->getPluginFilter();

		$eventParams = [
			'exclude_non_bookable_children' => 'true',
			'serializer_format' => 'website_list'
		];

		$eventParams = $this->getListFilterForEventParams($filters, $eventParams);

		$limit = $this->getFilterValue($filters, 'limit', '1000');
		$showAll = $this->getFilterValue($filters, 'showAll', '0');

		try {
			if ($showAll === '1') {
				$events = $this->getClient()->getRestClient()->fetchCollection('events', $eventParams); // TODO: Methode in AIS SDK für findAll
				$eventsArray = $events->toArray();
				$events = $eventsArray['results'];
			} else {
				$events = $this->getClient()->event->findUpcoming($eventParams);
				$events = $this->sanitizeEvents($events);
			}

			$groupedEvents = array_splice($this->getEventsGroupedByEventBase($events), 0, $limit);

			// Assign events from EDM to view
			$this->view->assign('groupedEvents', $groupedEvents);
		} catch (ClientException $e) {
			if ($e->getCode() === 401) {
				$this->resetAccessToken();
				$this->view->assign('internalError', true);
			}
		} catch (Throwable $e) {
			$this->view->assign('internalError', true);
		}

		return $this->htmlResponse();
	}

	/**
	 * List all upcoming events for search
	 */
	public function searchAction(): ResponseInterface
	{
		$limit = $this->settings['eventSearchLimit'] ?: '1000';
		$showAllEvents = $this->settings['customConditions']['eventTypes']['showAllEvents'] !== '{$plugin.tx_edmtypo3.customConditions.eventTypes.showAllEvents}';

		// Get filter settings from backend
		$filters = $this->getPluginFilter();

		$eventParams = [
			'page_size' => $limit,
			'exclude_non_bookable_children' => 'true',
			'serializer_format' => 'website_list'
		];

		$eventParams = $this->getListFilterForEventParams($filters, $eventParams);

		try {
			$events = $this->getClient()->event->findUpcoming($eventParams);

			// Get events without dates with specific event type
			if ($showAllEvents) {
				$eventParams = array_merge($eventParams, [
					'event_base__event_type' => $this->settings['customConditions']['eventTypes']['showAllEvents'],
					'status' => ['OFFERED', 'TAKES_PLACE'],
					'is_public' => 'true'
				]);
				$ongoingEvents = $this->getClient()->getRestClient()->fetchCollection('events', $eventParams);
			}

			if (isset($events)) {
				$events = $events->toArray();

				if (!is_null($ongoingEvents)) {
					// Merge upcoming events with events without dates and specific event type
					$ongoingEvents = $ongoingEvents->toArray();
					$events = array_merge($events['results'], $ongoingEvents['results']);
				} else {
					$events = $events['results'];
				}

				$sanitizedEvents = $this->sanitizeEvents($events);
				$groupedEvents = $this->getEventsGroupedByEventBase($sanitizedEvents);
				$categoryTree = $this->createCategoryTreeByEvents($groupedEvents);

				// Sort grouped events by first day, placing events without date on top
				usort($groupedEvents, function ($item1, $item2) {
					return $item1['first_day'] <=> $item2['first_day'];
				});

				$this->view->assign('filterData', $this->prepareFilterDataForEvents($sanitizedEvents, $categoryTree));
				$this->view->assign('groupedEvents', $groupedEvents);
				$this->view->assign('categoryTree', $categoryTree);
			}
		} catch (ClientException $e) {
			print($e->getMessage());
			if ($e->getCode() === 401) {
				$this->resetAccessToken();
				$this->view->assign('internalError', true);
			}
		} catch (Throwable $e) {
			print($e->getMessage());
			$this->view->assign('internalError', true);
		} finally {
			return $this->htmlResponse();
		}
	}

	/**
	 * Detailed view of an event
	 */
	public function detailAction(): ResponseInterface
	{
		$showAll = false;

		if (isset($_GET['showAll']) && $_GET['showAll'] === 'true') {
			$showAll = true;
		}

		$this->abstractDetailAction($showAll);

		return $this->htmlResponse();
	}

	protected function abstractDetailAction(bool $showAll)
	{
		$settings = $this->settings;
		$this->view->assign('data', $this->request->getAttribute('currentContentObject')->data);

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
		$lecturerData = array();
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
			$allContents .= strip_tags($event['event_base']['summary']) . " " . strip_tags(implode(" ", $event['event_base']['tags'])) . " ";

			// fill lecturers
			foreach ($event['lecturers'] as $lecturer) {
				if (!isset($lecturerData[$lecturer['id']])) {
					$lecturerData[$lecturer['id']]['last_name'] = $lecturer['last_name'];
					$lecturerData[$lecturer['id']]['name'] = $lecturer['first_name'] . ' ' . $lecturer['last_name'];
					$lecturerData[$lecturer['id']]['gender'] = $lecturer['gender'];
					$lecturerData[$lecturer['id']]['title'] = $lecturer['title'];
					$lecturerData[$lecturer['id']]['id'] = $lecturer['id'];
				}
			}

			uasort($lecturerData, function ($a, $b) {
				return $a['last_name'] <=> $b['last_name'];
			});
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
			'type' => array(
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
			'lecturer' => array(
				'labelSingular' => 'Dozent',
				'labelPlural' => 'Dozenten',
				'data' => array_values($lecturerData),
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
		$showAllEventsArray = explode(',', $this->settings['customConditions']['eventTypes']['showAllEvents']);

		// only add events that have price information and that have not started yet
		foreach ($events as $key => $event) {
			if ((strtotime($event['first_day']) > $today) && !empty($event['prices']) && empty($event['archived_at']) || (in_array(strval($event['event_base']['event_type']), $showAllEventsArray))) {
				$sanitizedEvents[] = $event;
			}
		}

		if (!empty($sanitizedEvents)) {
			$sanitizedEvents = $this->verifyEventPrices($sanitizedEvents);
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

			// add format
			if (!isset($eventBases[$eventBaseId]['format'])) {
				$eventBases[$eventBaseId]['format'] = $event['format'];
			}

			// add locations
			if (!isset($eventBases[$eventBaseId]['locations'][$event['location']['id']])) {
				$eventBases[$eventBaseId]['locations'][$event['location']['id']] = array(
					'name' => $event['location']['name'],
					'id' => $event['location']['id'],
					'city' => explode(' - ', $event['location']['name'])[0],
				);
			}

			// add lecturers
			if (isset($event['lecturers']) && is_array($event['lecturers'])) {
				foreach ($event['lecturers'] as $lecturer) {
					if (is_array($lecturer) && !isset($eventBases[$eventBaseId]['lecturers'][$lecturer['id']])) {
						$eventBases[$eventBaseId]['lecturers'][$lecturer['id']] = array(
							'name' => $lecturer['first_name'] . ' ' . $lecturer['last_name'],
							'id' => $lecturer['id'],
						);
					}
				}
			}

			// set first day and start dates
			if (!isset($eventBases[$eventBaseId]['first_day']) || strtotime($event['first_day']) < strtotime($eventBases[$eventBaseId]['first_day'])) {
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

			if ($tempPriceArray && $tempPriceArray[$eventBaseId]) {
				sort($tempPriceArray[$eventBaseId]);
			}
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

	protected function getListFilterForEventParams(array $filters = NULL, array $eventParams)
	{
		$eventIds = $this->getFilterValue($filters, 'eventIds');
		$eventBaseIds = $this->getFilterValue($filters, 'eventBaseIds');
		$categoryIds = $this->getFilterValue($filters, 'categoryIds');
		$eventTypeId = $this->getFilterValue($filters, 'eventTypeId');
		$eventFormat = $this->getFilterValue($filters, 'eventFormat');
		$limit = $this->getFilterValue($filters, 'limit', '1000');
		$context = $this->getFilterValue($filters, 'context');
		$location = $this->getFilterValue($filters, 'location');
		$isBookable = $this->getFilterValue($filters, 'isBookable');
		$dateFrom = $this->getFilterValue($filters, 'dateForm');
		$dateTo = $this->getFilterValue($filters, 'dateTo');

		// Apply filters if any are set
		if ($filters) {
			if ($eventIds) {
				$eventParams['id'] = $eventIds;
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
			if ($eventFormat && $eventFormat != 0) {
				$eventParams['event_format'] = $eventFormat;
			}
			if ($eventTypeId && $eventTypeId != 0) {
				$eventParams['event_base__event_type'] = explode(',', $eventTypeId);
			}
			if ($context && $context != 0) {
				$eventParams['event_base__context'] = $context;
			}
			if ($location && $location != 0) {
				$eventParams['location'] = explode(',', $location);
			}
			if ($isBookable && $isBookable != 0) {
				$eventParams['is_bookable'] = 'true';
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
		} catch (ClientException $e) {
			if ($e->getCode() === 401) {
				$this->resetAccessToken();
				$this->view->assign('internalError', true);
			}
		} catch (Throwable $e) {
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
			} catch (ClientException $e) {
				if ($e->getCode() === 401) {
					$this->resetAccessToken();
					$this->view->assign('internalError', true);
				}
			} catch (Throwable $e) {
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
			$eventBaseType = $eventBase['event_type']['id'];

			// Get events from EDM, transform them to array and assign the results to FE
			$events = $this->getEventsFromEventBase($eventBaseId, $showAll, $eventBaseType);

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
		} catch (ClientException $e) {
			if ($e->getCode() === 401) {
				$this->resetAccessToken();
				$this->view->assign('internalError', true);
			}
			return;
		} catch (Throwable $e) {
			$this->view->assign('internalError', true);
			return;
		}

		return $eventBase;
	}

	protected function getEventsFromEventBase(int $eventBaseId, bool $showAll, int $eventBaseType)
	{
		$eventParams = [
			'expand' => 'event_base.contact_person,event_base.group_children,dates.location,~all',
			'event_base' => $eventBaseId,
			'is_public' => 'true'
		];

		// Get events from EDM, transform them to array and assign the results to FE
		try {
			if ($showAll === true || in_array(strval($eventBaseType), explode(',', $this->settings['customConditions']['eventTypes']['showAllEvents']))) {
				$events = $this->getClient()->getRestClient()->fetchCollection('events', $eventParams); // TODO: Methode in AIS SDK für findAll
			} else {
				$events = $this->getClient()->event->findUpcoming($eventParams);
			}
		} catch (ClientException $e) {
			if ($e->getCode() === 401) {
				$this->resetAccessToken();
				$this->view->assign('internalError', true);
			}
			return;
		} catch (Throwable $e) {
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

			$priceCount = count($event['prices']);
		}

		$event['price_count'] = $priceCount;

		return $event;
	}

	protected function verifyEventPrices(array $events)
	{
		foreach ($events as $key => &$event) {
			$tempPriceArray = [];
			$currentTimestamp = time();

			foreach ($event['prices'] as $price) {
				$validFrom = isset($price['valid_from']) ? strtotime($price['valid_from']) : null;
				$validUntil = isset($price['valid_until']) ? strtotime($price['valid_until']) : null;

				if (($validFrom && $currentTimestamp <= $validFrom) || ($validUntil && $currentTimestamp >= $validUntil)) {
					continue; // Skip invalid prices
				}

				$tempPriceArray[] = $price;

				// Update lowest price
				if (!isset($event['lowest_price']) || $event['lowest_price'] > $price['amount']) {
					$event['lowest_price'] = $price['amount'];
				}
			}

			$event['prices'] = $tempPriceArray;
		}

		return $events;
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
			if (isset($location['address'])) {
				$cities[] = $location['address']['city'];
			}
		}

		// Sort cities alphabetically
		if (isset($cities)) asort($cities);

		return $cities;
	}

	protected function getLocationCities($events, $isResultsArray = false)
	{
		$cities = [];

		foreach ($events as $event) {
			if (isset($event['location']['address']['city'])) {
				$cities[] = $event['location']['address']['city'];
			}
		}

		// Sort cities alphabetically
		if (isset($cities)) asort($cities);

		return $cities;
	}

	protected function getFilterValue(array $filters, string $filter, mixed $default = '')
	{
		return array_key_exists($filter, $filters) ? $filters[$filter] : $default;
	}
}
