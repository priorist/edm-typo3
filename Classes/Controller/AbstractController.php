<?php

namespace Priorist\EdmTypo3\Controller;

use Exception;
use Priorist\EDM\Client\Client;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class AbstractController extends ActionController
{
	const REGISTRY_NAMESPACE = 'tx_edm';
	const REGISTRY_KEY = 'cache_access-token';
	const REGISTRY_EXPIRATION = 12; // expiration in hours

	protected $client = null;
	protected $registry;

	public function __construct()
	{
		$this->registry = GeneralUtility::makeInstance(Registry::class);
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

	/**
	 * Get filters set in Typo3 plugin backend configuration
	 */
	public function getPluginFilter()
	{
		$filters = $this->settings['listFilter'];

		if ($filters) {
			if (!$filters['eventIds']) {
				unset($filters['eventIds']);
			}

			if (!$filters['eventBaseIds']) {
				unset($filters['eventBaseIds']);
			}

			if (!$filters['categoryIds']) {
				unset($filters['categoryIds']);
			}

			if (!$filters['eventTypeId']) {
				unset($filters['eventTypeId']);
			}

			if (!$filters['limit']) {
				unset($filters['limit']);
			}

			if (!$filters['context']) {
				unset($filters['context']);
			}

			if (!$filters['location']) {
				unset($filters['location']);
			}

			if (!$filters['isBookable']) {
				unset($filters['isBookable']);
			}

			if (!$filters['dateFrom']) {
				unset($filters['dateFrom']);
			}

			if (!$filters['dateTo']) {
				unset($filters['dateTo']);
			}

			if (!$filters['showAll']) {
				unset($filters['showAll']);
			}
		}

		return $filters;
	}
}
