<?php
class FM_Attachment extends BizForm
{  
   public function Download($id)
   {
      global $g_BizSystem;
      
      $resultRecords = array();
      $this->m_SearchRule = "[Id]=$id";
      $this->_run_search($resultRecords);
      
      if (count($resultRecords) == 0)
         return;
      $size = $resultRecords[0]['size'];
      $type = $resultRecords[0]['type'];
      $name = $resultRecords[0]['filename'];
      $pid = $resultRecords[0]['parent_id'];
      $filepath = $this->GetTargetPath()."/".$pid."/".$name;
      
      if (!file_exists($filepath)) {
         $this->ReportError("File is not found at $filepath");
         return;
      }
      
      include_once (OPENBIZ_HOME."/others/class.httpdownload.php");
      
      $object = new httpdownload();
    	$object->set_byfile($filepath); //Download from a file
    	$object->mime = $type;
    	$object->use_resume = true; //Enable Resume Mode
    	$object->download(); //Download File

      exit;
   }
   
   private function GetTargetPath()
   {
      return SECURE_UPLOAD_PATH.DIRECTORY_SEPARATOR.$this->m_Package.DIRECTORY_SEPARATOR."attachment";
   }

   public function SaveRecord()
   {
      global $g_BizSystem;
      // call ValidateForm()
      if ($this->ValidateForm() == false)
          return;

      $recArr = array();
      if ($this->ReadInputRecord($recArr) == false)
         return;

      // read in file data and attributes
      foreach ($_FILES as $file) {
         $error = $file['error'];
         if ($error != 0) {
            $this->ReportError($error);
            return false;
         }
         $recArr["filename"] = $file['name'];
         $recArr["type"] = $file['type'];
         $recArr["size"] = $file['size'];
         $pid = $recArr['parent_id'];
         
         // move the upload file to target upload directory
         $filedir = $this->GetTargetPath().DIRECTORY_SEPARATOR.$pid;
         $filepath = $filedir.DIRECTORY_SEPARATOR.$file['name'];
         if (!is_dir($filedir))
         {
            $old = umask(0);
            $res = @mkdir($filedir, 0777, true);
            umask($old);
         }
         
         // TODO: check if the same file exist
         $replace_exist =  $g_BizSystem->GetClientProxy()->GetFormInputs('bctrl_replace');
         if (file_exists($filepath) && $replace_exist != 'Y')
         {
            $g_BizSystem->GetClientProxy()->ShowErrorMessage("The same file already exists, please try again!");
            return false;
         }
         
         if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $g_BizSystem->GetClientProxy()->ShowErrorMessage("There was an error uploading the file, please try again!");
            return false;
         }
         if (file_exists($filepath))
         {
            // update the record whose filename is the same as input
            $searchRule = "[filename] = '".$file['name']."'";
            $recordList = array();
            $this->GetDataObj()->FetchRecords($searchRule, $recordList);
            if (count($recordList)>0)
               $dataRec = new DataRecord($recordList[0], $this->GetDataObj());
            else
               $dataRec = new DataRecord(null, $this->GetDataObj());
         }
         else 
         {
            // create a new record
            $dataRec = new DataRecord(null, $this->GetDataObj());
         }

         break;
      }
      
      foreach ($recArr as $k=>$v)
         $dataRec[$k] = $v;   // or $dataRec->$k = $v;
      $ok = $dataRec->save();

      if (!$ok)
         return $this->ProcessDataObjError($ok);

      $this->UpdateActiveRecord($this->GetDataObj()->GetActiveRecord());
      $this->SetDisplayMode (MODE_R);
      // TODO: popup message of new record successful saved
      return $this->ReRender();
   }
/*
   protected function ReadInputRecord(&$recArr)
   {
      parent::ReadInputRecord($recArr);
      
      // read in file data and attributes
      foreach ($_FILES as $file) {
         $error = $file['error'];
         if ($error != 0) {
            $this->ReportError($error);
            return false;
         }
         $recArr["filename"] = $file['name'];
         $recArr["type"] = $file['type'];
         $recArr["size"] = $file['size'];
         $pid = $recArr['parent_id'];
         
         // move the upload file to target upload directory
         $filedir = $this->GetTargetPath().DIRECTORY_SEPARATOR.$pid;
         $filepath = $filedir.DIRECTORY_SEPARATOR.$file['name'];
         if (!is_dir($filedir))
         {
            $old = umask(0);
            $res = @mkdir($filedir, 0777, true);
            umask($old);
         }
         
         if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $this->ReportError("There was an error uploading the file, please try again!");
            return false;
         }

         break;
      }
      return true;
   }
*/
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