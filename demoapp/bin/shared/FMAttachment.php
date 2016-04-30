<?php
class FMAttachment extends BizForm
{  
   public function Download($id)
   {
      global $g_BizSystem;
      $db = $g_BizSystem->GetDBConnection();
      
      $dataobj = $this->GetDataObj();
      $table = $dataobj->m_MainTable;
      $name_col = $dataobj->GetField("Name")->m_Column;
      $type_col = $dataobj->GetField("Type")->m_Column;
      $size_col = $dataobj->GetField("Size")->m_Column;
      $data_col = $dataobj->GetField("Data")->m_Column;
      $id_col = $dataobj->GetField("Id")->m_Column;
      
      $sql = "SELECT $name_col, $type_col, $size_col, $data_col FROM $table WHERE $id_col = '$id'";
      try {
         $pdo_stmt = $db->query($sql);
      }
      catch (Exception $e) {
         $errMsg = "Error in query: " . $sql . ". " . $e->getMessage();
         echo $errMsg;
         exit;
      }
      
      list($name, $type, $size, $content) = $pdo_stmt->fetch(PDO::FETCH_NUM);
      
      unset($pdo_stmt);
      unset($db);
      
      ob_clean();
      
      header("Cache-Control: ");// leave blank to avoid IE errors
      header("Pragma: ");// leave blank to avoid IE errors
      header("Content-Disposition: attachment; filename=\"$name\"");
      header("Content-length: $size");
      header("Content-type: $type");
      echo $content;

      exit;
   }
/*Example 1692. Inserting an image into a database

This example opens up a file and passes the file handle to PDO to insert it as a LOB. PDO will do its best to get the contents of the file up to the database in the most efficient manner possible.
<?php
$db = new PDO('odbc:SAMPLE', 'db2inst1', 'ibmdb2');
$stmt = $db->prepare("insert into images (id, contenttype, imagedata) values (?, ?, ?)");
$id = get_new_id(); // some function to allocate a new ID

// assume that we are running as part of a file upload form
// You can find more information in the PHP documentation

$fp = fopen($_FILES['file']['tmp_name'], 'rb');

$stmt->bindParam(1, $id);
$stmt->bindParam(2, $_FILES['file']['type']);
$stmt->bindParam(3, $fp, PDO::PARAM_LOB);

$db->beginTransaction();
$stmt->execute();
$db->commit();
?>
*/ 

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
         $recArr["Name"] = $file['name'];
         $recArr["Type"] = $file['type'];
         $recArr["Size"] = $file['size'];
         $recArr["Data"] = $file['tmp_name'];
         /*
         $fileName = $file['name'];
         $tmpName  = $file['tmp_name'];
         $fileSize = $file['size'];
         $fileType = $file['type'];
         
         $fp = fopen($tmpName, 'r') or die("can't open the uploaded file to read");         
         $fileData = fread($fp, $fileSize) or die("can't read from the uploaded file");
         $fileData = addslashes($fileData);
         fclose($fp);
         
         if(!get_magic_quotes_gpc()) {
            $fileName = addslashes($fileName);
         }
         
         $recArr["Name"] = $fileName;
         $recArr["Type"] = $fileType;
         $recArr["Size"] = $fileSize;
         $recArr["Data"] = $fileData;
         */
         // only read the first one
         break;
      }
      return true;
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