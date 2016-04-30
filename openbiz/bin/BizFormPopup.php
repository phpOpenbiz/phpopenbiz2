<?php
/**
 * BizFormPopup class - extension of BizForm to support field selection from a popup form
 * 
 * @package BizView
 */
class BizFormPopup extends BizForm 
{
   public $m_PrtFormCtrlName = "";
   
   public function GetSessionVars($sessCtxt)
   {
      parent::GetSessionVars($sessCtxt);
      $sessCtxt->GetObjVar($this->m_Name, "PrtFormCtrlName", $this->m_PrtFormCtrlName);
   }
   public function SetSessionVars($sessCtxt)
   {
      parent::SetSessionVars($sessCtxt);
      $sessCtxt->SetObjVar($this->m_Name, "PrtFormCtrlName", $this->m_PrtFormCtrlName);
   }
   public function GetViewObject() {
      // generate an xml attribute array for a dynamic bizview
      $xmlArr = BizView::GetPopupViewXML($this->m_Package, $this->m_Name);
      // create a BizViewPopup with the xml array
      global $g_BizSystem;
      $popupView = $g_BizSystem->GetObjectFactory()->CreateObject("DynPopup",$xmlArr);
      return $popupView;
   }
	
	/**
    * BizFormPopup::Close() - close the popup window
    * 
    * @return string HTML text
    */
	public function Close()
	{
	   global $g_BizSystem;
	   $sessCtxt = $g_BizSystem->GetSessionContext();
	   $this->ClearSessionVars($sessCtxt);
	   // clear the object sessio vars
	   return $g_BizSystem->GetClientProxy()->ClosePopup();
	}
	
	public function RefreshParent()
	{
	   global $g_BizSystem;
      $prtForm = $g_BizSystem->GetObjectFactory()->GetObject($this->m_ParentFormName);
      return $prtForm->ReRender();
	}
	
	/**
    * BizFormPopup::JoinToParent() - join a record (popup) to parent form
    * 
    * @return string HTML text
    */
	public function JoinToParent()
   {
      global $g_BizSystem;
      $prtForm = $g_BizSystem->GetObjectFactory()->GetObject($this->m_ParentFormName);
      $rec = $this->GetActiveRecord();
      $updArray = array();
      
      // get the picker map of the control
      $pickerMap = $prtForm->GetControl($this->m_PrtFormCtrlName)->m_PickerMap;
      if ($pickerMap) {
         $pickerList = $this->parsePickerMap($pickerMap);
         foreach ($pickerList as $ctrlPair) {
            $this_ctrl = $this->GetControl($ctrlPair[1]);
            if (!$this_ctrl)
               continue;
            $this_ctrl_val = $this_ctrl->GetValue();
            $other_ctrl = $prtForm->GetControl($ctrlPair[0]);
            if ($other_ctrl)
               $updArray[$other_ctrl->m_Name] = $this_ctrl_val;
         }
      }
      else {
         // - set up the new record to be the active record?
         $retRecord = $prtForm->GetDataObj()->JoinRecord($this->GetDataObj());
         /*
         // update the parent form fields on UI
         // !!! in case of new record, active record is not the new one 
         $rec = $prtForm->GetActiveRecord();
         foreach ($retRecord as $fld=>$val) {
            $rec[$fld] = $val;
         }
         $prtForm->UpdateActiveRecord($rec);
         
         // !!! rerender parent form will lose the user input on parent form
         return $prtForm->ReRender();
         */
         foreach ($retRecord as $fld=>$val) {
            $ctrl = $prtForm->m_RecordRow->GetControlByField($fld);
            if ($ctrl)
               $updArray[$ctrl->m_Name] = $val;
         }
      }
      $g_BizSystem->GetClientProxy()->UpdateFormElements($prtForm->m_Name, $updArray);
      return;
   }
   
   private function parsePickerMap($pickerMap)
   {
      $returnList = array();
      $pickerList = split(",", $pickerMap);
      foreach ($pickerList as $pair)
      {
         $ctrlMap = split(":", $pair);
         $ctrlMap[0] = trim($ctrlMap[0]);
         $ctrlMap[1] = trim($ctrlMap[1]);
         $returnList[] = $ctrlMap;
      }
      return $returnList;
   }
   
   /**
    * BizFormPopup::AddToParent() - M-M or M-1/1-1 popup OK button to add a record (popup) to the parent form
    * 
    * @return string HTML text
    */
   // todo: support multiple records
   public function AddToParent()
   {
      global $g_BizSystem;
      
      // todo: if grandparent's mode is new, commit the new record first
      
      $prtForm = $g_BizSystem->GetObjectFactory()->GetObject($this->m_ParentFormName);
      $rec = $this->GetActiveRecord();
      // add record to parent form's dataobj who is M-M or M-1/1-1 to its parent dataobj
      $ok = $prtForm->GetDataObj()->AddRecord($this->m_ActiveRecord, $bPrtObjUpdated);
      if (!$ok) 
         return $prtForm->ProcessDataObjError($ok);

      $this->Close();
      
      $html = "";
      // rerender parent form's driving form (its field is updated in M-1 case)
      if ($bPrtObjUpdated) { 
         $prt_prtForm = $g_BizSystem->GetObjectFactory()->GetObject($prtForm->GetParentForm());
         //$prt_prtForm->UpdateActiveRecord($prt_prtForm->GetDataObj()->GetRecord(0));
         $html = $prt_prtForm->ReRender();
      }
      // rerender the parent form
      // synch form with data
      //$prtForm->UpdateActiveRecord($prtForm->GetDataObj()->GetRecord(0));
	   return $html . $prtForm->ReRender();
   }
}
?>