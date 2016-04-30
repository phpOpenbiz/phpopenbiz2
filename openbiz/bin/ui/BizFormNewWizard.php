<?php
/**
 * BizFormWizard class - extension of BizForm to support wizard form
 *
 * @package BizView
 */
class BizFormNewWizard extends BizFormWizard
{
   protected $m_FormData = null;

   public function GetSessionVars($sessCtxt)
	{
      /*$sessCtxt->GetObjVar($this->m_Name, "FormData", $this->m_FormData);
      foreach ($this->m_RecordRow as $fldCtrl) {
         $fldCtrl->SetValue($this->m_FormData[$fldCtrl->m_Name]);
      }*/
	}

	public function SetSessionVars($sessCtxt)
	{
      //$sessCtxt->SetObjVar($this->m_Name, "FormData", $this->m_FormData);
	}

   public function GoNext($commit=false) //- call RenderNextWizardForm() by default
   {
      $viewobj = $this->GetViewObject();
      $nextFormName = $viewobj->GetNextWizardForm();
      if ($nextFormName != $this->m_Name) {
         $this->SetFormData();
         foreach ($this->m_RecordRow as $fldCtrl) {
            if ($fldCtrl->m_BizFieldName)
               $recArray[$fldCtrl->m_BizFieldName] = $this->m_FormData[$fldCtrl->m_Name];
         }
	 $this->ValidateForm();
         $ok = $this->GetViewObject()->ValidateWizardFormData($this->m_DataObjName, $recArray);

         if (!$ok) {
            return $this->ProcessDataObjError($ok);
         } else {
            $viewobj->RenderWizardForm($nextFormName);
         }
      }
   }

   public function GoPrev() //- call RenderPrevWizardForm() by default
   {
      $viewobj = $this->GetViewObject();
      $prevFormName = $viewobj->GetPrevWizardForm();
      if ($prevFormName != $this->m_Name) {
         $this->SetFormData();
         $viewobj->RenderWizardForm($prevFormName);
      }
   }

   public function DoFinish() //- call FinishWizard() by default
   {
      global $g_BizSystem;

      $postAction = $this->GetPostAction();
      if (!$postAction) {
         $g_BizSystem->GetClientProxy()->ShowErrorMessage("Your wizard process cannot be finished due to invalid PostAction of the finish action");
         return;
      }
      else {
         // commit wizard data
         $this->SetFormData();
         $this->GetViewObject()->CommitWizardData();

         $this->HandlePostAction($postAction);
         return;
      }
   }

   public function DoCancel() //- call CancelWizard() by default
   {
      global $g_BizSystem;
      // get postaction of the cancel button
      $postAction = $this->GetPostAction();
      if (!$postAction) {
         $g_BizSystem->GetClientProxy()->ShowErrorMessage("Your wizard process cannot be canceled due to invalid PostAction of the cancel action");
         return;
      }
      else {
         $this->HandlePostAction($postAction);
         return;
      }
   }

   protected function GetFormData()
   {
      $this->m_FormData = $this->GetViewObject()->GetWizardFormData($this->m_Name);
      $viewobj = $this->GetViewObject();
      if ($viewobj->GetDataObjState($this->m_DataObjName) != "IS_CREATED") {
         // if edit record, do a query. if new record, call newrecord()
         $recArray = $this->GetNewRecord();
         $viewobj->SetDataObjState($this->m_DataObjName, "IS_CREATED");
         $viewobj->SetDataObjectsData($this->m_DataObjName, $recArray);
      }
      // if Formdata is empty, get data from dataobj
      if (!$this->m_FormData) {
         foreach ($this->m_RecordRow as $fldCtrl) {
            if ($fldCtrl->m_BizFieldName) { // if control based on field
               $this->m_FormData[$fldCtrl->m_Name] = $viewobj->GetDataObjectsData($this->m_DataObjName, $fldCtrl->m_BizFieldName);
            }
            else {
               $this->m_FormData[$fldCtrl->m_Name] = "";
            }
         }
         //$this->$viewobj->SetWizardFormData($this->m_Name, $this->m_FormData);
      }

      foreach ($this->m_RecordRow as $fldCtrl) {
         $fldCtrl->SetValue($this->m_FormData[$fldCtrl->m_Name]);
      }
   }

   protected function SetFormData()
   {
      global $g_BizSystem;
      $recArray = array();
      foreach ($this->m_RecordRow as $fldCtrl) {
         $value = $g_BizSystem->GetClientProxy()->GetFormInputs($fldCtrl->m_Name);
         $this->m_FormData[$fldCtrl->m_Name] = $value;
         $fldCtrl->SetValue($this->m_FormData[$fldCtrl->m_Name]);
         if ($fldCtrl->m_BizFieldName)
            $recArray[$fldCtrl->m_BizFieldName] = $value;
      }
      $this->GetViewObject()->SetWizardFormData($this->m_Name, $this->m_FormData);
      $this->GetViewObject()->SetDataObjectsData($this->m_DataObjName, $recArray);
   }

   public function Render()
   {

      return $this->RenderHTML();
   }

   protected function RenderArray()
   {
      $columns = $this->m_RecordRow->RenderColumn();
      foreach($columns as $key=>$val)
         $fields[$key]["label"] = $val;

      if (!$this->m_ActiveRecord)
         $this->GetFormData();
      $controls = $this->m_RecordRow->Render();
      if ($this->CanShowData()) {
         foreach($controls as $key=>$val) {
            $fields[$key]["control"] = $val;
            $fields[$key]["required"] = $this->GetControl($key)->m_Required;
            $fields[$key]["description"] = $this->GetControl($key)->m_Description;
         }
      }
      return $fields;
   }

   public function GetControlValue($controlName)
   {
      return $this->m_FormData[$controlName];
   }
}
?>