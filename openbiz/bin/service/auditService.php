<?php
/**
 * @package PluginService
 */
/**
 * auditService - 
 * class auditService is the plug-in service of handling audit trail of DataObj
 * 
 * @package PluginService
 * @author rocky swen 
 * @copyright Copyright (c) 2003
 * @version $Id: auditService.php,v 1.1 2006/04/07 08:01:29 rockys Exp $
 * @access public 
 */
class auditService
{
   public $m_AuditDataObj = "shared.BOAudit";
   
   function __construct() {}
   
   public function Audit($dataObjName)
   {
      global $g_BizSystem;
      // get audit dataobj
      $auditDataObj = $g_BizSystem->GetObjectFactory()->GetObject($this->m_AuditDataObj);
      if (!$auditDataObj) return false;
      
      // get the source dataobj
      $srcDataObj = $g_BizSystem->GetObjectFactory()->GetObject($dataObjName);
      if (!$srcDataObj) return false;
      
      // for each onaudit field, add a record in audit dataobj
      $auditFlds = $srcDataObj->GetOnAuditFields();
      foreach ($auditFlds as $fld)
      {
         if ($fld->m_OldValue == $fld->m_Value) 
            continue;
         $recArr = $auditDataObj->NewRecord();
         if ($recArr == false) {
            BizSystem::log(LOG_ERR, "DATAOBJ", $auditDataObj->GetErrorMessage());
            return false;
         }
         $recArr['DataObjName'] = $dataObjName;
         $recArr['ObjectId'] = $srcDataObj->GetFieldValue("Id");
         $recArr['FieldName'] = $fld->m_Name;
         $recArr['OldValue'] = $fld->m_OldValue;
         $recArr['NewValue'] = $fld->m_Value;
         $recArr['ChangeTime'] = date("Y-m-d H:i:s");
         $ok = $auditDataObj->InsertRecord($recArr);
         if ($ok == false){
            BizSystem::log(LOG_ERR, "DATAOBJ", $auditDataObj->GetErrorMessage());
            return false;
         }
      }
   }
}
?>