<?php

namespace Priorist\EdmTypo3\Controller;

use Exception;
use Priorist\EDM\Client\Client;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class AbstractController extends ActionController
{
	const REGISTRY_NAMESPACE = 'tx_edmtypo3';
	const REGISTRY_KEY = 'cache_access-token';
	const REGISTRY_EXPIRATION = 12; // expiration in hours
	const LOG_MESSAGE_401 = 'EDM-Authentifizierung fehlgeschlagen. Access Token wird neu angefordert.';

	protected $client = null;
	protected $registry;

	public function __construct()
	{
		$this->registry = GeneralUtility::makeInstance(Registry::class);
		$this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
	}

	protected function storeAccessToken($accessToken)
	{
		$registryData = [
			'access_token' => $accessToken,
			'timestamp' => time(),
		];
		$this->registry->set(static::REGISTRY_NAMESPACE, static::REGISTRY_KEY, $registryData);
	}

	protected function getStoredAccessToken()
	{
		$edmCacheAccessToken = $this->registry->get(static::REGISTRY_NAMESPACE, static::REGISTRY_KEY);

		if ($edmCacheAccessToken === false) {
			return null;
		}

		$timestampOfPersistedAccessToken = $edmCacheAccessToken['timestamp'];
		$currentTimestamp = time();
		$expirationPeriodInSeconds = static::REGISTRY_EXPIRATION * 60 * 60;

		if (($currentTimestamp - $expirationPeriodInSeconds) >= $timestampOfPersistedAccessToken) {
			$this->registry->remove(static::REGISTRY_NAMESPACE, static::REGISTRY_KEY);
			return null;
		}

		return $edmCacheAccessToken['access_token'];
	}

	public function getClient()
	{
		if ($this->client === null) {
			$settings = $this->settings;

			if ($settings === null) {
				throw new Exception('EDM Extension TypoScript is not configured properly.');
			}

			if ($settings['edm']['url'] === '{$plugin.tx_edm.edm.url}') {
				throw new Exception('No EDM URL has been defined in TypoScript constants.');
			}

			if ($settings['edm']['auth']['anonymous']['clientId'] === '{$plugin.tx_edm.edm.auth.anonymous.clientId}') {
				throw new Exception('No EDM Client ID has been defined in TypoScript constants.');
			}

			if ($settings['edm']['auth']['anonymous']['clientSecret'] === '{$plugin.tx_edm.edm.auth.anonymous.clientSecret}') {
				throw new Exception('No EDM Client Secret has been defined in TypoScript constants.');
			}

			$url = $settings['edm']['url'];
			$clientId = $settings['edm']['auth']['anonymous']['clientId'];
			$clientSecret = $settings['edm']['auth']['anonymous']['clientSecret'];

			$this->client = new Client($url, $clientId, $clientSecret);
		}

		$storedAccessToken = $this->getStoredAccessToken();

		if ($storedAccessToken !== null) {
			$this->client->setAccessToken($storedAccessToken);
		} else {
			$this->storeAccessToken($this->client->getAccessToken());
		}

		return $this->client;
	}

	protected function resetAccessToken(): void
	{
		$this->logger->error(static::LOG_MESSAGE_401);
		$this->registry->remove(static::REGISTRY_NAMESPACE, static::REGISTRY_KEY);
	}

	/**
	 * Get filters set in Typo3 plugin backend configuration
	 */
	public function getPluginFilter()
	{
		$filters = $this->settings['listFilter'];

		if (isset($filters)) {
			$filterKeys = [
				'eventIds',
				'eventBaseIds',
				'categoryIds',
				'eventTypeId',
				'eventFormat',
				'limit',
				'context',
				'location',
				'isBookable',
				'dateFrom',
				'dateTo',
				'showAll'
			];

			foreach ($filterKeys as $key) {
				if (array_key_exists($key, $filters) && $this->hasNoFilterValue($filters[$key])) {
					unset($filters[$key]);
				}
			}
		}

		return $filters;
	}

	protected function hasNoFilterValue($val)
	{
		return $val === null || strlen($val) == 0;
	}

	/**
	 * Drop prices outside their validity period, sort the remaining ones ascending
	 * and derive lowest price and price count from them
	 */
	protected function prepareEventPriceData(array $event): array
	{
		$now = time();

		$prices = array_filter($event['prices'] ?? [], function ($price) use ($now) {
			$validFrom = !empty($price['valid_from']) ? strtotime($price['valid_from']) : null;
			$validUntil = !empty($price['valid_until']) ? strtotime($price['valid_until']) : null;

			return ($validFrom === null || $validFrom <= $now) && ($validUntil === null || $validUntil >= $now);
		});

		usort($prices, function ($item1, $item2) {
			return $item1['amount'] <=> $item2['amount'];
		});

		$event['prices'] = $prices;
		$event['price_count'] = count($prices);
		$event['lowest_price'] = $prices[0]['amount'] ?? null;

		return $event;
	}

	protected function prepareEventsPriceData(?iterable $events): array
	{
		$preparedEvents = [];

		foreach ($events ?? [] as $event) {
			$preparedEvents[] = $this->prepareEventPriceData($event);
		}

		return $preparedEvents;
	}

	/**
	 * Prevent search engines from indexing pages of event bases
	 * belonging to one of the configured EDM contexts
	 */
	protected function applyRobotsNoIndex(?array $eventBase): void
	{
		$configuredContexts = array_filter(array_map(
			'trim',
			explode(',', (string)($this->settings['customConditions']['context']['noIndex'] ?? ''))
		), 'strlen');

		if ($configuredContexts === []) {
			return;
		}

		foreach (($eventBase['contexts'] ?? []) as $context) {
			// EDM serializers deliver contexts either expanded (['id' => 1]) or as bare IDs
			$contextId = is_array($context) ? ($context['id'] ?? null) : $context;

			if ($contextId !== null && in_array((string)$contextId, $configuredContexts, true)) {
				GeneralUtility::makeInstance(MetaTagManagerRegistry::class)
					->getManagerForProperty('robots')
					->addProperty('robots', 'noindex, nofollow', [], true);

				return;
			}
		}
	}
}
