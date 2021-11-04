<?php

namespace Priorist\EdmTypo3\Controller;

use Priorist\EDM\Client\Client;

class AbstractController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
	protected $client = null;
	protected $session;
	protected $settings;

	/**
	 * Lists AbstractController's class dependencies.
	 *
	 * @var array
	 */
	protected $_classes = array(
		'session'	=> 'Priorist\EdmTypo3\Core\Session'
	);

	/**
	 * Initializes the current action
	 */
	public function initializeAction()
	{
		$this->session	= \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($this->_classes['session']);
	}

	/**
	 *
	 */
	public function setUserSession($ident, $value)
	{
		$this->context->setUserSession($ident, $value);
	}

	/**
	 *
	 */
	public function getUserSession($ident)
	{
		return $this->context->getUserSession($ident);
	}

	/**
	 * Get EDM Client
	 */
	public function getClient()
	{
		// TODO: prevent creation of new request token per request
		/* Initialize client if not already set */
		if ($this->client === null) {
			$settings = $this->settings;
			$url = $settings['edm']['url'];

			// Check whether there's an already logged in participant
			$participantToken = $this->session->get('participantToken');

			if ($participantToken !== null) {
				$clientId = $settings['edm']['auth']['password']['clientId'];
				$clientSecret = $settings['edm']['auth']['password']['clientSecret'];
				$this->client = new Client($url, $clientId, $clientSecret);
				$this->client->setAccessToken($participantToken);
			} else {
				$clientId = $settings['edm']['auth']['anonymous']['clientId'];
				$clientSecret = $settings['edm']['auth']['anonymous']['clientSecret'];
				$this->client = new Client($url, $clientId, $clientSecret);
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
