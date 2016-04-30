<?php
/**
 * @package PluginService
 */

/**
 * pdfService - 
 * class pdfService is the plug-in service of printing bizform to pdf 
 * 
 * @package PluginService
 * @author rocky swen 
 * @copyright Copyright (c) 2003
 * @version $Id: pdfService.php,v 1.1 2006/04/07 08:01:29 rockys Exp $
 * @access public 
 */
class pdfService
{
   /**
    * pdfService::pdfService()
    * 
    * @param void
    * @return void 
    */
   function pdfService() {}
   
   /**
    * pdfService::render() - render the pdf output
    * 
    * @param string $objname object name which is the bizform name
    * @return void 
    */
   function renderView($viewName)
   {
      //echo "pdf Service";
      global $g_BizSystem;
      $viewobj = $g_BizSystem->GetObjectFactory()->GetObject($viewName);
      if($viewobj) {
         $viewobj->SetConsoleOutput(false);
         $sHTML = $viewobj->Render();
         //$sHTML = "Test";
         //require_once("dompdf/dompdf_config.inc.php");
         $dompdf = new DOMPDF();
         $dompdf->load_html($sHTML);
         //$dompdf->set_paper($_POST["paper"], $_POST["orientation"]);
         $dompdf->render();
         $this->Output($dompdf);
         //$dompdf->stream("dompdf_out.pdf");
      }
   }
   
   function Output($dompdf)
   {
      //$tmpfile = getcwd()."/tmpfiles";
      $tmpfile = APP_HOME."/tmpfiles";
      //echo $tmpfile;
      $this->CleanFiles($tmpfile, 100);
      //Determine a temporary file name in the current directory
      $file_tmp=tempnam($tmpfile,'tmp');
      $file=$file_tmp.'.pdf';
      $file=str_replace("\\","/",$file);
      unlink($file_tmp);
      //Save PDF to file
      $pdfText = $dompdf->output();
      $h_file = fopen($file, 'w') or die("can't open pdf file to write");
      fwrite($h_file, $pdfText) or die("can't write to the pdf file");
      fclose($h_file);
      //JavaScript redirection
      $path_parts = pathinfo($file);
      $file_download = "tmpfiles/".$path_parts['basename'];
      echo "<HTML><BODY onload=\"window.location.href='../$file_download';\"</BODY></HTML>";
   }
   
   function CleanFiles($dir, $seconds)
   {
      //Delete temporary files
      $t=time();
      $h=opendir($dir);
      while($file=readdir($h))
      {
        if(substr($file,0,3)=='tmp' && substr($file,-4)=='.pdf')
        {
          $path=$dir.'/'.$file;
          if($t-filemtime($path)>$seconds)
             unlink($path);
        }
      }
      closedir($h);
   }
}
?>