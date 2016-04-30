<?php
class FM_TicketDetails extends BizForm 
{ 
   private $m_AuditDataObj = "trac.DO_TicketChange";
   
   public function SaveRecord()
   {
      if ($this->m_Mode == MODE_N)
         return parent::SaveRecord();
      
      $this->m_ReRenderOn = false;
      parent::SaveRecord();
      $this->m_ReRenderOn = true;
      
      global $g_BizSystem;
      // get audit dataobj
      $auditDataObj = $g_BizSystem->GetObjectFactory()->GetObject($this->m_AuditDataObj);
      if (!$auditDataObj) return false;
      
      // get the source dataobj
      $srcDataObj = $this->GetDataObj();
      if (!$srcDataObj) return false;
      
      $author = $g_BizSystem->GetClientProxy()->GetFormInputs("editor_name");
      $time = date("Y-m-d H:i:s");
      foreach ($srcDataObj->m_BizRecord as $fld)
      {
         if ($fld->m_OldValue == $fld->m_Value || trim($fld->m_OldValue) == trim($fld->m_Value))
            continue;
         
         $dataRec = new DataRecord(null, $auditDataObj); 
         $dataRec['ticket'] = $srcDataObj->GetFieldValue("Id");
         $dataRec['field'] = $fld->m_Name;
         $dataRec['oldvalue'] = $fld->m_OldValue;
         $dataRec['newvalue'] = $fld->m_Value;
         $dataRec['time'] = $time;
         $dataRec['author'] = $author;       
         $ok = $dataRec->Save();
         if ($ok == false){
            BizSystem::log(LOG_ERR, "DATAOBJ", $auditDataObj->GetErrorMessage());
            return false;
         }
      }
      
      // save comment
      $comment = $g_BizSystem->GetClientProxy()->GetFormInputs("editor_comment");
      if ($comment && $comment != "")
      {
         $dataRec = new DataRecord(null, $auditDataObj); 
         $dataRec['ticket'] = $srcDataObj->GetFieldValue("Id");
         $dataRec['field'] = 'comment';
         $dataRec['newvalue'] = $comment;
         $dataRec['time'] = $time;
         $dataRec['author'] = $author;       
         $ok = $dataRec->Save();
         if ($ok == false){
            BizSystem::log(LOG_ERR, "DATAOBJ", $auditDataObj->GetErrorMessage());
            return false;
         }
      }
      
      return $this->ReRender();
   }   
}
?>