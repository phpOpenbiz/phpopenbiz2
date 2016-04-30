<?php
/****************************************************************************
   Error Messages
 ****************************************************************************/
$Rsc_ErrorMessages = array (
 "SYS_ERROR" => "BizSystem Error",
 "SYS_ERROR_RPCARGS" => "You pass less than 2 arguments which are object name and method name.",
 "SYS_ERROR_CLASSNOTFOUND" => "Cannot find the class with name as %0%.",
 "SYS_ERROR_METHODNOTFOUND" => "Object %0% doesn't have such a method %1%.",
 "SYS_ERROR_NODBINFO" => "No Database information found in the config file.",
 
 "BDO_ERROR" => "BizDataObj Error",
 "BDO_ERROR_DBCONN" => "Unable to connection to database %0%.",
 "BDO_ERROR_REQUIRED" => "Field [%0%] is required. Please enter a valid value.",
 "BDO_ERROR_INVALID_INPUT" => "Unable to validate field [%0%] = %1% as input value against rule %2%. Please enter again.",
 "BDO_ERROR_INVALID_DBNAME" => "Unable to find a valide entry with Name as '%0%' in metadata/Config.xml. Please add a Database entry with Name as '%0%' in metadata/Config.xml file",
 
 "BFM_ERROR_INVALID_DISPMODE" => "Unable to find a valid display mode for BizForm %0%."
);

?>