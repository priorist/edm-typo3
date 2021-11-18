<?php

namespace Priorist\EdmTypo3\Controller;

use Priorist\EDM\Client\Client;

class AbstractController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
	protected $client;

	private function setClient()
	{
		$settings = $this->settings;
		$url = $settings['edm']['url'];
		$clientId = $settings['edm']['auth']['anonymous']['clientId'];
		$clientSecret = $settings['edm']['auth']['anonymous']['clientSecret'];

		$this->client = new Client($url, $clientId, $clientSecret);

		return $this;
	}

	private function storeAccessToken($accessToken)
	{
		$registry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Registry::class);
		$registryData = [
			'access_token' => $accessToken,
			'timestamp' => time(),
		];
		$registry->set('tx_edm', 'edm_cache_access-token', $registryData);
	}

	public function getClient()
	{
		$registry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Registry::class);

		if (false === $edmCacheAccessToken = $registry->get('tx_edm', 'edm_cache_access-token')) {
			// no access token has been set yet
			$this->setClient();
			$this->storeAccessToken($this->client->getAccessToken());
		} else {
			$timestampOfPersistedAccessToken = $edmCacheAccessToken['timestamp'];
			$currentTimestamp = time();
			$expirationPeriod = 43200; // 12 hours in seconds

			if (($currentTimestamp - $expirationPeriod) >= $timestampOfPersistedAccessToken) {
				// set access token has expired
				$registry->remove('tx_edm', 'edm_cache_access-token');
				$this->setClient();
				$this->storeAccessToken($this->client->getAccessToken());
			} else {
				// set access token is still valid
				$persistedAccessToken = $edmCacheAccessToken['access_token'];
				$this->client->setAccessToken($persistedAccessToken); // $this->client currently null
			}
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
