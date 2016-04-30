<?php
/**
 * @package PluginService
 */

/**
 * reportService - 
 * class reportService is the plug-in service of generate report for BizDataobj 
 * 
 * @package PluginService
 * @author rocky swen 
 * @copyright Copyright (c) 2003
 * @version $Id: reportService.php,v 1.1 2006/04/07 08:01:29 rockys Exp $
 * @access public 
 */
class reportService extends MetaObject
{
   public $m_targetReportPath; // = "D:\\Tomcat5\\webapps\\birt-viewer\\report\\";
   public $m_rptTemplate; // = "dataobj.rptdesign.tpl";
   public $m_birtViewer; // = "http://localhost:8080/birt-viewer";
   
   /**
    * reportService::reportService()
    * 
    * @param void
    * @return void 
    */
   function __construct(&$xmlArr)
   {
      $this->ReadMetadata($xmlArr);
   }
   
   protected function ReadMetadata(&$xmlArr)
   {
      parent::ReadMetaData($xmlArr);
      $this->m_targetReportPath = isset($xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["TARGETREPORTPATH"]) ? $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["TARGETREPORTPATH"] : null;
      $this->m_rptTemplate = isset($xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["REPORTTEMPLATE"]) ? $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["REPORTTEMPLATE"] : null;
      $this->m_birtViewer = isset($xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["BIRTVIEWER"]) ? $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["BIRTVIEWER"] : null;
   }
   
   /**
    * reportService::render() - render the pdf output
    * 
    * @param string $objname object name which is the bizform name
    * @return void 
    */
   function render($objname)
   {
      global $g_BizSystem;  
      // get the current UI bizobj
      $bizform = $g_BizSystem->GetObjectFactory()->GetObject($objname);    // get the existing bizform object 
      $bizobj = $bizform->GetDataObj();
      
      $h=opendir($this->m_targetReportPath);
      if (!$h) {
         echo "cannot read dir ".$this->m_targetReportPath; exit;
      }
      // create a tmp csv file for hold the data, then feed csv file to report engine
      $uid = $this->GetUniqueString();
      $tmpfname = $this->m_targetReportPath . $uid . ".csv";
      //echo "csv file is at $tmpfname.<br>";
      $fp = fopen($tmpfname, 'w');
      
      $keylist = $bizform->m_RecordRow->GetSortControlKeys();
      $fieldNames = array();
      foreach($keylist as $key) {
         $fieldNames[] = $bizform->GetControl($key)->m_BizFieldName;
      }
      fputcsv($fp, $fieldNames);
      
      $recList = $bizobj->DirectFetch();
      foreach ($recList as $recArray)
      {
         unset($fieldValues);
         $fieldValues = array();
         $line = "";
         foreach($keylist as $key) {
            $fieldValues[] = $recArray[$bizform->GetControl($key)->m_BizFieldName];
         }
         fputcsv($fp, $fieldValues);
      }
      
      fclose($fp);
      
      $i = 0;
      foreach($keylist as $key) {
         $rpt_fields[$i]["name"] = $bizform->GetControl($key)->m_BizFieldName;
         $rpt_fields[$i]["type"] = $bizobj->GetField($rpt_fields[$i]["name"])->m_Type;
         $i++;
      }
      
      // dataobj.rptdesign.tpl
      // $rpt_data_dir, $rpt_title, $rpt_csv_file, $rpt_fields[](name,type)
      $smarty = BizSystem::GetSmartyTemplate();
      $smarty->assign_by_ref("rpt_data_dir", $this->m_targetReportPath);
      $smarty->assign_by_ref("rpt_title", $bizform->m_Title);
      $smarty->assign_by_ref("rpt_csv_file", basename($tmpfname));
      $smarty->assign_by_ref("rpt_fields", $rpt_fields);
      $rpt_content = $smarty->fetch($this->m_rptTemplate);
      
      $tmpRptDsgn = $this->m_targetReportPath . $uid . ".rptdesign";
      //echo "temp rpt design file is at $tmpRptDsgn.<br>";
      $fp = fopen($tmpRptDsgn, 'w');
      fwrite($fp, $rpt_content);
      fclose($fp);
      
      ob_clean();
      $dsgnFilename = $uid . ".rptdesign";
      $content = "<div style='font-family:Arial; font-size:12px; background-color:#FCFCFC;'>";
      $content .= "Reports can be viewed as ";
      $content .= "<li><a href='".$this->m_birtViewer."/run?__report=report\\$dsgnFilename' target='__blank'>HTML report</a></li>";
      $content .= "<li><a href='".$this->m_birtViewer."/run?__report=report\\$dsgnFilename&__format=pdf' target='__blank'>PDF report</a></li>";
      $content .= "<li><a href='".$this->m_birtViewer."/frameset?__report=report\\$dsgnFilename' target='__blank'>Interactive report</a></li>";
      $content .= "</div>";

      echo $content;
      exit;
   }
   
   function CleanFiles($dir, $seconds)
   {
      //Delete temporary files
      $t=time();
      $h=opendir($dir);
      while($file=readdir($h))
      {
          $path=$dir.'/'.$file;
          if($t-filemtime($path)>$seconds)
             unlink($path);
      }
      closedir($h);
   }
   
   function GetUniqueString()
   {
      $mdy = date("mdy");
      $hms = date("His");	
      $rightnow = $mdy.$hms;
   
      return md5($rightnow);
   }
}
?>