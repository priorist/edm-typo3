<?php
namespace Priorist\Edm\Core;

class Base {

	/**
	 * Lists Tx_Edm_Base's class dependencies.
	 *
	 * @var array
	 */
	private $_classes = array(
		'typoscriptservice' => 'TYPO3\CMS\Core\TypoScript\TypoScriptService'
	);

	private static $_settings = null;

	/**
	 *
	 */
	public function getTypoScriptSettings($key=null, $default=null){
		if (self::$_settings === NULL) {
			$service = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($this->_classes['typoscriptservice']);

			self::$_settings = $GLOBALS['TSFE']->tmpl->setup["plugin."]["tx_edm."]['settings.'];
			self::$_settings = $service->convertTypoScriptArrayToPlainArray(self::$_settings);
		}

		if ($key === null) {
			return self::$_settings;
        }

		if (array_key_exists($key, self::$_settings)) {
			return self::$_settings[$key];
		}

		return $default;
	}

	/**
	 * @see http://www.codeterrorizer.com/typoscript/typo3-dev-get-base-url-in-extension
	 */
	public function getTypo3BaseUrl(){
		return $GLOBALS['TSFE']->tmpl->setup['config.']['baseURL'];
	}

	/**
	 * @see http://www.typo3forum.net/forum/typo3-4-x-fragen-probleme/31375-id-rootpage-auslesen.html
	 *
	 * @return array
	 */
	public function getTypo3RootPage(){
		$page_handler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Page\PageRepository');
		$root_page = $GLOBALS["TSFE"]->page;

		while($root_page['pid'] != 0){
			if($root_page['is_siteroot']){
				break;
			}
			$root_page = $page_handler->getPage($root_page['pid']);
		}

		return $root_page;
	}

	/**
	 * Gets the currently active language key.
	 * Default value for language key is "default".
	 *
	 * @see \TYPO3\CMS\Extbase\Utility\LocalizationUtility::setLanguageKeys()
	 *
	 * @return string
	 */
	public function getTypo3LanguageKey() {
		$languageKey = 'default';
		if (TYPO3_MODE === 'FE') {
			if (isset($GLOBALS['TSFE']->config['config']['language'])) {
				$languageKey = $GLOBALS['TSFE']->config['config']['language'];
			}
		} elseif (strlen($GLOBALS['BE_USER']->uc['lang']) > 0) {
			$languageKey = $GLOBALS['BE_USER']->uc['lang'];
		}
		return $languageKey;
	}

	/**
	 *
	 */
	public function getServerName(){
		$settings = $this->getTypoScriptSettings();

		if($settings['debug'] && $settings['service']['debug_domain']){
			$debugDomain = preg_replace('/[^a-z0-9\-\.]/', '', strtolower($settings['service']['debug_domain']));
			if(strlen($debugDomain) > 0){
				return $debugDomain;
			}
		}

		return $_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : ($_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : '');
	}
}


?>
