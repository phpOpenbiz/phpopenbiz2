<?php
/****************************************************************************
   openbiz core path
 ****************************************************************************/
//define('OPENBIZ_HOME', 'absolute_dir/Openbiz');
if(!defined('OPENBIZ_HOME')) define('OPENBIZ_HOME',dirname(dirname(__FILE__)));
if(!defined('OPENBIZ_BIN'))  define('OPENBIZ_BIN',OPENBIZ_HOME."/bin/");
if(!defined('OPENBIZ_META')) define('OPENBIZ_META',OPENBIZ_HOME."/metadata/");

/****************************************************************************
   third party library path
 ****************************************************************************/
// Smarty package
if(!defined('SMARTY_DIR')) define('SMARTY_DIR',OPENBIZ_HOME."/others/Smarty/libs/");

// ADODB package
if(!defined('ADODB_DIR')) define('ADODB_DIR',OPENBIZ_HOME."/others/adodb");

// PHPMailer package
if(!defined('PHPMAILER_DIR')) define('PHPMAILER_DIR',OPENBIZ_HOME."/others/phpmailer");

/****************************************************************************
   application services
 ****************************************************************************/
if(!defined('AUTH_SERVICE'))     define('AUTH_SERVICE', "authService");
if(!defined('ACCESS_SERVICE'))   define('ACCESS_SERVICE', "accessService");
if(!defined('PROFILE_SERVICE'))  define('PROFILE_SERVICE', "profileService");
if(!defined('LOG_SERVICE'))      define('LOG_SERVICE', "logService");
if(!defined('EXCEL_SERVICE'))    define('EXCEL_SERVICE', "excelService");
if(!defined('PDF_SERVICE'))      define('PDF_SERVICE', "pdfService");
if(!defined('IO_SERVICE'))       define('IO_SERVICE', "ioService");
if(!defined('EMAIL_SERVICE'))    define('EMAIL_SERVICE', "emailService");
if(!defined('DOTRIGGER_SERVICE')) define('DOTRIGGER_SERVICE', "doTriggerService");
if(!defined('GENID_SERVICE'))    define('GENID_SERVICE', "genIdService");
if(!defined('VALIDATE_SERVICE')) define('VALIDATE_SERVICE', "validateService");

/* whether print debug infomation or not */
if(!defined('DEBUG')) define("DEBUG", 0);
if(!defined('SQLDEBUG')) define("SQLDEBUG", 0);

/* check whether user logged in */
if(!defined('CHECKUSER')) define("CHECKUSER", "N");
/* session timeout seconds */
if(!defined('TIMEOUT')) define("TIMEOUT", -1);  // -1 means never timeout.

include_once("sysresource.inc");

// defined Zend framework library home as ZEND_FRWK_HOME
define('ZEND_FRWK_HOME', OPENBIZ_HOME."/others/zendfrwk/library/");
// add zend framework to include path
set_include_path(get_include_path() . PATH_SEPARATOR . ZEND_FRWK_HOME);

/**
 * MetaObject class - MetaObject is the base class of all derived metadata-driven classes
 * 
 * @package BizSystem
 */
abstract class MetaObject
{
   public $m_Name, $m_Class, $m_Package, $m_Description;
   function __construct(&$xmlArr) {}
   //function __destruct() {}
   protected function ReadMetaData(&$xmlArr) {
      $root_keys = array_keys($xmlArr);
      $root_key = $root_keys[0];
      $this->m_Name = isset($xmlArr[$root_key]["ATTRIBUTES"]["NAME"]) ? $xmlArr[$root_key]["ATTRIBUTES"]["NAME"] : null;
      $this->m_Description = isset($xmlArr[$root_key]["ATTRIBUTES"]["DESCRIPTION"]) ? $xmlArr[$root_key]["ATTRIBUTES"]["DESCRIPTION"] : null;
      $this->m_Package = isset($xmlArr[$root_key]["ATTRIBUTES"]["PACKAGE"]) ? $xmlArr[$root_key]["ATTRIBUTES"]["PACKAGE"] : null;
      $this->m_Class = isset($xmlArr[$root_key]["ATTRIBUTES"]["CLASS"]) ? $xmlArr[$root_key]["ATTRIBUTES"]["CLASS"] : null;
   }
   protected function ReadMetaCollection(&$xmlArr, &$metaList) {
      if (!$xmlArr) { $metaList = null; return; }
      if (isset($xmlArr["ATTRIBUTES"]))  $metaList[] = $xmlArr;
      else $metaList = $xmlArr;
   }
   protected function PrefixPackage($name) {
      if ($name && !strpos($name, ".") && ($this->m_Package)) // no package prefix as package.object, add it
         $name = $this->m_Package.".".$name;
      
      return $name;
   }
   public function GetProperty($propertyName) { 
      if ($propertyName == "Name") return $this->m_Name;
      if ($propertyName == "Class") return $this->m_Class;
      if ($propertyName == "Package") return $this->m_Package;
      if ($propertyName == "Description") return $this->m_Description;
      return null; 
   }
}
/**
 * MetaIterator class - MetaIterator is the base class of all derived metadata-driven classes who support iteration
 * 
 * @package BizSystem
 */
class MetaIterator implements Iterator
{
   protected $m_prtObj = null;
   protected $m_var = array();
   public function __construct(&$xmlArr, $childClassName, $prtObj=null) { 
      //if (is_array($array)) $this->var = $array; 
      $this->m_prtObj = $prtObj;
      if (!$xmlArr) return;
      if (isset($xmlArr["ATTRIBUTES"])) {
         $className = isset($xmlArr["ATTRIBUTES"]['CLASS']) ? $xmlArr["ATTRIBUTES"]['CLASS'] : $childClassName;
         $obj = new $className($xmlArr, $prtObj);
         $this->m_var[$obj->m_Name] = $obj;
      }
      else {
         foreach ($xmlArr as $child) {
            $className = isset($child["ATTRIBUTES"]['CLASS']) ? $child["ATTRIBUTES"]['CLASS'] : $childClassName;
            //$this->m_var[$child["ATTRIBUTES"]['NAME']] = new $className($child, $prtObj);
            $obj = new $className($child, $prtObj);
            $this->m_var[$obj->m_Name] = $obj;
         }
      }
   }
   public function merge(&$anotherMIObj) { 
      foreach($anotherMIObj as $key=>$value) {
         if (!key_exists($key,$this->m_var)) $this->m_var[$key] = $value;
      }
   }
   public function get($key) { return isset($this->m_var[$key]) ? $this->m_var[$key] : null; }
   public function set($key, $val) { $this->m_var[$key] = $val; }
   public function rewind() { reset($this->m_var);  }
   public function current() { return current($this->m_var); }
   public function key() { return key($this->m_var); }
   public function next() { return next($this->m_var); }
   public function valid() { return $this->current() !== false; }
}

class Parameter
{
   public $m_Name, $m_Value, $m_Required, $m_InOut;
   public function __construct(&$xmlArr) {
      $this->m_Name = isset($xmlArr["ATTRIBUTES"]["NAME"]) ? $xmlArr["ATTRIBUTES"]["NAME"] : null;
      $this->m_Value = isset($xmlArr["ATTRIBUTES"]["VALUE"]) ? $xmlArr["ATTRIBUTES"]["VALUE"] : null;
      $this->m_Required = isset($xmlArr["ATTRIBUTES"]["REQUIRED"]) ? $xmlArr["ATTRIBUTES"]["REQUIRED"] : null;
      $this->m_InOut = isset($xmlArr["ATTRIBUTES"]["INOUT"]) ? $xmlArr["ATTRIBUTES"]["INOUT"] : null;
   }
   public function GetProperty($propertyName) { 
      if ($propertyName == "Value") return $this->m_Value;
      return null; 
   }
}

/**
 * iSessionObject interface - stateful metadata-driven classed need to implement SetSessionVars and GetSessionVars
 * 
 * @package BizSystem
 */
interface iSessionObject
{
   public function SetSessionVars($sessCtxt);
   public function GetSessionVars($sessCtxt);
}

/**
 * iUIControl interface - all UI classes need to implement Render method
 * 
 * @package BizSystem
 */
interface iUIControl
{
   public function Render();
}

class BDOException extends Exception {}
class BFMException extends Exception {}
class BSVCException extends Exception {}

/*
   global variables
*/
include_once("BizSystem.php");
//$g_BizSystem = new BizSystem();
$g_BizSystem = BizSystem::instance();

/**
 * __auload - search for the php file required to load the class
 * 
 * @package BizSystem
 */
function __autoload($class_name) {
   $filepath = BizSystem::GetLibFileWithPath($class_name);
   if ($filepath) {
      include_once($filepath);
      return;
   }
}

function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

// error handling 
error_reporting(E_ALL ^ (E_NOTICE | E_STRICT));
// if use user defined error handling function, all errors are reported to the function
set_error_handler("userErrorHandler");
set_exception_handler('userExceptionHandler');

/**
 * userErrorHandler - user error handler function
 * 
 * @package BizSystem
 */
function userErrorHandler ($errno, $errmsg, $filename, $linenum, $vars) 
{
   include_once(OPENBIZ_BIN.'ErrorHandler.php');
   OB_ErrorHandler::ErrorHandler($errno, $errmsg, $filename, $linenum, $vars);
}

function userExceptionHandler ($exc) 
{
   include_once(OPENBIZ_BIN.'ErrorHandler.php');
   OB_ErrorHandler::ExceptionHandler($exc);
}
?>
