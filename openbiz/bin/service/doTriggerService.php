<?php
/**
 * @package PluginService
 */
/*
<PluginService Name="" Description="" Package="" Class="" BizObjectName="">
  <DOTrigger TriggerType="INSERT|UPDATE|DELETE"> *
   <TriggerCondition Expression="" ExtraSearchRule="" />
   <TriggerActions>
      <TriggerAction Action="ExecuteSQL|ExecuteShell|CreateInboxItem|SendEmail|..." Immediate="Y|N" DelayMinutes="" RepeatMinutes="">
         <ActionArgument Name="" Value="" /> *
      </TriggerAction>
   </TriggerActions>
  </DOTrigger>
</PluginService>
*/
/**
 * doTriggerService - 
 * class doTriggerService is the plug-in service of handle DataObject trigger 
 * 
 * @package PluginService
 * @author rocky swen 
 * @copyright Copyright (c) 2003
 * @version $Id: doTriggerService.php,v 1.1 2006/04/07 08:01:29 rockys Exp $
 * @access public 
 */
class doTriggerService extends MetaObject
{
   public $m_DataObjName;
   public $m_DOTriggerList = array();
   
   function __construct(&$xmlArr)
   {
      $this->ReadMetadata($xmlArr);
   }
   
   protected function ReadMetadata(&$xmlArr)
   {
      parent::ReadMetaData($xmlArr);
      $this->m_DataObjName = $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["DATAOBJECTNAME"];
      $this->m_DataObjName = $this->PrefixPackage($this->m_DataObjName);
      
      $this->ReadMetaCollection($xmlArr["PLUGINSERVICE"]["DOTRIGGER"], $tmpList);
      if (!$tmpList) return;
      foreach ($tmpList as $triggerXml) {
         $this->m_DOTriggerList[] = new DOTrigger($triggerXml);
      }
   }
   
   public function Execute($dataObj, $triggerType)
   {
      foreach ($this->m_DOTriggerList as $doTrigger) 
      {
         if ($doTrigger->m_TriggerType == $triggerType)
            $this->ExecuteAllActions($doTrigger, $dataObj);
      }
   }
   
   protected function ExecuteAllActions($doTrigger, $dataObj)
   {
      if (!$this->MatchCondition($doTrigger, $dataObj))
         return;
      foreach ($doTrigger->m_TriggerActions as $triggerAction) {
         $this->ExecuteAction($triggerAction, $dataObj);
      }
   }
   
   protected function MatchCondition($doTrigger, $dataObj)
   {
      // evaluate expression
      $condExpr = $doTrigger->m_TriggerCondition["Expression"];
      if ($condExpr) {
         $exprVal = Expression::EvaluateExpression($condExpr, $dataObj);
         if ($exprVal == false)
            return false;
      }
      
      // do query with extra search rule, check if there's any record returned
      $extraSearchRule = $doTrigger->m_TriggerCondition["ExtraSearchRule"];
      if ($extraSearchRule) {
         $realSearchRule = Expression::EvaluateExpression($extraSearchRule, $dataObj);
         $recordList = array();
         // get one record of the first page with additional searchrule
         $dataObj->FetchRecords($realSearchRule, $recordList, 1, 1, false);
         if (count($recordList) == 0)
            return false;
      }
      
      return true;
   }
   
   protected function ExecuteAction($triggerAction, $dataObj)
   {
      // action method
      $methodName = $triggerAction->m_Action;
      // action method arguments
      if (method_exists($this, $methodName)) {
         // evaluate arguments as expression support
         foreach ($triggerAction->m_ArgList as $argName=>$argValue)
            $argList[$argName] = Expression::EvaluateExpression($argValue, $dataObj);
         // check the immediate flag
         if ($triggerAction->m_Immediate == "Y") // call the method if Immediate is "Y"
            $this->$methodName($argList);
         else { // put it to a passive queue
            /* $passiveQueueSvc->Push($methodName, 
                                      $argList, 
                                      $triggerAction->m_DelayMinutes, 
                                      $triggerAction->m_RepeatMinutes); */
         }
      }
   }
   
   private function ComposeActionMessage($triggerAction, $methodName, $argList)
   {
      $actionMsg["Method"] = $methodName;
      $actionMsg["ArgList"] = $argList;
      $actionMsg["DelayMinutes"] = $triggerAction->m_DelayMinutes;
      $actionMsg["RepeatMinutes"] = $triggerAction->m_RepeatMinutes;
      $actionMsg["StartTime"] = strftime("%Y-%m-%d %H:%M:%S");
   }
   
   protected function ExecuteShell($argList)
   {
      $Script = $argList["Script"];
      $Inputs = $argList["Inputs"];
      $command = "$Script $Inputs";
      //$result = exec($command, $output);
      exec($command);
   }

   protected function ExecuteSQL($argList)
   {
      $dbName = $argList["DBName"];
      if (!$dbName) $dbName = "Default";
      $sql = $argList["SQL"];
      
      global $g_BizSystem;
      $db = $g_BizSystem->GetDBConnection($dbName); 
      
      try {
         $resultSet = $db->query($sql);
      }
      catch (Exception $e) {
         $errorMessage = "Error in run SQL: " . $sql . ". " . $e->getMessage();
      }
   }
   
   protected function SendEmail($argList)
   {
      // integrate with http://phpmailer.sourceforge.net/
      $emailSvcName = $argList["EmailService"];
      global $g_BizSystem;
      $emailSvc = $g_BizSystem->GetObjectFactory()->GetObject($emailSvcName);
      if ($emailSvc == null) 
         return;
      $emailSvc->UseAccount($argList["Account"]);
      
      $TOs = doTriggerService::MakeArray($argList["TOs"]);
      $CCs = doTriggerService::MakeArray($argList["CCs"]);
      $BCCs = doTriggerService::MakeArray($argList["BCCs"]);
      $Attachments = doTriggerService::MakeArray($argList["Attachments"]);
      $subject = $argList["Subject"];
      $body = $argList["Body"];
      
      $ok = $emailSvc->SendEmail($TOs, $CCs, $BCCs, $subject, $body, $Attachments);
      if ($ok == false) {
         // log $emailSvc->GetErrorMsg();
      }
   }
   
   protected function AuditTrail($argList)
   {
      $auditSvcName = $argList["AuditService"];
      global $g_BizSystem;
      $auditSvc = $g_BizSystem->GetObjectFactory()->GetObject($auditSvcName);
      if ($auditSvc == null) 
         return;
      $dataobjName = $argList["DataObjectName"];
      $ok = $auditSvc->Audit($dataobjName);
      if ($ok == false) {
         // log $auditSvc->GetErrorMsg();
      }
   }
   
   static private function MakeArray($string)
   {
      if (!$string) return null;
      $arr = split(";", $string);
      $size = count($arr);
      for ($i=0; $i<$size; $i++)
         $arr[$i] = trim($arr[$i]);
      return $arr;
   }
   
   protected function CreateInboxItem($argList)
   {
      // call inbox service
   }
}

class DOTrigger
{
   public $m_TriggerType;
   public $m_TriggerCondition = array();
   public $m_TriggerActions;
   
   public function __construct($xmlArr)
   {
      $this->m_TriggerType = $xmlArr["ATTRIBUTES"]["TRIGGERTYPE"];
      
      // read in trigger condition
      $this->m_TriggerCondition["Expression"] = $xmlArr["TRIGGERCONDITION"]["ATTRIBUTES"]["EXPRESSION"];
      $this->m_TriggerCondition["ExtraSearchRule"] = $xmlArr["TRIGGERCONDITION"]["ATTRIBUTES"]["EXTRASEARCHRULE"];
      
      $this->m_TriggerActions = new MetaIterator($xmlArr["TRIGGERACTIONS"]["TRIGGERACTION"],"TriggerAction");
   }
}

class TriggerAction extends MetaObject 
{
   public $m_Name;
   public $m_Action;
   public $m_Immediate;
   public $m_DelayMinutes;
   public $m_RepeatMinutes;
   public $m_ArgList = array();
   
   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_Action = $xmlArr["ATTRIBUTES"]["ACTION"];
      $this->m_Immediate = $xmlArr["ATTRIBUTES"]["IMMEDIATE"];
      $this->m_DelayMinutes = $xmlArr["ATTRIBUTES"]["DELAYMINUTES"];
      $this->m_RepeatMinutes = $xmlArr["ATTRIBUTES"]["REPEATMINUTES"];
      
      $this->ReadMetaCollection($xmlArr["ACTIONARGUMENT"], $tmpList);
      if (!$tmpList)
         return;
      foreach ($tmpList as $arg) {
         $this->m_ArgList[$arg["ATTRIBUTES"]["NAME"]] = $arg["ATTRIBUTES"]["VALUE"];
      }
   }
}

?>