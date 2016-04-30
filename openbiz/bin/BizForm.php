<?PHP
/* define modes constants */
define ("MODE_R", "READ");   //READ
define ("MODE_N", "NEW");    //NEW
define ("MODE_E", "EDIT");    //EDIT
define ("MODE_Q", "QUERY");   //QUERY

/**
 * BizForm class - BizForm is the base class that contains UI controls.
 * BizForm is a html form that is included in a BizView which is a html page.
 *
 * @package BizView
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */
class BizForm extends MetaObject implements iSessionObject
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
   public $m_CursorIndex = 0;
   protected $m_CursorIDMap = array();
   protected $m_Mode = MODE_R;
   protected $m_OnSortField;
   protected $m_OnSortFlag;
   public $m_ActiveRecord = null;
   protected $m_RecordChanged = false;
   protected $m_SubForms = null;
   protected $m_FixSearchRule = ""; // rename to FixSearchRule which is the search rule always applying on the search
   protected $m_SortedControlKeys = null;

   protected $m_HistoryInfo;
   protected $m_NoHistoryInfo = false;
   protected $m_HistRecordId = null;

   protected $m_PostActionOff = false;
   protected $m_ParentFormName;
   protected $m_PrtCommitPending = false;

   // indicate the state of form. It's passed down to template
   protected $m_FormState = 0;   // 0 - default, positive int - success, negative int - error
   protected $m_QueryONRender = true;
   protected $m_ClearSearchRule = false;
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

   /**
    * BizForm::GetSessionContext() - Retrieve Session data of this object
    *
    * @param SessionContext $sessCtxt
    * @return void
    */
   //todo: pack session vars into a single array
	public function GetSessionVars($sessCtxt)
	{
	   if ($this->m_Stateless == "Y")
	       return;
      $sessCtxt->GetObjVar($this->m_Name, "Mode", $mode);

      $sessCtxt->GetObjVar($this->m_Name, "CursorIndex", $this->m_CursorIndex);
      $sessCtxt->GetObjVar($this->m_Name, "CursorIDMap", $this->m_CursorIDMap);
      $sessCtxt->GetObjVar($this->m_Name, "SubForms", $this->m_SubForms);
      $sessCtxt->GetObjVar($this->m_Name, "ParentFormName", $this->m_ParentFormName);
      $sessCtxt->GetObjVar($this->m_Name, "PrtCommitPending", $this->m_PrtCommitPending);
      $sessCtxt->GetObjVar($this->m_Name, "FixSearchRule", $this->m_FixSearchRule);
      $sessCtxt->GetObjVar($this->m_Name, "OnSortField", $this->m_OnSortField);
      $sessCtxt->GetObjVar($this->m_Name, "OnSortFlag", $this->m_OnSortFlag);
      $sessCtxt->GetObjVar($this->m_Name, "ActiveRecord", $this->m_ActiveRecord);

      $this->m_HistoryInfo = $sessCtxt->GetViewHistory($this->m_Name);

      $this->SetDisplayMode ($mode);
      if ($this->m_ActiveRecord)
         $this->m_RecordRow->SetRecordArr($this->m_ActiveRecord);
      $this->SetSortFieldFlag($this->m_OnSortField, $this->m_OnSortFlag);
	}

	/**
    * BizForm::SetSessionContext() - Save Session data of this object
    *
    * @param SessionContext $sessCtxt
    * @return void
    */
	public function SetSessionVars($sessCtxt)
	{
	   if ($this->m_Stateless == "Y")
	       return;
      $sessCtxt->SetObjVar($this->m_Name, "Mode", $this->m_Mode);
      $sessCtxt->SetObjVar($this->m_Name, "CursorIndex", $this->m_CursorIndex);
      $sessCtxt->SetObjVar($this->m_Name, "CursorIDMap", $this->m_CursorIDMap);
      $sessCtxt->SetObjVar($this->m_Name, "SubForms", $this->m_SubForms);
      $sessCtxt->SetObjVar($this->m_Name, "ParentFormName", $this->m_ParentFormName);
      $sessCtxt->SetObjVar($this->m_Name, "PrtCommitPending", $this->m_PrtCommitPending);
      $sessCtxt->SetObjVar($this->m_Name, "FixSearchRule", $this->m_FixSearchRule);
      $sessCtxt->SetObjVar($this->m_Name, "OnSortField", $this->m_OnSortField);
      $sessCtxt->SetObjVar($this->m_Name, "OnSortFlag", $this->m_OnSortFlag);
      //$sessCtxt->SetObjVar($this->m_Name, "ActiveRecord", $this->m_ActiveRecord);

	   $sessCtxt->SetViewHistory($this->m_Name, $this->GetHistoryInfo());
	}

	public function ClearSessionVars($sessCtxt)
	{
	   $this->m_NoHistoryInfo = true;
	}

	/**
    * BizForm::GetHistoryInfo() - get history info array
    *
    * @return array
    */
	public function GetHistoryInfo()
	{
	   if ($this->m_Stateless == "Y")
	       return;
	   if (!$this->m_NoHistoryInfo && $this->m_DataObj != null)
	   {
	      $histInfo = $this->m_DataObj->GetBookmark();
	      // add display mode in history info ???
	      return $histInfo;
	   }
	   return null;
	}

	/**
    * BizForm::CleanHistoryInfo() - clear history info so that the data set is fresh
    *
    * @return array
    */
	public function CleanHistoryInfo()
	{
	   $this->m_HistoryInfo = null;
	}

	protected function SetPostActionOff() { $this->m_PostActionOff = true; }

	// get post action url/view/... This method is called in Render() to determine the post action
	protected function GetPostAction()
	{
	   if ($this->m_PostActionOff) return null;
	   // check if the current rpc call has postaction specified
	   global $g_BizSystem;
      // get the control that issues the call
      // __this is ctrlname:eventhandlername
	   $ctrlname_ehname = $g_BizSystem->GetClientProxy()->GetFormInputs("__this");
	   if (!$ctrlname_ehname) return null;
	   list($ctrlname, $ehname) = split(":", $ctrlname_ehname);
	   $ctrlobj = $this->GetControl($ctrlname);
	   if (!$ctrlobj) return null;
	   $postAction = $ctrlobj->GetPostAction($ehname);  // need to get postaction of eventhandler
	   if ($postAction)
	      return $postAction;
      return null;
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
    * BizForm::SetCursorIndex() - set the current cursor index to the given value, dataobj's cursor is changed accordingly.
    *
    * @param int $cursorIndex
    * @return void
    */
	final protected function SetCursorIndex($cursorIndex)
	{
	   //global $g_BizSystem;
	   //$g_BizSystem->ErrorBacktrace();
	   $this->m_CursorIndex = $cursorIndex;
      if (($do = $this->GetDataObj()) != null) {
         $do->SetActiveRecordId($this->m_CursorIDMap[$this->m_CursorIndex]);
      }
      //$this->UpdateActiveRecord($this->GetDataObj()->GetRecord(0));
	}

	/**
    * BizForm::UpdateActiveRecord() - update the active record with given record array
    *
    * @param array $recArr
    * @return void
    */
	final public function UpdateActiveRecord($recArr)
	{
	   $this->m_ActiveRecord = $recArr;
      $this->m_RecordRow->SetRecordArr($this->m_ActiveRecord);  // needed ???
	}

	protected function GetActiveRecord()
	{
	   BizSystem::log(LOG_DEBUG,"FORMOBJ",$this->m_Name."::GetActiveRecord()");
	   if (!$this->m_ActiveRecord)
	   {
	      if ($this->GetDataObj() == null)
	         return null;
	      if (isset($this->m_CursorIDMap[$this->m_CursorIndex]))
	         $this->GetDataObj()->SetActiveRecordId($this->m_CursorIDMap[$this->m_CursorIndex]);
	      $this->m_ActiveRecord = $this->GetDataObj()->GetActiveRecord();
	      // update the record row
	      $this->m_RecordRow->SetRecordArr($this->m_ActiveRecord);
	   }
	   return $this->m_ActiveRecord;
	}

	// toggle to different BizForm.
	public function ToggleForm($otherForm, $parentForm)
	{

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
         $this->m_FixSearchRule = $this->m_FixSearchRule . " AND " . $rule;
      if (!$this->m_FixSearchRule && $rule)
         $this->m_FixSearchRule = $rule;
	}

	/**
    * BizForm::_run_search() - call RunSearch of its dataobj by applying its FixSearchRule and SearchRule
    * Its dataobj current search rule will be replaced by its FixSearchRule and SearchRule.
    *
    * @return void
    */
	public function _run_search(&$resultRecords, $clearSearchRule=true)
   {
      if (!$this->m_DataObjName)
         return;
      $dataobj = $this->GetDataObj();
      if (strlen($this->m_FixSearchRule) > 0) {
         if (strlen($this->m_SearchRule) > 0)
            $this->m_SearchRule .= " AND " . $this->m_FixSearchRule;
         else
            $this->m_SearchRule = $this->m_FixSearchRule;
      }
      if ($clearSearchRule)
         $dataobj->ClearSearchRule();
      $dataobj->SetSearchRule($this->m_SearchRule);
      $dataobj->SetPageRange($this->m_Range);
      return $dataobj->RunSearch($resultRecords);
   }

   public function GotoPage($page=1)
   {
      if (!$this->GetDataObj()->GotoPage($page))
         return;
      $this->m_CursorIndex = 0;
      return $this->ReRender();
   }

   /**
    * BizForm::NextPage() - move to next page
    *
    * @return string - html content of next page
    */
   public function NextPage()
   {
      if (!$this->GetDataObj()->NextPage())
         return;
      $this->m_CursorIndex = 0;
      return $this->ReRender();
   }
   /**
    * BizForm::PrevPage() - move to previous page
    *
    * @return string - html content of previous page
    */
   public function PrevPage()
   {
      if(!$this->GetDataObj()->PrevPage())
         return;
      $this->m_CursorIndex = 0;
      return $this->ReRender();
   }
   /**
    * BizForm::SelectRecord() - Select the record to selected row (if form show list of records)
    *
    * @return string - HTML content of this form and its sub forms whose content are changed with their parent form change
    */
   public function SelectRecord()
   {
      global $g_BizSystem;
      $rownum = $g_BizSystem->GetClientProxy()->GetFormInputs("__SelectedRow");
      if ($rownum < 1 || $rownum > $this->m_Range)
         return;
      $cursorIndex = $rownum-1;
      if ($this->m_CursorIndex == $cursorIndex)
         return;
      $this->m_CursorIndex = $cursorIndex;
      // clean the activerecord when record is changed
      $this->m_ActiveRecord = null;
      return $this->ReRender(false);   // not redraw the this form, but draw the subforms
   }

   /**
    * BizForm::SearchRecord() - show the query record mode
    *
    * @return string - HTML text of this form's query mode
    */
   public function SearchRecord()
   {
      $this->UpdateActiveRecord(null);
      $this->m_QueryONRender = false;
      $this->SetDisplayMode(MODE_Q);
      return $this->ReRender(true,false);
   }
   /**
    * BizForm::NewRecord() - show the new record mode
    *
    * @return string - HTML text of this form's new mode
    */
   public function NewRecord()
   {
      global $g_BizSystem;
      $this->SetDisplayMode(MODE_N);
      $recArr = $this->GetNewRecord();
      if (!$recArr)
         return $this->ProcessDataObjError();
      $this->UpdateActiveRecord($recArr);
      // TODO: popup message of new record successful created
      return $this->ReRender();
   }
   protected function GetNewRecord()
   {
      $recArr = $this->GetDataObj()->NewRecord();
      if (!$recArr)
         return $this->ProcessDataObjError();
      // load default values if new record value is empty
      $default_recArr = $this->m_RecordRow->GetDefaultRecordArr();
      foreach ($recArr as $field=>$val) {
         if ($val == "" && $default_recArr[$field] != "")
            $recArr[$field] = $default_recArr[$field];
      }
      return $recArr;
   }
   /**
    * BizForm::EditRecord() - edit the record of current row
    *
    * @return string - HTML text of this form's edit mode
    */
   public function EditRecord()
   {
      $rec = $this->GetActiveRecord();
      if (!$rec) return;
      $this->UpdateActiveRecord($rec);
      $this->SetDisplayMode(MODE_E);
      return $this->ReRender(true,false);
   }

   /**
    * BizForm::ReadInputRecord() - read user input data from UI
    *
    * @param array - record array read in as output
    * @return boolean - indicate whether the input is read successfully
    */
   protected function ReadInputRecord(&$recArr)
   {
      global $g_BizSystem;
      foreach ($this->m_RecordRow as $fldCtrl) {
         if ($fldCtrl->CanDisplayed()) {
            $value = $g_BizSystem->GetClientProxy()->GetFormInputs($fldCtrl->m_Name);
            $recArr[$fldCtrl->m_BizFieldName] = $value;
         }
      }
      return true;
   }

   // default form validation do nothing.
   // developers need to override this method to implement their logic
   protected function ValidateForm()
   {
      return true;
   }

   /**
    * BizForm::SaveRecord() - Save current edited record with input
    *
    * @return string - HTML text of this form's read mode
    */
   public function SaveRecord()
   {
      // call ValidateForm()
      if ($this->ValidateForm() == false)
          return;

      $recArr = array();
      if ($this->ReadInputRecord($recArr) == false)
         return;

      /*if ($this->m_Mode == MODE_N)
         $ok = $this->GetDataObj()->InsertRecord($recArr);
      else if ($this->m_Mode == MODE_E)
      {
         // merge input array with the activeRecord. Todo: should only merge hidden fieldcontrols
         // consider new record case - no active record yet
         $oldRec = $this->GetActiveRecord();
         if (isset($oldRec))
            $recArr = array_merge($oldRec, $recArr);
         $ok = $this->GetDataObj()->UpdateRecord($recArr);
      }*/
      if ($this->m_Mode == MODE_N)
         $dataRec = new DataRecord(null, $this->GetDataObj());
      else if ($this->m_Mode == MODE_E)
         $dataRec = new DataRecord($this->GetActiveRecord(), $this->GetDataObj());
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
   /**
    * BizForm::DeleteRecord() - Delete the record of current row
    *
    * @return string - HTML text of this form's current mode
    */
   public function DeleteRecord()
   {
      $rec = $this->GetActiveRecord();
      if (!$rec) return;
      global $g_BizSystem;
      //$recId = $this->m_ActiveRecord["Id"];
      $ok = $this->GetDataObj()->DeleteRecord($rec);
      if (!$ok)
         return $this->ProcessDataObjError($ok);

      return $this->ReRender();
   }
   /**
    * BizForm::RemoveRecord() - Remove the record out of the associate relationship
    *
    * @return string - HTML text of this form's current mode
    */
   public function RemoveRecord()
   {
      $rec = $this->GetActiveRecord();
      global $g_BizSystem;
      $ok = $this->GetDataObj()->RemoveRecord($rec,$bPrtObjUpdated);
      if (!$ok)
         return $this->ProcessDataObjError($ok);

      $html = "";
      // rerender parent form's driving form (its field is updated in M-1 case)
      if ($bPrtObjUpdated) {
         $prtForm = $g_BizSystem->GetObjectFactory()->GetObject($this->m_ParentFormName);
         $html = $prtForm->ReRender();
      }
      //$this->UpdateActiveRecord($this->GetDataObj()->GetRecord(0));
      return $html . $this->ReRender();
   }

   /**
    * BizForm::CopyRecord() - Copy current record and paste to a new record
    * Note: copy record will error out if db table uses composite columns as its primary key
    *
    * @return string - HTML text of this form's current mode
    */
   public function CopyRecord()
   {
      $rec = $this->GetActiveRecord();
      if (!$rec) return;
      foreach ($rec as $k=>$v)
         $rec[$k] = addslashes($v);
      global $g_BizSystem;
      // get new record array
      $recArr = $this->GetDataObj()->NewRecord();

      $rec["Id"] = $recArr["Id"]; // replace with new Id field
      $this->m_RecordRow->SetRecordArr($rec);
      $ok = $this->GetDataObj()->InsertRecord($rec);
      if (!$ok)
         return $this->ProcessDataObjError($ok);
      return $this->ReRender();
   }

   /**
    * BizForm::SortRecord() - sort record on given column
    *
    * @param strinf $sort_col with format as "fieldControl,1|0" which means sorting on field by ASC|DESC
    * @return string - HTML text of this form's current mode
    */
   public function SortRecord($sort_col)
   {
      $pos = strpos($sort_col, ",");
      if ($pos > 0)
         $reverse_flag = substr($sort_col, $pos + 1);
      $sortflag = ($reverse_flag == 1) ? "DESC" : "ASC";
      $sort_col = substr($sort_col, 0, $pos);

      // change the OnSortField
      $this->m_OnSortField = $sort_col;
      $this->m_OnSortFlag = $sortflag;

      // turn off the OnSort flag of the old onsort field
      $this->SetSortFieldFlag($this->m_OnSortField, null);

      // turn on the OnSort flag of the new onsort field
      $this->SetSortFieldFlag($this->m_OnSortField, $sortflag);

      // change the sort rule and issue the query
      $this->GetDataObj()->SetSortRule("[" . $this->GetControl($this->m_OnSortField)->m_BizFieldName . "] " . $sortflag);

      return $this->ReRender();
   }

   protected function SetSortFieldFlag($sortFld, $sortFlag)
   {
      if ($sortFld) {
         $fldCtrl = $this->GetControl($sortFld);
         $fldCtrl->SetSortFlag($sortFlag);
      }
   }

   /**
    * BizForm::Cancel() - Cancel current edit or query, then go read mode
    *
    * @return string - HTML text of this form's current mode
    */
   public function Cancel()
   {
      $this->m_CursorIndex = $this->m_CursorIndex;
      $prevMode = $this->m_Mode;
      $this->SetDisplayMode(MODE_R);
      if ($prevMode == MODE_N)   // NEW mode to READ mode, has record change, need to refresh the subforms
         return $this->ReRender(true, true);
      // EDIT to READ, no record change
      return $this->ReRender(true,false);
   }

   /**
    * BizForm::RunSearch() - Run search on query mode, then go read mode
    *
    * @return string - HTML text of this form's read mode
    */
   public function RunSearch()
   {
      BizSystem::log(LOG_DEBUG,"FORMOBJ",$this->m_Name."::RunSearch()");
      global $g_BizSystem;
      $this->m_SearchRule = "";
      foreach ($this->m_RecordRow as $fldCtrl) {
         $value = $g_BizSystem->GetClientProxy()->GetFormInputs($fldCtrl->m_Name);
         if ($value) {
            $searchStr = $this->InputValToRule($fldCtrl->m_BizFieldName, $value);
            if ($this->m_SearchRule == "")
               $this->m_SearchRule .= $searchStr;
            else
               $this->m_SearchRule .= " AND " . $searchStr;
         }
      }

      $this->SetDisplayMode (MODE_R);
      $this->GotoPage(1);
      $this->m_CursorIndex = 0;
      $this->m_ClearSearchRule = true;
      return $this->ReRender();
   }

   /**
    * BizForm::RefreshQuery() - clear the search rule and do the original query when view first loaded
    *
    * @return string - HTML text of this form's read mode
    */
   public function RefreshQuery()
   {
      if ($this->m_OnSortField) {
         $this->SetSortFieldFlag($this->m_OnSortField, null);
         $this->m_OnSortField = null;
         $this->GetDataObj()->ClearSortRule();
      }
      //$this->m_SearchRule = "";
      $this->SetDisplayMode (MODE_R);
      $this->m_CursorIndex = 0;
      $this->m_ClearSearchRule = true;
      return $this->ReRender();
   }

   /**
    * BizForm::InputValToRule() - convert the user input on a given fieldcontrol in qeury mode to search rule
    *
    * @param string $field - fieldcontrol name
    * @param string $inputVal - use input text
    * @return string - searchRule
    */
   protected function InputValToRule($field, $inputVal)
   {
      // todo: should check single quote for nonoperators clauses
      // find locations for all sql key words
      // search for starting ' and closing ' pair, check if sql key word in the pair

      $val = trim($inputVal);
      // check " AND ", " OR "
      if (($pos=strpos(strtoupper($val), " AND "))!==false) {
         $inputArr = spliti(" AND ", $val);
         $retStr = null;
         foreach($inputArr as $v)
            $retStr .= ($retStr) ? " AND ".$this->InputValToRule($field, $v) : $this->InputValToRule($field, $v);
         return $retStr;
      }
      else if (($pos=strpos(strtoupper($val), " OR "))!==false) {
         $inputArr = spliti(" OR ", $val);
         $retStr = null;
         foreach($inputArr as $v)
            $retStr .= ($retStr) ? " OR ".$this->InputValToRule($field, $v) : $this->InputValToRule($field, $v);
         return "(".$retStr.")";
      }
      // check >=, >, <=, <, =
      if (($pos=strpos($val, "<>"))!==false || ($pos=strpos($val, "!="))!==false) {
         $opr = "<>"; $oprlen = 2;
      }
      else if (($pos=strpos($val, ">="))!==false) {
         $opr = ">="; $oprlen = 2;
      }
      else if (($pos=strpos($val, ">"))!==false) {
         $opr = ">"; $oprlen = 1;
      }
      else if (($pos=strpos($val, "<="))!==false) {
         $opr = "<="; $oprlen = 2;
      }
      else if (($pos=strpos($val, "<"))!==false) {
         $opr = "<"; $oprlen = 1;
      }
      else if (($pos=strpos($val, "="))!==false) {
         $opr = "="; $oprlen = 1;
      }
      if ($opr) {
         $val = trim(substr($val, $pos+$oprlen));
      }

      if (strpos($val, "*") !== false) {
         $opr = "LIKE";
         $val = str_replace("*", "%", $val);
      }
      //if (strpos($val, "'") !== false) {   // not needed since addslashes() is called before
      //   $val = str_replace("'", "\\'", $val);
      //}
      if (!$opr)
         $opr = "=";

      // unformat value to real value data
      $bizFld = $this->GetDataObj()->GetField($field);
      global $g_BizSystem;
      $realVal = $g_BizSystem->GetTypeManager()->FormattedStringToValue($bizFld->m_Type, $bizFld->m_Format, $val);

      return "[" . $field . "] " . $opr . " '" . $realVal . "'";
   }

   // send user input to BizForm. synch up client to server Bizform's activeRecord
   public function SendUserInput()
   {
      $this->ReadInputRecord($recArr);
      $this->UpdateActiveRecord($recArr);
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

   /*
      Automatically search on the user input.
      - input is empty, delete all related controls values
      - input is no empty, query the DataObj of valuepicker BizForm.
        handle the cases of single/multiple/no records returned
   */
   public function AutoPickValue($ctrlName)
   {
      global $g_BizSystem;
      $value = $g_BizSystem->GetClientProxy()->GetFormInputs($ctrlName);
      $ctrlObj = $this->GetControl($ctrlName);

      $valuePicker = $this->GetControl($ctrlName)->m_ValuePicker;
      if (!$valuePicker) return;
      // get valuePicker form and its dataobj
      $valuePickerForm = $g_BizSystem->GetObjectFactory()->GetObject($valuePicker);
      if (!$valuePickerForm) return;
      $valuePickerDO = $valuePickerForm->GetDataObj();
      if (!$valuePickerDO) return;

      // get map of thisDOfield => joinDOfield
      $joinFields = $this->GetDataObj()->GetJoinFields($valuePickerDO);

      if (!$value) {
         // delete all related control values
         $joinFields = $this->GetDataObj()->GetJoinFields($valuePickerDO);
         $rec = $this->GetActiveRecord();
         foreach ($joinFields as $tfield=>$jfield) {
            $rec[$tfield] = "";
         }
         $this->UpdateActiveRecord($rec);
      }
      else {
         // query on valuePickerDO with current control value. searchrule as [fieldname]='value*'
         $jfield = $joinFields[$ctrlObj->m_BizFieldName];
         if (!$jfield) 
            return;
         $searchRule = "[$jfield] LIKE '$value%'";
         $recordList = array();
         $valuePickerDO->FetchRecords($searchRule, $recordList, 2);  // fetch >1 records
         // if return single record, populate the fields
         if (count($recordList) == 1) {
            $rec = $this->GetActiveRecord();
            foreach ($joinFields as $tfield=>$jfield) {
               $rec[$tfield] = $recordList[0][$jfield];
            }
            $this->UpdateActiveRecord($rec);
         }
         else {
            if (count($recordList) == 2)
               $valuePickerDO->SetSearchRule($searchRule);
            // if return > 1 records, show popup by calling clientProxy->showPopup()
            $g_BizSystem->GetClientProxy()->ShowPopup($this->m_Name, $valuePicker);
            return;
         }
      }

      return $this->ReRender();
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

   public function AutoSuggest($input)
   {
      global $g_BizSystem;
      $value = $g_BizSystem->GetClientProxy()->GetFormInputs($input);
      // get the select from list of the control
      $ctrl = $this->GetControl($input);
      $ctrl->SetValue($value);
      $fromlist = array();
      $ctrl->GetFromList($fromlist);

      echo "<ul>";
      foreach($fromlist as $item)
      {
         echo "<li>".$item['txt']."</li>";
      }
      echo "</ul>";
   }

   /**
    * BizForm::HandlePostAction() - post action is the redirected page/view after an action is finished successfully
    *
    * @param string $postAction postaction can be view:xxx, url:xxx, mode:xxx
    * @return string - redirect page or view
    */
   public function HandlePostAction($postAction)
   {
      global $g_BizSystem;
      $pos = strpos($postAction, ":");
      $tag = substr($postAction, 0, $pos);
      $content = substr($postAction, $pos+1);
      if ($tag == "view")
         $g_BizSystem->GetClientProxy()->ReDirectView($content);
      else if ($tag == "url")
         $g_BizSystem->GetClientProxy()->ReDirectPage($content);
      else if ($tag == "mode")
         {}
      else
         return;
   }

   // update form controls
   public function UpdateForm()
   {
      // set the input to form controls
      $recArr = array();
      $this->ReadInputRecord($recArr);
      $this->UpdateActiveRecord($recArr);

      // strait way of updating form - rerender
      return $this->ReRender();
   }

   /**
    * BizForm::Render() - render this form (return html content), called by bizview's render method (called when form is loaded).
    * Query is issued before returning the html content.
    *
    * @return string - HTML text of this form's read mode
    */
	public function Render()
	{
	   // when in NEW mode or when parent form in NEW mode, do nothing
	   global $g_BizSystem;
	   $prtMode = "";
	   if ($this->m_ParentFormName) {
         $prtForm = $g_BizSystem->GetObjectFactory()->GetObject($this->m_ParentFormName);
         $prtMode = $prtForm->GetDisplayMode()->GetMode();
	   }
	   if ($this->m_Mode != MODE_N && $prtMode != MODE_N)
	   {
   	   if ($this->GetDataObj()) {
            if ($this->m_HistoryInfo) {
               $this->m_HistRecordId = $this->m_HistoryInfo[0];
               $this->m_CursorIndex = -1; // cursor index is unknown
               $this->GetDataObj()->SetBookmark($this->m_HistoryInfo);
            }
   	   }
	   }
	   if ($this->m_Mode == MODE_N)
         $this->UpdateActiveRecord($this->GetDataObj()->NewRecord());

      //Moved the renderHTML function infront of declaring subforms
      $renderedHTML = $this->RenderHTML();

	   global $g_BizSystem;
	   // prepare the subforms' dataobjs, since the subform relates to parent form by dataobj association
	   if ($this->m_SubForms) {
   	   foreach($this->m_SubForms as $subForm) {
            $formObj = $g_BizSystem->GetObjectFactory()->GetObject($subForm);
            $dataObj = $this->GetDataObj()->GetRefObject($formObj->m_DataObjName);
            if ($dataObj)
               $formObj->SetDataObj($dataObj);
         }
	   }
	   $this->SetClientScripts();

      return $renderedHTML;
	}

	/**
	 * BizForm::SetClientScripts - auto add javascripts code to the page
	 */
	protected function SetClientScripts()
	{
	   global $g_BizSystem;
	   if ($this->m_jsClass != "jbForm")
         $g_BizSystem->GetClientProxy()->AppendScripts($this->m_jsClass, $this->m_jsClass.".js");

      // scan all elements
      foreach ($this->m_RecordRow as $ctrl)
      {
         $cType = strtoupper($ctrl->m_Type);
         if ($cType == "RICHTEXT")
            $g_BizSystem->GetClientProxy()->IncludeRTEScripts();
         else if ($cType == "DATETIME" || $cType == "DATE")
            $g_BizSystem->GetClientProxy()->IncludeCalendarScripts();
         else if ($cType == "AUTOSUGGEST")
            $g_BizSystem->GetClientProxy()->AppendScripts("scriptaculous", "scriptaculous.js");
      }

      $g_BizSystem->GetClientProxy()->AppendScripts("tablekit", "tablekit.js");
      //$g_BizSystem->GetClientProxy()->AppendScripts("fastinit", "fastinit.js");
	}

	/**
    * BizForm::ReRender() - rerender this form (form is rendered already) .
    *
    * @param boolean $redrawForm - whether render this form again or not
    * @param boolean $hasRecordChange - if record change, need to render subforms
    * @return string - HTML text of this form's read mode
    */
	public function ReRender($redrawForm=true, $hasRecordChange=true)
	{
	   // consider the postAction
	   $postAction = $this->GetPostAction();
      if ($postAction) {
         $this->HandlePostAction($postAction);
         return;
      }

      global $g_BizSystem;
      if ($redrawForm)
      {
         if ($g_BizSystem->GetClientProxy()->HasFormRerendered($this->m_Name) == false)
	         $g_BizSystem->GetClientProxy()->ReDrawForm($this->m_Name, $this->RenderHTML());
      }
	   if ($hasRecordChange)
	   {
	      $this->ReRenderSubForms();
	   }
	   return;
	}

	/**
    * BizForm::ReRenderSubForms() - rerender sub forms who has dependecy on this form.
    * This method is called when parent form's change affect the sub forms
    *
    * @return string - HTML text of this form's read mode
    */
	protected function ReRenderSubForms()
   {
      if (!$this->m_SubForms)
         return;

      $this->SetCursorIndex($this->m_CursorIndex);
      $this->m_ActiveRecord = $this->GetActiveRecord();

      global $g_BizSystem;
      $mode = $this->GetDisplayMode()->GetMode();
      foreach($this->m_SubForms as $subForm) {
         $formObj = $g_BizSystem->GetObjectFactory()->GetObject($subForm);
         $formObj->SetPostActionOff();
         if ($mode == MODE_N) {  // parent form on new mode
            $formObj->SetPrtCommitPending(true);
         }
         else {
            $formObj->SetPrtCommitPending(false);
            $dataObj = $this->GetDataObj()->GetRefObject($formObj->m_DataObjName);
            if ($dataObj)
               $formObj->SetDataObj($dataObj);
         }
         $formObj->m_CursorIndex = 0;
         $formObj->ReRender();
      }
      return;
   }

   /**
    * BizForm::RenderHTML() - render html content of this form
    *
    * @return string - HTML text of this form's read mode
    */
	protected function RenderHTML()
	{
	   $dispmode = $this->GetDisplayMode();
	   $this->SetDisplayMode($dispmode->GetMode());

      $smarty = BizSystem::GetSmartyTemplate();
      $smarty->assign_by_ref("name", $this->m_Name);
      $smarty->assign_by_ref("title", $this->m_Title);
      $smarty->assign_by_ref("formstate", $this->m_FormState);
      $smarty->assign_by_ref("toolbar", $this->m_ToolBar->Render());

      if ($dispmode->m_DataFormat == "array") // if dataFormat is array, call array render function
         $smarty->assign_by_ref("fields", $this->RenderArray());
      else if ($dispmode->m_DataFormat == "table") // if dataFormat is table, call table render function.
      {
         $smarty->assign_by_ref("table", $this->RenderTable());
         $smarty->assign_by_ref("formobj", $this);
      }
      else if ($dispmode->m_DataFormat == "block" && $dispmode->m_FormatStyle)
         $smarty->assign_by_ref("block", $this->RenderFormattedTable());

      $smarty->assign_by_ref("navbar", $this->m_NavBar->Render());

      //$smarty->assign_by_ref("contextmenu", $this->RenderContextMenu());

	   return $smarty->fetch($dispmode->m_TemplateFile)
	          . "\n" . $this->RenderShortcutKeys()
	          . "\n" . $this->RenderContextMenu();
	}

   /**
    * BizForm::RenderArray() - Render form as array format using array template
    * @return string 1d array
    */
   protected function RenderArray()
   {
      //$this->SetCursorIndex(0);
      if ($this->m_QueryONRender && !$this->m_ActiveRecord && $this->m_DataObjName) {
         if (!$this->_run_search($resultRecords, $this->m_ClearSearchRule))
            return $this->ProcessDataObjError($ok);
         $this->UpdateActiveRecord($resultRecords[0]);
         $this->m_CursorIDMap[0] = $resultRecords[0]["Id"];
      }

      $columns = $this->m_RecordRow->RenderColumn();
      foreach($columns as $key=>$val) {
         $fields[$key]["label"] = $val;
         $fields[$key]["required"] = $this->GetControl($key)->m_Required;
         $fields[$key]["description"] = $this->GetControl($key)->m_Description;
         $fields[$key]["value"] = $this->GetControl($key)->m_Value;	 
      }

      $controls = $this->m_RecordRow->Render();
      if ($this->CanShowData()) {
         foreach($controls as $key=>$val) {
            $fields[$key]["control"] = $val;
         }
      }
      return $fields;
   }

   /**
    * BizForm::RenderTable() - Render form as table format using table template
    * @return string 2d array
    */
   protected function RenderTable()
   {
      if ($this->m_QueryONRender)
         if (!$this->_run_search($resultRecords, $this->m_ClearSearchRule))
            return $this->ProcessDataObjError($ok);
      //print_r($resultRecords);
      //exit;
      $records = array();
      $records[] = $this->m_RecordRow->RenderColumn();
      $counter = 0;
      while ($counter < $this->m_Range) {
         if ($this->CanShowData())
            $arr = $resultRecords[$counter];
         if (!$arr)
            break;
         $this->m_CursorIDMap[$counter] = $arr["Id"];
         $this->m_RecordRow->SetRecordArr($arr);
         $tblRow = $this->m_RecordRow->Render();
         $records[] = $tblRow;
         $counter++;
      }
      $this->SetCursorIndex($this->m_CursorIndex);
      return $records;
   }

   /**
    * BizForm::RenderFormattedTable() - Render form as table format using table format style
    * Example as template->m_FormatStyle:table_style give the top style of the table.
    * head,rowodd,roweven,rowsel,cell in css file will be used in the table elements
    *
    * @return string HTML text
    */
   protected function RenderFormattedTable()
   {
      if ($this->m_QueryONRender)
         if (!$this->_run_search($resultRecords, $this->m_ClearSearchRule))
            return $this->ProcessDataObjError($ok);

      $dispmode = $this->GetDisplayMode();
      $hasSub = $this->m_SubForms ? 0 : 1;
      //$this->SetDisplayMode($dispmode->GetMode());
      $cls_tbl = strlen($dispmode->m_FormatStyle[0])>0 ? "class='".$dispmode->m_FormatStyle[0]."'" : "";
      $sHTML_tbl = "<table width=100% border=0 cellspacing=0 cellpadding=3 $cls_tbl>\n";
      //$sHTML_tby = "<tbody id='".$this->m_Name."_tbody' Highlighted='".$this->m_Name."_data_".($this->m_CursorIndex+1)."' SelectedRow='".($this->m_CursorIndex+1)."'>\n";

      // print column header
      $columns = $this->m_RecordRow->RenderColumn();
      $sHTML = "";
      foreach($columns as $colname)
         $sHTML .= "<th class=head>$colname</th>\n";

      // print column data table
      $name = $this->m_Name;
      $counter = 0;
      $this->m_CursorIDMap = array();
      while ($counter < $this->m_Range) {
         if ($this->CanShowData())
            $arr = $resultRecords[$counter];
         else
            $arr = null;
         if (!$arr && $this->m_FullPage == "N")
            break;
         if (!$arr)
            $sHTML .= "<tr><td colspan=99>&nbsp;</td></tr>\n";
         else {
            $this->m_CursorIDMap[$counter] = $arr["Id"];

            $this->m_RecordRow->SetRecordArr($arr);
            $tblRow = $this->m_RecordRow->Render();
            $rowHTML = "";
            foreach($tblRow as $cell)
               $rowHTML .= "<td valign=top class=cell>$cell</td>\n";
            $rownum = $counter+1;
            $rowid = $name."_data_".$rownum;
            $attr = $rownum % 2 == 0 ? "normal=roweven select=rowsel" : "normal=rowodd select=rowsel";

            if ($this->m_HistRecordId != null && $this->m_HistRecordId == $arr["Id"]) {
               $this->m_CursorIndex = $counter;
               $style_class = "class=rowsel";
            }
            else if ($counter == $this->m_CursorIndex)
               $style_class = "class=rowsel";
            else if ($rownum % 2 == 0)
               $style_class = "class=roweven";
            else
               $style_class = "class=rowodd";

            //$onclick = "onclick=\"CallFunction('$name.SelectRecord($rownum,$hasSub)');\"";
            $onclick = "ondblclick=\"CallFunction('$name.EditRecord()');\" onclick=\"CallFunction('$name.SelectRecord($rownum,$hasSub)');\"";

            $sHTML .= "<tr id='$rowid' $style_class $attr $onclick>\n$rowHTML</tr>\n";
         }
         $counter++;
      } // while
      // move daraobj's cursor to the UI current record
      $this->SetCursorIndex($this->m_CursorIndex);

      $sHTML_tby = "<tbody id='".$this->m_Name."_tbody' Highlighted='".$this->m_Name."_data_".($this->m_CursorIndex+1)."' SelectedRow='".($this->m_CursorIndex+1)."'>\n";

      $sHTML = $sHTML_tbl . $sHTML_tby . $sHTML . "</tbody></table>";

      // restore the RecordRow data because it gets changed during record navigation
      ///$this->m_RecordRow->SetRecordArr($this->m_ActiveRecord);

      return $sHTML;
   }

   protected function RenderShortcutKeys()
   {
      $keymap = array();
      // scan toolbar navbar elements. if its eventhandler has shortcutkey attribute, print [key => function]
      foreach ($this->m_ToolBar as $ctrl) {
         $map = $ctrl->GetSCKeyFuncMap();
         if (count($map)>0) $keymap = array_merge($keymap, $map);
      }
      foreach ($this->m_NavBar as $ctrl) {
         $map = $ctrl->GetSCKeyFuncMap();
         if (count($map)>0) $keymap = array_merge($keymap, $map);
      }
      $str = "<div id='".$this->m_Name."_accelkeys' style='display:none'>";
      foreach ($keymap as $key=>$func)
         $str .= "[$key:$func]";
      $str .= "</div>";
      return $str;
   }

   protected function RenderContextMenu()
   {
      $menulist = array();
      // scan toolbar navbar elements. if its eventhandler has shortcutkey attribute, print [key => function]
      foreach ($this->m_ToolBar as $ctrl) {
         $menus = $ctrl->GetContextMenu();
         foreach ($menus as $m)
            $menulist[] = $m;
      }
      foreach ($this->m_NavBar as $ctrl) {
         $menus = $ctrl->GetContextMenu();
         foreach ($menus as $m)
            $menulist[] = $m;
      }
      if (count($menulist) == 0)
         return "";
      $str = "<ul class='contextMenu' id='".$this->m_Name."_contextmenu'>";
      foreach ($menulist as $m) {
         $func  = $m['func'];
         $str .= "<li><a href=\"javascript:$func\">".$m['text']."  (".$m['key'].")</a></li>";
      }
      $str .= "</ul>";

      return $str;
   }
}

/**
 * RecordRow class - RecordRow is the class that contains FieldControls
 *
 * @package BizView
 */
class RecordRow extends MetaIterator implements iUIControl
{
   protected $m_SortedControlKeys;

   public function SetMode($mode, $dataFormat)
   {
      foreach ($this->m_var as $ctrl)
         $ctrl->SetMode($mode, $dataFormat);
   }

   /**
    * RecordRow::GetSortControlKeys() - Get sorted contorl keys, the sort order is defined in metadata file
    *
    * @return array - sorted key array
    */
   public function GetSortControlKeys()
   {
      if ($this->m_SortedControlKeys)
         return $this->m_SortedControlKeys;
      foreach($this->m_var as $key=>$ctrl)
      {
         if ($ctrl->m_Order)
            $keyOrder[$key] = $ctrl->m_Order;
         else
            $keyNoOrder[] = $key;
      }
      if($keyOrder) {
         asort($keyOrder);
         if ($keyNoOrder)
            $this->m_SortedControlKeys = array_merge($keyNoOrder, array_keys($keyOrder));
         else
            $this->m_SortedControlKeys = array_keys($keyOrder);
      }
      else
         $this->m_SortedControlKeys = $keyNoOrder;
      return $this->m_SortedControlKeys;
   }
   /**
    * RecordRow::SetRecordArr() - assign the record array to RecordRow object. It is usually called before calling its render method.
    *
    * @param array - record array
    * @return void
    */
   public function SetRecordArr(&$recArr)
   {
      foreach ($this->m_var as $fldCtrl) {
         if (!$recArr)
            $fldCtrl->SetValue("");
         else if (key_exists($fldCtrl->m_BizFieldName,$recArr))
            $fldCtrl->SetValue($recArr[$fldCtrl->m_BizFieldName]);
      }
   }
   public function GetDefaultRecordArr()
   {
      foreach ($this->m_var as $fldCtrl) {
         $recArr[$fldCtrl->m_BizFieldName] = $fldCtrl->GetDefaultValue();
      }
      return $recArr;
   }
   public function GetControlByField($fieldName)
   {
      foreach ($this->m_var as $fldCtrl) {
         if ($fldCtrl->m_BizFieldName == $fieldName)
            return $fldCtrl;
      }
      return null;
   }
   /**
    * RecordRow::Render() - Render the record row with thml text. It is usually called after calling its SetRecordArr method.
    *
    * @return string - html text
    */
   public function Render()
   {
      $values = array();
      $keylist = $this->GetSortControlKeys();
      foreach ($keylist as $key) {
         $fldCtrl = $this->m_var[$key];
         if (!$fldCtrl->CanDisplayed())
            continue;
         $values[$key] = $fldCtrl->Render();
      }
      return $values;
   }
   /**
    * RecordRow::RenderColumn() - Render the current record display name (header of a html table)
    *
    * @return array - display name of all fieldcontrols
    */
   public function RenderColumn()
   {
      $values = array();
      foreach ($this->GetSortControlKeys() as $key) {
         $fldCtrl = $this->m_var[$key];
         if (!$fldCtrl->CanDisplayed())
            continue;
         $colname = $fldCtrl->RenderHeader();
         if ($colname)
            $values[$key] = $colname;
      }
      return $values;
   }
}

/**
 * ToolBar class - ToolBar is the class that contains HTMLControls
 *
 * @package BizView
 */
class ToolBar extends MetaIterator implements iUIControl
{
   public function SetMode($mode, $dataFormat)
   {
      foreach ($this->m_var as $ctrl)
         $ctrl->SetMode($mode, $dataFormat);
   }

   /**
    * ToolBar::Render() - Render the ToolBar with thml text.
    *
    * @return string - html text
    */
   public function Render()
   {
      $mode = $this->m_prtObj->GetDisplayMode();
      $tbar = array();
      foreach($this->m_var as $ctrl) {
         $ctrl->SetState("ENABLED");
         // todo: readonly access
         if ($ctrl->CanDisplayed())
            $tbar[$ctrl->m_Name] = $ctrl->Render();
      }
      return $tbar;
   }
}

/**
 * NavBar class - NavBar is the class that contains navigation buttons
 *
 * @package BizView
 */
class NavBar extends MetaIterator implements iUIControl
{
   public function SetMode($mode, $dataFormat)
   {
      foreach ($this->m_var as $ctrl)
         $ctrl->SetMode($mode, $dataFormat);
   }

   /**
    * NavBar::Render() - Render the ToolBar with thml text.
    *
    * @return string - html text
    */
   public function Render()
   {
      if (!$this->m_prtObj->GetDataObj()) return "";
      $nbar = array();
      $curPage = $this->m_prtObj->GetDataObj()->GetCurrentPageNumber();
      $totalPage = $this->m_prtObj->GetDataObj()->GetTotalPageCount();
      foreach($this->m_var as $ctrl) {
         if (!$ctrl->CanDisplayed()) continue;
         if (($curPage == 1) && (strpos($ctrl->m_Function, "PrevPage") > 0))
            $ctrl->SetState("DISABLED");
         else if (($curPage == $totalPage) && (strpos($ctrl->m_Function, "NextPage") > 0))
            $ctrl->SetState("DISABLED");
         else
            $ctrl->SetState("ENABLED");

         $nbar[$ctrl->m_Name] = $ctrl->Render();
      }
      // append curPage and totalPage
      $nbar["curPage"] = $totalPage==0 ? 0 : $curPage;
      $nbar["totalPage"] = $totalPage;
      return $nbar;
   }
}

/**
 * DisplayMode class - contains the BizForm display mode information
 *
 * @package BizView
 */
class DisplayMode
{
   public $m_Name;
   public $m_DataFormat;
   public $m_FormatStyle = null;
   public $m_InitMode;
   public $m_TemplateFile;

   function __construct(&$xmlArr)
   {
      $this->m_Name = isset($xmlArr["ATTRIBUTES"]["NAME"]) ? $xmlArr["ATTRIBUTES"]["NAME"] : null;
      $this->m_DataFormat = isset($xmlArr["ATTRIBUTES"]["DATAFORMAT"]) ? $xmlArr["ATTRIBUTES"]["DATAFORMAT"] : null;
      $this->m_TemplateFile = isset($xmlArr["ATTRIBUTES"]["TEMPLATEFILE"]) ? $xmlArr["ATTRIBUTES"]["TEMPLATEFILE"] : null;
      $this->m_InitMode = isset($xmlArr["ATTRIBUTES"]["INITMODE"]) ? $xmlArr["ATTRIBUTES"]["INITMODE"] : null;
      if (isset($xmlArr["ATTRIBUTES"]["FORMATSTYLE"])) {
         $this->m_FormatStyle = array();
         $this->m_FormatStyle = split(",",$xmlArr["ATTRIBUTES"]["FORMATSTYLE"]);
      }
   }

   public function GetMode()
   {
      switch ($this->m_Name) {
         case "READ": $mode = MODE_R; break;
         case "EDIT": $mode = MODE_E; break;
         case "NEW": $mode = MODE_N; break;
         case "QUERY": $mode = MODE_Q; break;
         default: $mode = $this->m_Name;
      }
      return $mode;
   }
}

?>
