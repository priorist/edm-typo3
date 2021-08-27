<?php

namespace Priorist\Edm\Core;

require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('core') . 'Classes/SingletonInterface.php';

class Helper extends Base implements \TYPO3\CMS\Core\SingletonInterface
{
  /**
   * Get a specific global variable.
   *
   * Possible values for type are:
   * - M 		search the variable in REQUEST_METHOD, _POST or _GET
   * - G 		search in _GET only
   * - P 		search in _POST only
   * - C 		search in _COOKIE only
   * - S 		search in _SESSION only
   * - X 		search for GPCS, firt find will delivered
   * - F 		search in _FILES only
   * - E 		use getenv()
   * - GPSCF	search explicit in given order
   *
   * @access public
   * @param String 		$varname
   * @param String 		$type
   * @return mixed|null	The specific global variable
   */
  public function &valueof($varname, $type = 'M')
  {
    global $_GET, $_POST, $_SESSION, $_COOKIE, $_FILES;
    $res = null;
    $varname = strval($varname);
    $type = strtoupper(trim(strval($type)));
    @$type = ($type == 'M') ? substr($_SERVER['REQUEST_METHOD'], 0, 1) : $type;
    $type = ($type == 'X') ? 'GPCSEF' : $type;
    if (($sl = strlen($type)) > 1) {
      $type = ereg_replace('[^GPSCEF]', '', $type);
      $sl = strlen($type);
      for ($i = 0; $i < $sl; $i++) {
        $tt = substr($type, $i, 1);
        switch ($tt) {
          case 'G':
            if (isset($_GET[$varname])) {
              return $_GET[$varname];
            }
          case 'P':
            if (isset($_POST[$varname])) {
              return $_POST[$varname];
            }
          case 'C':
            if (isset($_SESSION[$varname])) {
              return $_SESSION[$varname];
            }
          case 'S':
            if (isset($_COOKIE[$varname])) {
              return $_COOKIE[$varname];
            }
          case 'E':
            if ($res = getenv($varname)) {
              return $res;
            }
          case 'F':
            if (isset($_FILES[$varname])) {
              return $_FILES[$varname];
            }
        }
      }
      return $res;
    }
    switch ($type) {
      case 'E':
        $res = getenv($varname);
        return $res;
      case 'G':
        return $_GET[$varname];
      case 'P':
        return $_POST[$varname];
      case 'C':
        return $_COOKIE[$varname];
      case 'S':
        return $_SESSION[$varname];
      case 'F':
        return $_FILES[$varname];
    }
    return $res;
  }
}
