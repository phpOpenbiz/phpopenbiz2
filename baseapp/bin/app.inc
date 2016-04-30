<?php
/****************************************************************************
   openbiz core path
 ****************************************************************************/
//define('OPENBIZ_HOME', 'absolute_dir/Openbiz');
define('OPENBIZ_HOME',dirname(dirname(dirname(__FILE__)))."/openbiz");

/****************************************************************************
   application related path
 ****************************************************************************/
define('APP_HOME',dirname(dirname(__FILE__)));

/* define modules path */
define('MODULE_PATH',APP_HOME."/modules");

define('META_PATH',APP_HOME."/metadata");

/* Log file path */
define("LOG_PATH", APP_HOME."/log");

/* path to save session files */
define("SESSION_PATH", APP_HOME."/session");

/* smarty template path */
define('SMARTY_TPL_PATH', APP_HOME."/templates");
define('SMARTY_CPL_PATH', APP_HOME."/templates/cpl");
define('SMARTY_CFG_PATH', APP_HOME."/templates/cfg");

/* file path. */
define('APP_FILE_PATH', APP_HOME."/files");

/* secured upload / attachment file path. files cannot be accessed by a direct url */
define('SECURE_UPLOAD_PATH', APP_HOME."/files/sec_upload");

/* public upload file path. for example, uploaded image files. files can be accessed by a direct url */ 
define('PUBLIC_UPLOAD_PATH', APP_HOME."/files/upload");

/* file cache directory */
define('CACHE_PATH', APP_HOME."/files/cache");

/* temopary files directory */
define('TEMPFILE_PATH', APP_HOME."/files/tmp");

/* metadata cache files directory */
define('CACHE_METADATA_PATH', APP_HOME."/files/cache/metadata");

/* data cache files directory */
define('CACHE_DATA_PATH', APP_HOME."/files/cache/data");

/****************************************************************************
   application system level constances
 ****************************************************************************/
/* whether print debug infomation or not */
define("DEBUG", 1);
//define("SQLDEBUG", 0);   no longer used in 2.2

/* check whether user logged in */
define("CHECKUSER", "Y");
/* session timeout seconds */
define("TIMEOUT", -1);  // -1 means never timeout.

//I18n
define("DEFAULT_LANGUAGE", "es");

include_once(OPENBIZ_HOME."/bin/sysheader.inc");
?>