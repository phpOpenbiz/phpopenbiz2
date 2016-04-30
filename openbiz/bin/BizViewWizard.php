<?PHP
/**
 * BizViewWizard class - BizViewWizard is the class that controls the wizard forms
 *
 * @package BizView
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */
class BizViewWizard extends BizView
{
   protected $m_WizardFormsData = array();  // should be saved in session
   protected $m_WizardFormIndex = 0;
   protected $m_DataObjectsInfo = array();   // keep track of DO's isQueried, isCommitted flags
   protected $m_DataObjectsData = array();

   public function __construct(&$xmlArr)
   {
      parent::__construct($xmlArr);
   }

   public function GetSessionVars($sessCtxt)
	{
	   $sessCtxt->GetObjVar($this->m_Name, "WizardFormsData", $this->m_WizardFormsData);
	   $sessCtxt->GetObjVar($this->m_Name, "WizardFormIndex", $this->m_WizardFormIndex);
	   $sessCtxt->GetObjVar($this->m_Name, "DataObjectsData", $this->m_DataObjectsData);
	   $sessCtxt->GetObjVar($this->m_Name, "DataObjectsInfo", $this->m_DataObjectsInfo);
	}

	public function SetSessionVars($sessCtxt)
	{
	   $sessCtxt->SetObjVar($this->m_Name, "WizardFormsData", $this->m_WizardFormsData);
	   $sessCtxt->SetObjVar($this->m_Name, "WizardFormIndex", $this->m_WizardFormIndex);
	   $sessCtxt->SetObjVar($this->m_Name, "DataObjectsData", $this->m_DataObjectsData);
	   $sessCtxt->SetObjVar($this->m_Name, "DataObjectsInfo", $this->m_DataObjectsInfo);
	}

	// do not initiate the all forms
	protected function InitAllForms() {}

   public function RenderWizardForm($formName, $isFirstTime=false)
   {
      global $g_BizSystem;
      $smarty = BizSystem::GetSmartyTemplate();

      $formobj = $g_BizSystem->GetObjectFactory()->GetObject($formName);

      if ($formobj->m_DataObjName && !key_exists($formobj->m_DataObjName, $this->m_DataObjectsData))
         $this->SetDataObjState($formobj->m_DataObjName, "");

      $htmlContainer = "\n<div id='" . $formobj->m_Name . "_container'>\n" . $formobj->Render() . "\n</div>\n";
      $newClntObj = "NewObject('" . $formobj->m_Name . "','" . $formobj->m_jsClass . "');";
      if ($isFirstTime)
      {
         $sHTML = "\n<script>\n" . $newClntObj . "\n</script>\n" . $htmlContainer;
         return $sHTML;
      }
      else   // call clientproxy redrawForm
      {
         $g_BizSystem->GetClientProxy()->RunClientScript($newClntObj);
         $g_BizSystem->GetClientProxy()->ReDrawForm($this->m_Name, $htmlContainer);
         return;
      }
   }

   public function GetCurWizardForm()
   {
      $formName = $this->m_MetaChildFormList[$this->m_WizardFormIndex]["FORM"];
      return $this->PrefixPackage($formName);
   }

   public function GetNextWizardForm()
   {
      $this->m_WizardFormIndex++;
      if ($this->m_WizardFormIndex >= count($this->m_MetaChildFormList))
         $this->m_WizardFormIndex = count($this->m_MetaChildFormList) - 1;
      $formName = $this->m_MetaChildFormList[$this->m_WizardFormIndex]["FORM"];
      return $this->PrefixPackage($formName);
   }

   public function GetPrevWizardForm()
   {
      $this->m_WizardFormIndex--;
      if ($this->m_WizardFormIndex < 0)
         $this->m_WizardFormIndex = 0;
      $formName = $this->m_MetaChildFormList[$this->m_WizardFormIndex]["FORM"];
      return $this->PrefixPackage($formName);
   }

   public function GetDataObjState($dataobjName)
   {
      return $this->m_DataObjectsInfo[$dataobjName];
   }

   public function SetDataObjState($dataobjName, $state)
   {
      return $this->m_DataObjectsInfo[$dataobjName] = $state;
   }

   public function GetDataObjectsData($dataobjName, $fldName)
   {
      return $this->m_DataObjectsData[$dataobjName][$fldName];
   }

   public function SetDataObjectsData($dataobjName, &$recArray)
   {
      foreach ($recArray as $fldName=>$fldValue)
         $this->m_DataObjectsData[$dataobjName][$fldName] = $fldValue;
   }

   public function GetWizardFormData($wizardForm, $controlName=null)
   {
      if ($controlName == null)
         return $this->m_WizardFormsData[$wizardForm];
      return $this->m_WizardFormsData[$wizardForm][$controlName];
   }

   public function ValidateWizardFormData($dataobjName, $dataArray)
   {
      global $g_BizSystem;
      $dataObj = $g_BizSystem->GetObjectFactory()->GetObject($dataobjName);
      $dataObj->m_BizRecord->SetInputRecord($dataArray);
      return $dataObj->ValidateInput();
   }

   public function SetWizardFormData($wizardForm, &$dataArray)
   {
      foreach ($dataArray as $ctrlName=>$ctrlValue)
         $this->m_WizardFormsData[$wizardForm][$ctrlName] = $ctrlValue;
   }

   public function CommitWizardData()
   {
      // commit all dataobjects of wizard forms
      global $g_BizSystem;

      foreach($this->m_DataObjectsData as $dataobjName=>$dataArray)
      {
         $dataobj = $g_BizSystem->GetObjectFactory()->GetObject($dataobjName);
         //Reload dataArray to account for new ObjRef generated values
         $dataArray = $this->m_DataObjectsData[$dataobjName];

         if ($this->m_DataObjectsInfo[$dataobjName] == "IS_CREATED") {
            $dataobj->InsertRecord($dataArray);
         } else {
            $dataobj->UpdateRecord($dataArray, $oldRec);
         }
         $this->_UpdateReferences($dataobj, $dataArray);
      }
   }

   public function _UpdateReferences($dataObj, $dataArray) {
      //Check for object references
      $objRef = $dataObj->m_ObjReferences;
      foreach($this->m_DataObjectsData as $dataobjName=>$someArray)
      {
         $match = $objRef->get($dataobjName);
         if($match) {
            $this->m_DataObjectsData[$dataobjName][$match->m_Column] = $dataArray[$match->m_FieldRef];
         }

      }
   }

   public function RenderProgressBar() {}

   public function Render($bReRender=false)
   {
      $smarty = BizSystem::GetSmartyTemplate();
	   global $g_BizSystem;
	   
      $this->SetClientScripts();
      echo implode("\n", $g_BizSystem->GetClientProxy()->GetAppendedScripts());

      // render progress bar

      // render only current wizard form, not all forms
      $formName = $this->GetCurWizardForm();
      // ouput the form into the wizard container
      $sHTML = $this->RenderWizardForm($formName, true);
      $controls[] = "<div id='" . $this->m_Name . "'>" . $sHTML . "</div>\n";

      $smarty->assign_by_ref("view_description", $this->m_Description);

      $smarty->assign_by_ref("controls", $controls);

      if ($this->m_ConsoleOutput)
         $smarty->display($this->m_Template);
      else
         return $smarty->fetch($this->m_Template);
   }
}

?>