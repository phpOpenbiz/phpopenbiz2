<?php
define('OPENBIZ_HOME',"../openbiz");
define('APP_HOME',dirname(dirname(__FILE__)));
define('META_PATH',APP_HOME."/metadata");

include_once(OPENBIZ_HOME."/bin/sysheader.inc");

global $g_BizSystem;
$users = $g_BizSystem->GetObjectFactory()->GetObject("calazoo.UsersBO");
echo "ok";

// include openbiz/sysheader.inc

// get db connection

// get data object

// fetch the records
// $dataobj->ClearSearchRule();
// $dataobj->SetSearchRule($this->m_SearchRule);
// $dataobj->SetPageRange($this->m_Range);
// $dataobj->RunSearch();
// or 
// $bizobj->FetchRecords ($searchRule, $resultSet, $numRecords); 

// print query results
?>