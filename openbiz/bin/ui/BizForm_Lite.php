<?PHP

/**
 * BizForm class - BizForm is the base class that contains UI controls.
 * BizForm is a html form that is included in a BizView which is a html page.
 *
 * @package BizView
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */

include_once(OPENBIZ_BIN."/ui/BizForm_Abstract.php");

class BizForm_lite extends BizForm_Abstract
{
   protected $m_CurrentPage = 1; 
   protected $m_TotalPages = 0; 
   protected $m_TotalRecords = 0; 

   protected $m_OnSortField;
   protected $m_OnSortFlag; 

   protected $m_ClearSearchRule = true;
	
	public function GetCurrentPageNumber() { return $this->m_CurrentPage; }
   public function GetTotalPageCount() { return $this->m_TotalPages; }
   public function GetTotalRecords() { return $this->m_TotalRecords; }
	
	public function GotoPage($page=1)
   {
      global $g_BizSystem;
      $this->m_CurrentPage = $g_BizSystem->GetClientProxy()->GetFormInputs('_currentPage');
      $this->m_TotalPages = $g_BizSystem->GetClientProxy()->GetFormInputs('_totalPage');
      $tgtPage = $page;
      if ($tgtPage == 0) $tgtPage = 1;
      else if ($tgtPage < 0) $tgtPage = $this->m_TotalPages;
      else if ($tgtPage > $this->m_TotalPages) $tgtPage = $this->m_TotalPages;
      if ($tgtPage == $this->m_CurrentPage)
         return;
      $this->m_CurrentPage = $tgtPage;
      return $this->ReRender();
   }

   /**
    * BizForm::NextPage() - move to next page
    *
    * @return string - html content of next page
    */
   public function NextPage()
   {
      global $g_BizSystem;
      $this->m_CurrentPage = $g_BizSystem->GetClientProxy()->GetFormInputs('_currentPage');
      $this->m_TotalPages = $g_BizSystem->GetClientProxy()->GetFormInputs('_totalPage');
      if ($this->m_CurrentPage >= $this->m_TotalPages)
         $this->m_CurrentPage = $this->m_TotalPages;
      else
         $this->m_CurrentPage++;
      return $this->ReRender();
   }
   /**
    * BizForm::PrevPage() - move to previous page
    *
    * @return string - html content of previous page
    */
   public function PrevPage()
   {
      global $g_BizSystem;
      $this->m_CurrentPage = $g_BizSystem->GetClientProxy()->GetFormInputs('_currentPage');
      $this->m_TotalPages = $g_BizSystem->GetClientProxy()->GetFormInputs('_totalPage');
      if ($this->m_CurrentPage <= 1)
         $this->m_CurrentPage = 1;
      else 
         $this->m_CurrentPage--;
      return $this->ReRender();
   }
   
   /**
    * BizForm::RunSearch() - Run search on query mode, then go read mode
    *
    * @return string - HTML text of this form's read mode
    */
   public function RunSearch($targetForm=null)
   {
      BizSystem::log(LOG_DEBUG,"FORMOBJ",$this->m_Name."::RunSearch(), SearchRule=".$this->m_SearchRule);
      
      global $g_BizSystem;
      
      $searchRule = "";
      foreach ($this->m_RecordRow as $fldCtrl) {
         $value = $g_BizSystem->GetClientProxy()->GetFormInputs($fldCtrl->m_Name);
         if ($value) {
            $searchStr = $this->InputValToRule($fldCtrl->m_BizFieldName, $value);
            if ($searchRule == "")
               $searchRule .= $searchStr;
            else
               $searchRule .= " AND " . $searchStr;
         }
      }
      
      if ($targetForm)
      {
         $tgtForm = BizSystem::ObjectFactory()->GetObject($targetForm);
         if ($tgtForm)
         {
            $tgtForm->SetSearchRule($searchRule);
            return $tgtForm->ReRender();
         }
         return;
      }
      
      $this->m_SearchRule = $searchRule;

      //$this->SetDisplayMode (MODE_R);
      $this->GotoPage(1);
      $this->m_ClearSearchRule = true;
      return $this->ReRender();
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
      $dataobj->setLimit($this->m_Range, ($this->m_CurrentPage-1)*$this->m_Range);
      $resultRecords = $dataobj->Fetch();
      $this->m_TotalRecords = $dataobj->Count();
      if ($this->m_Range && $this->m_Range > 0)
         $this->m_TotalPages = ceil($this->m_TotalRecords/$this->m_Range);
      return true;
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
      
      // move to 1st page
      $this->m_CurrentPage = 1;

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
   
   /**
    * BizForm::RefreshQuery() - clear the search rule and do the original query when view first loaded
    *
    * @return string - HTML text of this form's read mode
    */
   public function RefreshQuery()
   {
      /*if ($this->m_OnSortField) {
         $this->SetSortFieldFlag($this->m_OnSortField, null);
         $this->m_OnSortField = null;
         $this->GetDataObj()->ClearSortRule();
      }*/
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

   /**
    * BizForm::Render() - render this form (return html content), called by bizview's render method (called when form is loaded).
    * Query is issued before returning the html content.
    *
    * @return string - HTML text of this form's read mode
    */
	public function Render()
	{

      //Moved the renderHTML function infront of declaring subforms
      $renderedHTML = $this->RenderHTML();

      return $renderedHTML;
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
	   global $g_BizSystem; 
	   $g_BizSystem->GetClientProxy()->ReDrawForm($this->m_Name, $this->RenderHTML());
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
      
      //print_r ($this->m_SearchBox->Render());

      if ($dispmode->m_DataFormat == "array") // if dataFormat is array, call array render function
         $smarty->assign_by_ref("fields", $this->RenderArray());
      else if ($dispmode->m_DataFormat == "table") // if dataFormat is table, call table render function.
      {
         $smarty->assign_by_ref("table", $this->RenderTable());
         $smarty->assign_by_ref("formobj", $this);
      }

      $smarty->assign_by_ref("navbar", $this->m_NavBar->Render());

	   return $smarty->fetch(BizSystem::GetTplFileWithPath($dispmode->m_TemplateFile, $this->m_Package));
	}

   /**
    * BizForm::RenderArray() - Render form as array format using array template
    * @return string 1d array
    */
   protected function RenderArray()
   {
      if ($this->m_Mode != MODE_Q && $this->m_DataObjName) {
         $this->m_Range = 1;
         if (!$this->_run_search($resultRecords, $this->m_ClearSearchRule))
            return $this->ProcessDataObjError($ok);
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
      if (!$this->_run_search($resultRecords, $this->m_ClearSearchRule))
         return $this->ProcessDataObjError($ok);

      $records = array();
      $records[] = $this->m_RecordRow->RenderColumn();
      $counter = 0;
      while (true) {
         if ($this->m_Range != null && $this->m_Range > 0 && $counter > $this->m_Range)
            break;
         if ($this->CanShowData())
            $arr = $resultRecords[$counter];
         if (!$arr)
            break;
         $this->m_RecordRow->SetRecordArr($arr);
         $tblRow = $this->m_RecordRow->Render();
         $records[] = $tblRow;
         $counter++;
      }
      return $records;
   }

}

?>
