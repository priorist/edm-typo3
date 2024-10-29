<?php

namespace Priorist\EdmTypo3\Controller;

use Exception;
use Priorist\EDM\Client\Client;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class AbstractController extends ActionController
{
	const REGISTRY_NAMESPACE = 'tx_edm';
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
}
