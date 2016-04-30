<?php

/* define modes constants */
define ("MODE_R", "READ");   //READ
define ("MODE_N", "NEW");    //NEW
define ("MODE_E", "EDIT");    //EDIT
define ("MODE_Q", "QUERY");   //QUERY

/**
 * BizForm_Abstract class - contains form object metadata functions
 *
 * @package BizView
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */

include_once(OPENBIZ_BIN."/ui/private/RecordRow.php");
include_once(OPENBIZ_BIN."/ui/private/DisplayMode.php");
include_once(OPENBIZ_BIN."/ui/private/ToolBar.php");
include_once(OPENBIZ_BIN."/ui/private/NavBar.php");
include_once(OPENBIZ_BIN."/ui/HTMLControl.php");
include_once(OPENBIZ_BIN."/ui/FieldControl.php");

abstract class BizForm_Abstract extends MetaObject implements iSessionObject
{
   // metadata vars are public, necessary for metadata inheritance
   public $m_Title;
   public $m_jsClass;
   public $m_DataObjName;
   public $m_InheritFrom;
   public $m_Height = 640;
   public $m_Width = 800;
   public $m_Range = 10;
   public $m_FullPage = "Y";
   public $m_SearchRule = null;
   public $m_DisplayModes = null;
   public $m_RecordRow = null;
   public $m_ToolBar = null;
   public $m_NavBar = null;
   public $m_Parameters = array();
   
   protected $m_DataObj;
   protected $m_Mode = MODE_R;
   protected $m_SubForms = null;
   protected $m_FixSearchRule = ""; // rename to FixSearchRule which is the search rule always applying on the search

   protected $m_PostActionOff = false;
   protected $m_ParentFormName;
   protected $m_PrtCommitPending = false;

   // indicate the state of form. It's passed down to template
   protected $m_FormState = 0;   // 0 - default, positive int - success, negative int - error
   protected $m_Stateless = false;
   
   /**
    * BizForm::__construct(). Initialize BizForm with xml array
    *
    * @param array $xmlArr
    * @return void
    */
   function __construct(&$xmlArr)
   {
      global $g_BizSystem;

      $this->ReadMetadata($xmlArr);

      $this->InheritParentObj();
      
      $this->SetInitialDisplayMode();      
   }

   protected function ReadMetadata(&$xmlArr)
   {
      parent::ReadMetaData($xmlArr);
      $this->m_InheritFrom = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["INHERITFROM"]) ? $xmlArr["BIZFORM"]["ATTRIBUTES"]["INHERITFROM"] : null;
      $this->m_Title = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["TITLE"]) ? I18n::getInstance()->translate($xmlArr["BIZFORM"]["ATTRIBUTES"]["TITLE"]) : null;
      $this->m_SearchRule = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["SEARCHRULE"]) ? $xmlArr["BIZFORM"]["ATTRIBUTES"]["SEARCHRULE"] : null;
      $this->m_jsClass = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["JSCLASS"]) ? $xmlArr["BIZFORM"]["ATTRIBUTES"]["JSCLASS"] : null;
      $this->m_Height = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["HEIGHT"]) ? $xmlArr["BIZFORM"]["ATTRIBUTES"]["HEIGHT"] : null;
      $this->m_Width = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["WIDTH"]) ? $xmlArr["BIZFORM"]["ATTRIBUTES"]["WIDTH"] : null;
      $this->m_Range = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["PAGESIZE"]) ? $xmlArr["BIZFORM"]["ATTRIBUTES"]["PAGESIZE"] : null;
      $this->m_FullPage = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["FULLPAGE"]) ? $xmlArr["BIZFORM"]["ATTRIBUTES"]["FULLPAGE"] : null;
      $this->m_Stateless = isset($xmlArr["BIZFORM"]["ATTRIBUTES"]["STATELESS"]) ? $xmlArr["BIZFORM"]["ATTRIBUTES"]["STATELESS"] : null;

      $this->m_Name = $this->PrefixPackage($this->m_Name);
      $this->m_DataObjName = $this->PrefixPackage($xmlArr["BIZFORM"]["ATTRIBUTES"]["BIZDATAOBJ"]);

      $this->m_DisplayModes = new MetaIterator($xmlArr["BIZFORM"]["DISPLAYMODES"]["MODE"],"DisplayMode");
      $this->m_RecordRow = new RecordRow($xmlArr["BIZFORM"]["BIZCTRLLIST"]["BIZCTRL"],"FieldControl",$this);
      $this->m_ToolBar = new ToolBar($xmlArr["BIZFORM"]["TOOLBAR"]["CONTROL"],"HTMLControl",$this);
      $this->m_NavBar = new NavBar($xmlArr["BIZFORM"]["NAVBAR"]["CONTROL"],"HTMLControl",$this);
      $this->m_Parameters = new MetaIterator($xmlArr["BIZFORM"]["PARAMETERS"]["PARAMETER"],"Parameter");
   }

   protected function InheritParentObj()
   {
      if (!$this->m_InheritFrom) return;
      global $g_BizSystem;
      $prtObj = $g_BizSystem->GetObjectFactory()->GetObject($this->m_InheritFrom);

      $this->m_Title = $this->m_Title ? $this->m_Title : $prtObj->m_Title;
      $this->m_Description = $this->m_Description ? $this->m_Description : $prtObj->m_Description;
      $this->m_SearchRule = $this->m_SearchRule ? $this->m_SearchRule : $prtObj->m_SearchRule;
      $this->m_jsClass = $this->m_jsClass ? $this->m_jsClass: $prtObj->m_jsClass;
      $this->m_Height = $this->m_Height ? $this->m_Height: $prtObj->m_Height;
      $this->m_Width = $this->m_Width ? $this->m_Width: $prtObj->m_Width;
      $this->m_Range = $this->m_Range ? $this->m_Range: $prtObj->m_Range;
      $this->m_FullPage = $this->m_FullPage ? $this->m_FullPage: $prtObj->m_FullPage;
      $this->m_Stateless = $this->m_Stateless ? $this->m_Stateless: $prtObj->m_Stateless;
      $this->m_BizObjName = $this->m_BizObjName ? $this->m_BizObjName: $prtObj->m_BizObjName;

      $this->m_DisplayModes->merge($prtObj->m_DisplayModes);
      $this->m_RecordRow->merge($prtObj->m_RecordRow, $this->m_Name);
      foreach ($this->m_RecordRow as $ctrl) $ctrl->AdjustBizFormName($this->m_Name);
      $this->m_ToolBar->merge($prtObj->m_ToolBar, $this->m_Name);
      foreach ($this->m_ToolBar as $ctrl) $ctrl->AdjustBizFormName($this->m_Name);
      $this->m_NavBar->merge($prtObj->m_NavBar, $this->m_Name);
      foreach ($this->m_NavBar as $ctrl) $ctrl->AdjustBizFormName($this->m_Name);
      $this->m_Parameters->merge($prtObj->m_Parameters);
   }
   
   public function GetSessionVars($sessCtxt) {}
   
   public function SetSessionVars($sessCtxt) {}

    /* BizForm::SetInitialDisplayMode() - Set displaymode to READ first, otherwise set to first valid mode
     @return void
    */
   protected function SetInitialDisplayMode()
   {
      $valid_mode = $this->m_DisplayModes->get("READ");
      if ($valid_mode === null) {
         $this->m_DisplayModes->rewind();
         $valid_mode = $this->m_DisplayModes->current();
      }
      $this->m_Mode = $valid_mode->m_Name;
   }
   
   public function GetProperty($propertyName)
	{
	   $ret = parent::GetProperty($propertyName);
	   if ($ret) return $ret;
	   if ($propertyName == "Title") return $this->m_Title;
	   if ($propertyName == "jsClass") return $this->m_jsClass;
	   if ($propertyName == "DataObjName") return $this->m_DataObjName;
      // get control object if propertyName is "Control[ctrlname]"
      $pos1 = strpos($propertyName, "[");
      $pos2 = strpos($propertyName, "]");
      if ($pos1>0 && $pos2>$pos1) {
         $propType = substr($propertyName, 0, $pos1);
         $ctrlname = substr($propertyName, $pos1+1,$pos2-$pos1-1);
         if ($propType == "param") {   // get parameter
            return $this->m_Parameters->get($ctrlname);
         }
         return $this->GetControl($ctrlname);
      }
	}

	public function GetParameter($paramName)
	{
	   //return $this->m_Parameters[$paramName]->m_Value;
	   return $this->m_Parameters->get($paramName)->m_Value;
	}
	
	
	/**
    * BizForm::GetDisplayMode() - get display mode object
    *
    * @return DisplayMode
    */
   final public function GetDisplayMode()
   {
      if (($dispmode = $this->m_DisplayModes->get($this->m_Mode)))
         return $dispmode;
      foreach ($this->m_DisplayModes as $dispmode) {
         return $dispmode;
      }
      $errmsg = BizSystem::GetMessage("ERROR", "BFM_ERROR_INVALID_DISPMODE",array($this->m_Name));
      trigger_error($errmsg, E_USER_ERROR);
   }
   /**
    * BizForm::SetDisplayMode() - set display mode as given mode
    *
    * @param string $mode - MODE_R|MODE_N|MODE_E|MODE_Q
    * @return void
    */
   public function SetDisplayMode($mode)
   {
      if (!$mode AND !$this->m_Mode) {
         $mode = MODE_R;
      } elseif (!$mode) {
         $mode=$this->m_Mode;
      };
      $this->m_Mode = $mode;
      $dispmode = $this->m_DisplayModes->get($this->m_Mode);
      $this->m_RecordRow->SetMode($mode, $dispmode->m_DataFormat);
      $this->m_ToolBar->SetMode($mode, $dispmode->m_DataFormat);
      $this->m_NavBar->SetMode($mode, $dispmode->m_DataFormat);
   }

	/**
    * BizForm::GetDataObj() - get object instance of BizDataObj defined in its metadata file
    *
    * @return BizDataObj
    */
	final public function GetDataObj()
	{
	   global $g_BizSystem;
	   if (!$this->m_DataObj) {
	     if ($this->m_DataObjName)
           $this->m_DataObj = $g_BizSystem->GetObjectFactory()->GetObject($this->m_DataObjName);
           if($this->m_DataObj)
            $this->m_DataObj->m_BizFormName = $this->m_Name;
	   }
      return $this->m_DataObj;
	}

	final public function SetDataObj($dataObj)
	{
	   $this->m_DataObj = $dataObj;
	}

	/**
    * BizForm::ProcessDataObjError() - handle the error from DataObj method, report the error as an alert window
    *
    * @param int $errCode
    * @return string
    */
	public function ProcessDataObjError($errCode=0)
	{
	   global $g_BizSystem;
      $errorMsg = $this->GetDataObj()->GetErrorMessage();
      BizSystem::log(LOG_DEBUG, "DATAOBJ", "DataObj error = ".$errorMsg);
      $g_BizSystem->GetClientProxy()->ShowErrorMessage($errorMsg);
	}
	
	/**
    * BizForm::ProcessDataObjException() - handle the exception from DataObj method, report the error as an alert window
    *
    * @param int $errCode
    * @return string
    */
	public function ProcessBDOException($e)
	{
	   global $g_BizSystem;
      $errorMsg = $e->getMessage();
      BizSystem::log(LOG_DEBUG, "DATAOBJ", "DataObj error = ".$errorMsg);
      //$g_BizSystem->GetClientProxy()->ShowErrorMessage($errorMsg);
      BizSystem::ClientProxy()->ShowErrorMessage($errorMsg);
	}
	
	/**
    * BizForm::SetSearchRule() - set the search rule of the bizform, this search rule will apply on its bizdataobj
    *
    * @param string $rule - search rule has format "[fieldName1] opr1 Value1 AND/OR [fieldName2] opr2 Value2"
    * @param boolean $overwrite specify if this rule should overwrite any existing rule
    * @return void
    */
   public function SetSearchRule($rule = null, $overwrite=false)
	{
      if (!$rule) {
         return;
      } elseif (!$this->m_SearchRule or $overwrite == true) {
         $this->m_SearchRule = $rule;
      } elseif (strpos($this->m_SearchRule, $rule) === false) {
            $this->m_SearchRule .= " AND " . $rule;
      }
      
      BizSystem::log(LOG_DEBUG,"FORMOBJ",$this->m_Name." SetSearch() ". $this->m_SearchRule);
	}

	/**
    * BizForm::SetFixSearchRule() - set the dependent search rule of the bizform, this search rule will apply on its bizdataobj.
    * The dependent search rule (session var) will always be with bizform until it get set to other value
    *
    * @param string $rule - search rule has format "[fieldName1] opr1 Value1 AND/OR [fieldName2] opr2 Value2"
    * @return void
    */
	public function SetFixSearchRule($rule = null)
	{
      if ($this->m_FixSearchRule && $rule)
      {
         if (strpos($this->m_FixSearchRule, $rule) === false)
            $this->m_FixSearchRule = $this->m_FixSearchRule . " AND " . $rule;
      }
      if (!$this->m_FixSearchRule && $rule)
         $this->m_FixSearchRule = $rule;
	}
	
	/**
    * BizForm::SetSubForms() - set the sub forms of this form. This form is parent of other forms
    *
    * @param string $subForms - sub controls string with format: ctrl1;ctrl2...
    * @return void
    */
	final public function SetSubForms($subForms)
   {
      // sub controls string with format: ctrl1;ctrl2...
      if (!$subForms || strlen($subForms) < 1) {
         $this->m_SubForms = null;
         return;
      }
      $subFormArr = split(";", $subForms);
      unset($this->m_SubForms);
      foreach ($subFormArr as $subForm) {
         $this->m_SubForms[] = $this->PrefixPackage($subForm);
      }
   }

   public function GetViewObject() {
      global $g_BizSystem;
      $viewName = $g_BizSystem->GetCurrentViewName();
      $viewobj = $g_BizSystem->GetObjectFactory()->GetObject($viewName);
      return $viewobj;
   }

   public function GetSubForms() { return $this->m_SubForms; }

   public function GetParentForm() { return $this->m_ParentFormName; }

   public function SetParentForm($prtFormName) { $this->m_ParentFormName = $prtFormName; }

   public function GetPrtCommitPending() { return $this->m_PrtCommitPending; }

   public function SetPrtCommitPending($flag) { $this->m_PrtCommitPending = $flag; }

   public function GetFormState($state) { return $this->m_FormState; }

   public function SetFormState($state) { $this->m_FormState = $state; }

   protected function CanShowData() { return !$this->GetPrtCommitPending(); } // parent form has new record pending

   /**
    * BizForm::GetControl() - get a control (FieldControl or HTMLControl) object
    *
    * @param string $ctrlname - name of the control
    * @return HTMLControl or FieldControl
    */
	public function GetControl($ctrlname)
	{
	   if ($this->m_RecordRow->get($ctrlname)) return $this->m_RecordRow->get($ctrlname);
	   if ($this->m_ToolBar->get($ctrlname)) return $this->m_ToolBar->get($ctrlname);
	   if ($this->m_NavBar->get($ctrlname)) return $this->m_NavBar->get($ctrlname);
	}

   /**
    * BizForm::GetDisplayControls() - get an array of control names based on the bizform's current displaymode
    *
    * @return Array of Control objects
    */
	public function GetDisplayControls()
	{
	   $ctr_List = $this->m_RecordRow->GetSortControlKeys();
	   foreach ($ctr_List as $value) {
	      $ctrl = $this->m_RecordRow->GetControlByField($value);
	      if  ($ctrl->CanDisplayed()) {
	         $displayControls[] = $value;
	      }
	   }
      return $displayControls;
	}
	
	/**
    * BizForm::ShowPopup() - Popup a selection BizForm in a dynamically generated BizView
    *
    * @param string $formName - the popup bizform
    * @return string - HTML text of popup view
    */
   public function ShowPopup($formName, $ctrlName="")
   {
      $this->SendUserInput();
      // generate an xml attribute array for a dynamic bizview
      $xmlArr = BizView::GetPopupViewXML($this->m_Package, $formName);
      // create a BizViewPopup with the xml array
      global $g_BizSystem;
      $popupView = $g_BizSystem->GetObjectFactory()->CreateObject("DynPopup",$xmlArr);
      $formName = $this->PrefixPackage($formName);
      // set the ParentFormName and ParentCtrlName of the popup form
      $popupForm = $g_BizSystem->GetObjectFactory()->GetObject($formName);
      $popupForm->SetParentForm($this->m_Name);
      $popupForm->m_PrtFormCtrlName = $ctrlName;
      // set the dimension of the popup
      $w = $popupForm->m_Width ? $popupForm->m_Width : 640;
      $h = $popupForm->m_Height ? $popupForm->m_Height : 480;
      $popupView->SetPopupSize($w, $h);
      // clean history
      $popupView->CleanViewHistory();
      // render the popup
      $popupView->Render();
      // exit;
   }
   

   /**
    * BizForm::CallService() - invoke service method, this bizform name is passed to the method
    *
    * @param string $class
    * @param string $method
    * @return mixed - return value of the service method
    */
   public function CallService($class, $method)
   {
      global $g_BizSystem;
      //$ret = $g_BizSystem->CallService($class, $method, $this->m_Name);
      $svcobj = $g_BizSystem->GetService($class);
      $svcobj->$method($this->m_Name);
   }
	
	/*abstract public function GotoPage($page=1);
	
	abstract public function NextPage();
	
	abstract public function PrevPage();
	
	abstract public function SortRecord($sort_col);
	
	abstract public function RunSearch();
	
	abstract public function NewRecord();
	
	abstract public function EditRecord();
	
	abstract public function SaveRecord();
	
	abstract public function DeleteRecord();
	
	abstract public function Cancel();*/
	
	abstract public function Render();
	
}
?>