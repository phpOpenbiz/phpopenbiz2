<?php
class logService
{
   public function __construct() {}
   
   /*
    * logService::log() 
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
    * @return void
   */
   public function log($priority, $subject, $message)
   {
      if ($priority==LOG_DEBUG && DEBUG == 0) return;

      if ($priority==LOG_EMERG) $pri_str = "EMERGENT";   // same as LOG_ALERT and LOG_CRIT
      else if ($priority==LOG_ERR) $pri_str = "ERROR";
      else if ($priority==LOG_WARNING) $pri_str = "WARNING";
      else if ($priority==LOG_DEBUG) $pri_str = "DEBUG"; // same as LOG_INFO and LOG_NOTICE
      else return;
      
      global $g_BizSystem;
      $profile = $g_BizSystem->GetUserProfile();
      if ($profile["USERID"])
         $logfile = "log_".$profile["USERID"].".html";
      else
         $logfile = "log_".$pri_str.".html";
      //echo "logfile=".$logfile;
      if($fd = @fopen(LOG_PATH."/".$logfile, "a")) {
         $date = gmdate( "d/M/Y:H:i:s"); 
         fputs($fd, $date." - (".$pri_str." | ".$subject.") ".$message."<br>"); 
         fclose($fd); 
      } 
   }
   
}

?>