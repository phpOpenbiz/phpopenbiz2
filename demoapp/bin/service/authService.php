<?php
class authService
{
   public $m_AuthticationType = "database";
   
   public function __construct() {}
   
   public function AuthenticateUser($userid, $password)
   {
      if ($this->m_AuthticationType == "database")
         return $this->AuthDBUser($userid, $password);
      return false;
   }
   
   protected function AuthDBUser($userid, $password)
   {
      global $g_BizSystem;
      
      //--- very simple database table query
      //$sql = "SELECT * FROm $this->m_TableName AS t1 WHERE t1.userid='$userid' AND t1.password='$password'";
      //$resultSet = $g_BizSystem->GetDBConnection()->Execute($sql);
      //if ($resultSet->FetchRow())  return true;
      
      //--- query on dataobj share.BOAuth
      $boAuth = $g_BizSystem->GetObjectFactory()->GetObject("shared.BOAuth");
      if (!$boAuth)
         return false;
      $searchRule = "[User Id]='$userid' AND [Password]='$password'";
	   $recordList = array();
	   $boAuth->FetchRecords($searchRule, $recordList, 1);
	   if (count($recordList) == 1) 
	      return true;

      return false;
   }
}

?>