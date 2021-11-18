<?php
namespace Priorist\EdmTypo3\Core;

require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('core') . 'Classes/SingletonInterface.php';

// TODO: needs refactoring!!!

/**
 * Typo3 Session Handling
 *
 * @see http://typo3.org/documentation/document-library/core-documentation/doc_core_tsref/4.1.0/view/14/1/
 */
class Session extends Base implements \TYPO3\CMS\Core\SingletonInterface {

	protected $type			= 'ses';
	protected $container	= 'tx_edm_participant';

	/**
	 *
	 */
	public function __construct(){
		// $this->setRealm(false);
	}

	/**
	 *
	 * @param string|null|boolean $containerName
	 */
	public function setRealm($containerName = null){
		if ($containerName !== null) {
			if ($containerName === false) {
				$rootPage		= $this->getTypo3RootPage();
				$containerName	= $this->getServerName() . '_' . $rootPage['uid'];
			}
			$this->container = 'tx_edm_participant_'. preg_replace('/[^0-9a-zA-Z_\.\-]/', '', $containerName);
		} else {
			$this->container = 'tx_edm_participant';
		}
	}

	/**
	 * get data from session
	 *
	 */
	public function get($key=null, $default=null) {
		$data		= $GLOBALS['TSFE']->fe_user->getKey($this->type, $this->container);
		$data		= $data ? unserialize($data) : array();

		if ($key === null) {
			return (array) $data;
		}

		if (is_array($data) && array_key_exists($key, $data)) {
			return $data[$key];
		}

		return $default;
	}

	/**
	 * check, if key exists in session
	 *
	 */
	public function has($key) {
		$data = $this->get();
		return array_key_exists($key, $data) ? true : false;
	}

	/**
	 * store data in session
	 *
	 */
	public function set() {
		$key = null; $value = null; $data = null;

		if (func_num_args() == 2) {
			$key = func_get_arg(0);
			$value = func_get_arg(1);
		}

		if ($data === null) {
			$data		= $this->get();
			$data[$key] = $value;
		}

		$GLOBALS['TSFE']->fe_user->setKey($this->type, $this->container, serialize($data));
		$GLOBALS['TSFE']->fe_user->storeSessionData();
	}

	/**
	 * unset/remove data from session
	 *
	 */
	public function remove($key=null) {
		$data = array();

		if ($key !== null) {
			$data = $this->get();
			if (array_key_exists($key, $data)) {
				unset($data[$key]);
			}
		}

		$GLOBALS['TSFE']->fe_user->setKey($this->type, $this->container, serialize($data));
		$GLOBALS['TSFE']->fe_user->storeSessionData();
	}

	/**
	 * remove entire session
	 */
	public function removeData(){
		$GLOBALS['TSFE']->fe_user->removeSessionData();
	}
}