<?PHP

// register __destruct method as shutdown function
register_shutdown_function("bizsystem_shutdown");

function bizsystem_shutdown()
{
   global $g_BizSystem;
   $g_BizSystem->GetSessionContext()->SaveSessionObjects();
}

/**
 * BizSystem class - BizSystem is initialized for each request, it provides infrastructure objects and utility methods
 * which are used in whole request.
 *
 * @package BizSystem
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */
class BizSystem
{
   private $m_ObjectFactory = null;
   private $m_SessionContext = null;
   private $m_Confgiuration = null;
   private $m_ClientProxy = null;
   private $m_TypeManager = null;
   private $m_CurrentViewName = "";
   private $m_CurrentViewSet = "";
   private $m_DBConnection = array();
   private $m_ServiceList = array();
   private $m_UserProfile = null;
   
   private static $m_Instance = null;

   public static function instance()
   {
      if (self::$m_Instance == null)
         self::$m_Instance = new BizSystem();
      return self::$m_Instance;
   }
   
   /**
    * BizSystem::__construct() - initialize SessionContext and retieve object session variables
    *
    * @return void
    */
   private function __construct()
   {
      include_once(OPENBIZ_BIN."SessionContext.php");
      $this->m_SessionContext = new SessionContext();
      // retrieve object session vars
      $this->m_SessionContext->RetrieveSessionObjects();
   }

   /**
    * BizSystem::__destruct() - save object session variables
    *
    * @return void
    */
   public function __destruct()
   {
      // save object session vars
      //$this->m_SessionContext->SaveSessionObjects();
      //echo "<br>destruct bizSystem";
   }

   /**
    * BizSystem::GetObjectFactory() - get the ObjectFactory object
    *
    * @return ObjectFactory
    */
   public function GetObjectFactory()
   {
      if (!$this->m_ObjectFactory) {
         include_once(OPENBIZ_BIN."ObjectFactory.php");
         $this->m_ObjectFactory = new ObjectFactory();
      }
      return $this->m_ObjectFactory;
   }
   
   public static function ObjectFactory()
   {
      return BizSystem::instance()->GetObjectFactory();
   }

   /**
    * BizSystem::GetSessionContext() - get the SessionContext object
    *
    * @return SessionContext
    */
   public function GetSessionContext()
   {
      return $this->m_SessionContext;
   }
   
   public static function SessionContext()
   {
      return BizSystem::instance()->GetSessionContext();
   }

   /**
    * BizSystem::GetConfiguration() - get the Configuration object
    *
    * @return Configuration
    */
   public function GetConfiguration()
   {
      if (!$this->m_Confgiuration) {
         include_once(OPENBIZ_BIN."Configuration.php");
         $this->m_Confgiuration = new Configuration();
      }
      return $this->m_Confgiuration;
   }
   
   public static function Configuration()
   {
      return BizSystem::instance()->GetConfiguration();
   }

   /**
    * BizSystem::GetClientProxy() - get the ClientProxy object
    *
    * @return ClientProxy
    */
   public function GetClientProxy()
   {
      if (!$this->m_ClientProxy) {
         include_once(OPENBIZ_BIN."ClientProxy.php");
         $this->m_ClientProxy = new ClientProxy();
      }
      return $this->m_ClientProxy;
   }
   
   public static function ClientProxy()
   {
      return BizSystem::instance()->GetClientProxy();
   }

   /**
    * BizSystem::GetTypeManager() - get the TypeManager object
    *
    * @return TypeManager
    */
   public function GetTypeManager()
   {
      if (!$this->m_TypeManager) {
         include_once(OPENBIZ_BIN."TypeManager.php");
         $this->m_TypeManager = new TypeManager();
      }
      return $this->m_TypeManager;
   }
   
   public static function TypeManager()
   {
      return BizSystem::instance()->GetTypeManager();
   }

   public function GetService($service)
   {
      $default_package = "service";
      $svc_name = $service;
      if (strpos($service, ".") === false)
         $svc_name = $default_package.".".$service;
      return $this->GetObjectFactory()->GetObject($svc_name);
   }

   public function InitUserProfile($userId)
   {
      $svcobj = $this->GetService("profileService");
      $profile = $svcobj->GetProfile($userId);
      if ($profile)
         $this->GetSessionContext()->SetVar("USR_PRFL", $profile);
      return $profile;
   }

   public function GetUserProfile($attribute=null)
   {
      if (!$this->m_UserProfile) {
         $this->m_UserProfile = $this->GetSessionContext()->GetVar("USR_PRFL");   // USR_PRFL stands for user profile
      }
      if ($attribute) {
         if (!is_array($this->m_UserProfile))
            return "";
         if (isset($this->m_UserProfile[$attribute]))
            return $this->m_UserProfile[$attribute];
         else {
            $svcobj = $this->GetService("profileService");
            return $svcobj->GetAttribute($this->m_UserProfile["USERID"],$attribute);
         }
      }
      return $this->m_UserProfile;
   }

   /**
    * BizSystem::GetCurrentViewName() - get the current view name
    *
    * @return string
    */
   public function GetCurrentViewName()
   {
      if ($this->m_CurrentViewName == "")
         $this->m_CurrentViewName = $this->GetSessionContext()->GetVar("CVN");   // CVN stands for CurrentViewName
      return $this->m_CurrentViewName;
   }
   /**
    * BizSystem::SetCurrentViewName() - set the current view name
    *
    * @param string $viewname
    * @return string
    */
   public function SetCurrentViewName($viewname)
   {
      $this->m_CurrentViewName = $viewname;
      $this->GetSessionContext()->SetVar("CVN", $this->m_CurrentViewName);   // CVN stands for CurrentViewName
   }

   public function GetCurrentViewSet()
   {
      if ($this->m_CurrentViewSet == "")
         $this->m_CurrentViewSet = $this->GetSessionContext()->GetVar("CVS");   // CVS stands for CurrentViewSet
      return $this->m_CurrentViewSet;
   }

   public function SetCurrentViewSet($viewSet)
   {
      $this->m_CurrentViewSet = $viewSet;
      $this->GetSessionContext()->SetVar("CVS", $this->m_CurrentViewSet);   // CVS stands for CurrentViewSet
   }

   /**
    * BizSystem::GetDBConnection() - get the database connection object
    *
    * @param string $dbname, database name declared in config.xml
    * @return Zend_DB_Adaptor
    */
   public function GetDBConnection($dbname=null)
   {
      $rDBName = (!$dbname) ? "Default" : $dbname;
      if (isset($this->m_DBConnection[$rDBName]))
         return $this->m_DBConnection[$rDBName];

      $dbinfo = $this->GetConfiguration()->GetDatabaseInfo($rDBName);

      require_once 'Zend/Db.php';

      $params = array ('host'     => $dbinfo["Server"],
                       'username' => $dbinfo["User"],
                       'password' => $dbinfo["Password"],
                       'dbname'   => $dbinfo["DBName"]);

      $db = Zend_Db::factory($dbinfo["Driver"], $params);

      $db->setFetchMode(PDO::FETCH_NUM);

      // todo: only for mysql
      $db->query("SET NAMES 'utf8'");

      $this->m_DbConnection[$rDBName] = $db;

      return $db;
   }

   /**
    * BizSystem::GetMacroValue() - evaluate macro, this method can only be used to get profile in 2.0
    * For example, @macro_var:macro_key. i.e. @profile:ROLE
    *
    * @param string $var, macro name
    * @param string $key, macro key
    * @return string
    */
   public function GetMacroValue($var, $key)
   {
      if ($var == "profile") {
         return $this->GetUserProfile($key);
      }
      return null;
   }

   public static function GetSmartyTemplate()
   {
      include_once(SMARTY_DIR."Smarty.class.php");
      $smarty = new Smarty;
      if (defined('SMARTY_TPL_PATH'))
         $smarty->template_dir = SMARTY_TPL_PATH;
      if (defined('SMARTY_CPL_PATH'))
         $smarty->compile_dir = SMARTY_CPL_PATH;
      if (defined('SMARTY_CFG_PATH'))
         $smarty->config_dir = SMARTY_CFG_PATH;
      return $smarty;
   }

   /*
    * BizSystem::log()
    * log message to log file
    *
    * @param integer $priority. it can be one of following value
    *    LOG_EMERG	system is unusable = 1
    *    LOG_ALERT	action must be taken immediately = LOG_EMERG
    *    LOG_CRIT	   critical conditions = LOG_EMERG
    *    LOG_ERR	   error conditions = 4
    *    LOG_WARNING	warning conditions = 5
    *    LOG_NOTICE	normal, but significant, condition = 6
    *    LOG_INFO	   informational message = LOG_NOTICE
    *    LOG_DEBUG	debug-level message = LOG_NOTICE
    *    ### So LOG_EMERG, LOG_ERR, LOG_WARNING and LOG_DEBUG are valid inputs ###
    * @param string $subject. the log subject decided by caller function
    * @param string $message. the message to be logged in log file
    * @param string $file_name file to save to
    * @return void
   */
   public static function log($priority, $subject, $message, $file_name = NULL) {
      global $g_BizSystem;
      $svcobj = $g_BizSystem->GetService("logService");
      $svcobj->log($priority, $subject, $message, $file_name);
   }

   /**
    * BizSystem::GetXmlFileWithPath()
    * Search the object metedata file as objname+.xml in metedata directories
    * name convension: demo.BOEvent points to metadata/demo/BOEvent.xml
    * new in 2.2.3, demo.BOEvent can point to modules/demo/BOEvent.xml
    *
    * @param string $xmlobj
    * @return string xml config file path
    **/
   public static function GetXmlFileWithPath($xmlobj)
   {
      $xmlfile = $xmlobj;
      if (strpos($xmlobj, ".xml")>0)  // remove .xml suffix if any
         $xmlfile = substr($xmlobj, 0, strlen($xmlobj)-4);

      // replace "." with "/"
      $xmlfile = str_replace (".", "/", $xmlfile);
      $xmlfile .= ".xml";
      
      //if (file_exists($xmlfile))
      //   return $xmlfile;

      $xmlfile = "/".$xmlfile;
      // search in modules directory first
      if (file_exists(MODULE_PATH.$xmlfile))
         return MODULE_PATH.$xmlfile;
      
      if (file_exists(META_PATH.$xmlfile))
         return META_PATH.$xmlfile;
      if (file_exists(OPENBIZ_META.$xmlfile))
         return OPENBIZ_META.$xmlfile;

      return null;
   }
   
   /**
    * BizSystem::GetTplFileWithPath()
    * Get openbiz template file path by searching modules/package, /templates
    *
    * @param string $className
    * @return string php library file path
    **/
   public static function GetTplFileWithPath($templateFile, $packageName)
   {
      // check if the template file can be found under modules/module_name/templates
      $tplfile = MODULE_PATH."/".str_replace('.','/',$packageName)."/templates/".$templateFile;
      if (!file_exists($tplfile))
         $tplfile = $templateFile;
      return $tplfile;
   }

   /**
    * BizSystem::GetLibFileWithPath()
    * Get openbiz library php file path by searching modules/package, /bin/package and /bin
    *
    * @param string $className
    * @return string php library file path
    **/
   public static function GetLibFileWithPath($className, $packageName="")
   {
      if (!$className) return;
      // search it in cache first
      $cacheKey = $className."_path";
      if (extension_loaded('0') && ($filepath = apc_fetch($cacheKey)) != null)
         return $filepath;

      if (strpos($className, ".") > 0)
         $className = str_replace(".", "/", $className);
      $filepath = null;
      $classfile = $className.".php";
      $classfile_0 = $className.".php";
      // convert package name to path, add it to classfile
      $bFound = false;
      if ($packageName) {
         $path = str_replace(".", "/", $packageName);
         // search in apphome/modules directory first, search in apphome/bin directory then
         $classfiles[0] = MODULE_PATH."/".$path."/".$classfile;
         $classfiles[1] = APP_HOME."/bin/".$path."/".$classfile;
         foreach ($classfiles as $classfile)
         {
            if (file_exists($classfile))
            {
               $filepath = $classfile;
               $bFound = true;
               break;
            }
         }
      }
      
      if (!$bFound)
         $filepath = self::GetCoreLibFilePath($className);
      // cache it to save file search
      if ($filepath && extension_loaded('apc'))
         apc_store($cacheKey, $filepath);
      return $filepath;
   }
   
   private static function GetCoreLibFilePath($className)
   {
      $classfile = $className.'.php';
      if (strpos($className, 'BizData') === 0 || $className == 'DataRecord' 
          || $className == 'BizField')
         $classfile = OPENBIZ_BIN.'data/'.$classfile;
      else if (strpos($className, 'BizForm') === 0 || strpos($className, 'BizView') === 0
               || strpos($className, 'HTML') === 0 || $className === 'FieldControl' || $className === 'RowSelector')
         $classfile = OPENBIZ_BIN.'ui/'.$classfile;
      else if (strpos(strrev($className), strrev('Service')) === 0)
         $classfile = OPENBIZ_BIN.'service/'.$classfile;
      else
         $classfile = OPENBIZ_BIN.$classfile;
         
      if (file_exists($classfile))
         return $classfile;
      return null;
   }

   /**
    * BizSystem::GetXmlArray()
    * Get Xml Array. If xml file has been compiled (has .cmp), load the cmp file as array; 
    * otherwise, compile the .xml to .cmp first
    * new 2.2.3, .cmp files will be created in app/cache/metadata_cmp directory. replace '/' with '_'
    * for example, /module/demo/BOEvent.xml has cmp file as _module_demo_BOEvent.xml
    *
    * @param string $xmlFile
    * @return array
    **/
   public static function &GetXmlArray($xmlFile)
   {
      $objXmlFileName = $xmlFile;
      //$objCmpFileName = dirname($objXmlFileName) . "/__cmp/" . basename($objXmlFileName, "xml") . ".cmp";
      $_crc32 = sprintf('%08X', crc32(dirname($objXmlFileName)));
      $objCmpFileName = CACHE_METADATA_PATH.'/'.$_crc32 .'_' . basename($objXmlFileName, "xml") . "cmp";

      $xmlArr = null;
      //$cacheKey = substr($objXmlFileName, strlen(META_PATH)+1);
      $cacheKey = $objXmlFileName;
      $findInCache = false;
      if(file_exists($objCmpFileName)
         && (filemtime($objCmpFileName)>filemtime($objXmlFileName)) )
      {
         // search in cache first
         if (!$xmlArr && extension_loaded('apc')) {
            if (($xmlArr = apc_fetch($cacheKey)) != null) {
               $findInCache = true;
            }
         }
         if (!$xmlArr) {
            $content_array = file($objCmpFileName);
            $xmlArr = unserialize(implode("", $content_array));
         }
      }
      else {
         include_once(OPENBIZ_BIN."util/xmltoarray.php");
         $parser = new XMLParser($objXmlFileName, 'file', 1);
         $xmlArr = $parser->getTree();
         $xmlArrStr = serialize($xmlArr);
         if (!file_exists(dirname($objCmpFileName)))
            mkdir(dirname($objCmpFileName));
         $cmp_file = fopen($objCmpFileName, 'w') or die("can't open cmp file to write");
         fwrite($cmp_file, $xmlArrStr) or die("can't write to the cmp file");
         fclose($cmp_file);
      }
      // save to cache to avoid file processing overhead
      if (!$findInCache && extension_loaded('apc')) {
         apc_store($cacheKey, $xmlArr);
      }
      return $xmlArr;
   }
   
   /**
    * BizSystem::GetMessage()
    * Get message resource
    *
    * @param string $rscType, resource type (ERROR|...|...)
    * @param string $resId, resource id
    * @param array $paramArr, parameter key-value array
    * @return string
    **/
   public static function GetMessage($rscType, $rscId, $paramArr=null)
   {
      // get locale
      // get the resource according to the locale
      global $Rsc_ErrorMessages;
      if ($rscType == "ERROR") {
         $msg = $Rsc_ErrorMessages[$rscId];
      }
      if ($paramArr) {
         $i=0; $pos=0;
         foreach($paramArr as $param) {
            $pos = strpos($msg,"%".$i."%",$pos);
            if ($pos===false) break;
            $msg = str_replace("%".$i."%",$param,$msg);
            $i++;
         }
      }
      return $msg;
   }

   /**
    * BizSystem::ErrorHandler() - user error handler
    **/
   public static function ErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
   {
       // set of errors for which a var trace will be saved
       $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);

       //Zend_Translate code uses exception handling
        //but if warnings are displayed exception can't be thrown
        //so we disable warnings here too
		if ($errno == E_NOTICE || $errno == E_STRICT|| $errno == E_WARNING) return;  // ignore notice error

       // timestamp for the error entry
       date_default_timezone_set('GMT');   // to avoid error PHP 5.1
       $dt = date("Y-m-d H:i:s (T)");

       $err = "<div style='font-size: 12px; color: blue; font-family:Arial; font-weight:bold;'>";
       $err .= "[$dt] An exception occurred while executing this script:<br>";
       $err .= "Error message: <font color=maroon> #$errno, $errmsg</font><br>";
       $err .= "Script name and line number of error: <font color=maroon>$filename:$linenum</font><br>";
       //$err .= "Variable state when error occurred: $vars<br>";
       $err .= "<hr>";
       $err .= "Please ask system administrator for help...</div>";
       //BizSystem::ErrorBacktrace();

       //echo $err;
       global $g_BizSystem;
       echo $g_BizSystem->GetClientProxy()->ShowErrorPopup($err);

       if ($errno == E_USER_ERROR || $errno == E_ERROR)
          exit;
   }

   public static function ErrorBacktrace($print=true)
   {
       $debug_array = debug_backtrace();
       $counter = count($debug_array);
       for($tmp_counter = 0; $tmp_counter != $counter; ++$tmp_counter)
       {
          echo "<br><b>function:</b> ";
          echo $debug_array[$tmp_counter]["function"] . " ( ";
          //count how many args a there
          $args_counter = count($debug_array[$tmp_counter]["args"]);
          //print them
          for($tmp_args_counter = 0; $tmp_args_counter != $args_counter; ++$tmp_args_counter)
          {
             echo($debug_array[$tmp_counter]["args"][$tmp_args_counter]);
             if(($tmp_args_counter + 1) != $args_counter)
               echo(", ");
             else
               echo(" ");
          }
          echo ") @ ";
          echo($debug_array[$tmp_counter]["file"]." ");
          echo($debug_array[$tmp_counter]["line"]);
          if(($tmp_counter + 1) != $counter)
             echo "\n";
       }
       //exit();
   }

}

?>
