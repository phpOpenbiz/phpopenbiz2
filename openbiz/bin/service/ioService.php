<?php
/**
 * @package PluginService
 */
/**
 * ioService - 
 * class ioService is the plug-in service of handling file import/export 
 * 
 * @package PluginService
 * @author rocky swen 
 * @copyright Copyright (c) 2003
 * @version $Id: ioService.php,v 1.1 2006/04/07 08:01:29 rockys Exp $
 * @access public 
 */
class ioService
{
   public function __construct(&$xmlArr) 
   {
   }
   
   public function exportXML($objname)
   {
      global $g_BizSystem;  
      // get the current UI bizobj
      $bizform = $g_BizSystem->GetObjectFactory()->GetObject($objname);    // get the existing bizform object 
      $bizobj = $bizform->GetDataObj();
      
      $recList = array();
      $bizobj->FetchRecords("", $recList);
      
      $s_xml = "<?xml version='1.0' standalone='yes'?>\n";
      $s_xml .= "<BizDataObj Name=\"".$bizobj->m_Name."\">\n";
      foreach ($recList as $rec)
      {
         $s_record = "\t<Record>\n";
         foreach ($rec as $fld=>$val) {
            $s_record .= "\t\t<Field Name=\"$fld\" Value=\"$val\" />\n";
         }
         $s_record .= "\t</Record>\n";
         $s_xml .= $s_record;
      }
      $s_xml .= "</BizDataObj>";
      
      // output variables
      $name = str_replace(".","_",$bizobj->m_Name).".xml";
      $size = strlen($s_xml);
      $type = "text/plain";
      
      ob_clean();
      
      header("Cache-Control: ");// leave blank to avoid IE errors
      header("Pragma: ");// leave blank to avoid IE errors
      header("Content-Disposition: attachment; filename=\"$name\"");
      header("Content-length: $size");
      header("Content-type: $type");
      echo $s_xml;
      
      exit;
   }
   
   public function importXML($objname)
   {
      global $g_BizSystem; 
      // read in file from $_FILE
      // read in file data and attributes
      foreach ($_FILES as $file) {
         $error = $file['error'];
         if ($error != 0) {
            $this->ReportError($error);
            return;
         }
         
         $tmpName  = $file['tmp_name'];
         $xml = simplexml_load_file($tmpName);
         if (!$xml) {
            $errorMsg = "Invalid input data format, could not create xml object.";
            $g_BizSystem->GetClientProxy()->ShowErrorMessage($errorMsg);
            return;
         }
         // only read the first one
         break;
      }
      
      // get the current UI bizobj
      $bizform = $g_BizSystem->GetObjectFactory()->GetObject($objname);    // get the existing bizform object 
      $prtFormObj = $g_BizSystem->GetObjectFactory()->GetObject($bizform->GetParentForm());
      $bizobj = $prtFormObj->GetDataObj();
      
      //$oldCacheMode = $bizobj->GetCacheMode(); 
      //$bizobj->SetCacheMode(0);    // turn off cache mode, not affect the current cache
      
      // check if BizDataObj name matches
      $dataobjName = $xml['Name'];
      if ($bizobj->m_Name != $dataobjName) {
         $errorMsg = "Invalid input data. Input data object is not same as the current data object.";
         $g_BizSystem->GetClientProxy()->ShowErrorMessage($errorMsg);
         return;
      }
      
      // read records
      foreach ($xml->Record as $rec) {
         // insert record
         // todo: check if there's same user keys in the table
         $recArray = null;
         $recArray = $bizobj->NewRecord();
         foreach ($rec as $fld) {
            $value = "";
            foreach ($fld->attributes() as $att_name=>$att_value) {
               if ($att_name == 'Name') $name = $att_value."";
               else if ($att_name == 'Value') $value = $att_value."";
            }
            if ($name != "Id")
               $recArray[$name] = $value;
         }
         
         if (!$bizobj->InsertRecord($recArray)) {
            $bizobj()->GetErrorMessage();
            $g_BizSystem->GetClientProxy()->ShowErrorMessage($errorMsg);
            return;
         }
      }
      // $bizobj->SetCacheMode($oldCacheMode);  // restore cache mode
      
      $bizform->SetFormState(1); // indicate the import is done
   }
   
   protected function ReportError($error)
   {
      if ($error==1)
         $errorStr = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
      else if ($error==2)
         $errorStr = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
      else if ($error==3)
         $errorStr = "The uploaded file was only partially uploaded";
      else if ($error==4)
         $errorStr = "No file was uploaded";
      else if ($error==6)
         $errorStr = "Missing a temporary folder";
      else if ($error==7)
         $errorStr = "Failed to write file to disk";
      else
         $errorStr = "Error in file upload";
         
      global $g_BizSystem;
      $g_BizSystem->GetClientProxy()->ShowErrorMessage($errorStr);
   }
}

?>