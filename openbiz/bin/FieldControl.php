<?PHP
include_once("HTMLControl.php");

/**
 * FieldControl - class FieldControl is the base class of field control who binds with a bizfield
 *
 * @package BizView
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @version 1.2
 * @access public
 */
class FieldControl extends HTMLControl
{
   public $m_BizFieldName;
   public $m_DisplayName;
   public $m_BizFormName;
   public $m_ValuePicker = null;
   public $m_PickerMap = null;
   public $m_DrillDownLink = null;
   //public $m_Hidden = "N";
   public $m_Enabled = "Y";
   public $m_Sortable = "N";
   public $m_DataType = null;
   public $m_DataFormat = null;
   public $m_SortFlag = null;
   public $m_Order;
   public $m_DefaultValue = "";
   public $m_Description;

   function __construct(&$xmlArr, $formObj)
   {
      parent::__construct($xmlArr, $formObj);
      $this->m_BizFormName = $formObj->m_Name;
      $this->m_BizFieldName = isset($xmlArr["ATTRIBUTES"]["FIELDNAME"]) ? $xmlArr["ATTRIBUTES"]["FIELDNAME"] : null;
      $this->m_DisplayName = isset($xmlArr["ATTRIBUTES"]["DISPLAYNAME"]) ? I18n::getInstance()->translate($xmlArr["ATTRIBUTES"]["DISPLAYNAME"])  : null;
      $this->m_Description = isset($xmlArr["ATTRIBUTES"]["DESCRIPTION"]) ? $xmlArr["ATTRIBUTES"]["DESCRIPTION"] : null;
      $this->m_ValuePicker  = isset($xmlArr["ATTRIBUTES"]["VALUEPICKER"]) ? $xmlArr["ATTRIBUTES"]["VALUEPICKER"] : null;
      $this->m_PickerMap  = isset($xmlArr["ATTRIBUTES"]["PICKERMAP"]) ? $xmlArr["ATTRIBUTES"]["PICKERMAP"] : null;
      if (isset($xmlArr["ATTRIBUTES"]["DRILLDOWNLINK"]))
         $this->SetDrillDownLink ($xmlArr["ATTRIBUTES"]["DRILLDOWNLINK"]);
      $this->m_Enabled = isset($xmlArr["ATTRIBUTES"]["ENABLED"]) ? $xmlArr["ATTRIBUTES"]["ENABLED"] : null;
      $this->m_Sortable = isset($xmlArr["ATTRIBUTES"]["SORTABLE"]) ? $xmlArr["ATTRIBUTES"]["SORTABLE"] : null;
      $this->m_DataType = isset($xmlArr["ATTRIBUTES"]["DATATYPE"]) ? $xmlArr["ATTRIBUTES"]["DATATYPE"] : null;
      $this->m_Order = isset($xmlArr["ATTRIBUTES"]["ORDER"]) ? $xmlArr["ATTRIBUTES"]["ORDER"] : null;
      $this->m_DefaultValue = isset($xmlArr["ATTRIBUTES"]["DEFAULTVALUE"]) ? $xmlArr["ATTRIBUTES"]["DEFAULTVALUE"] : null;
      $this->m_Mode = MODE_R;

      // if no class name, add default class name. i.e. NewRecord => ObjName.NewRecord
      $this->m_ValuePicker = $this->PrefixPackage($this->m_ValuePicker);

      if (!$this->m_BizFieldName)
         $this->m_BizFieldName = $this->m_Name;
   }

   public function GetDefaultValue()
   {
      if ($this->m_DefaultValue == "")
         return "";
      $formobj = $this->GetFormObj();
      $defValue = Expression::EvaluateExpression($this->m_DefaultValue, $formobj);
      return $defValue;
   }

   private function SetDrillDownLink($ddLinkString)
   {
      // linkTo string with format:otherView,otherForm.ctrl=my_ctrl
      if (strlen($ddLinkString) < 1)
         return;
      $pos = strpos($ddLinkString, "=");
      $this->m_DrillDownLink["my_ctrl"] = substr($ddLinkString, $pos + 1, strlen($ddLinkString) - $pos);
      $other = substr($ddLinkString, 0, $pos);
      $pos = strpos($other, ",");
      $pos1 = strrpos($other, ".", $pos + 1);
      $linkView = substr($other, 0, $pos);
      $this->m_DrillDownLink["link_view"] = $this->PrefixPackage($linkView);
      $linkForm = substr($other, $pos + 1, $pos1 - $pos-1);
      $this->m_DrillDownLink["link_form"] = $this->PrefixPackage($linkForm);
      $this->m_DrillDownLink["link_ctrl"] = substr($other, $pos1 + 1, strlen($other) - $pos1);
   }

   /**
    * FieldControl::SetSortFlag() - set the sort flag of the control
    *
    * @param integer $flag 1 or 0
    * @return void
    */
   public function SetSortFlag($flag=null)
   {
     $this->m_SortFlag = $flag;
   }

   /**
    * FieldControl::RenderHeader() -  When render table, it return the table header; when render array, it return the display name
    *
    * @return string HTML text
    */
   public function RenderHeader()
   {
      if ($this->m_Hidden == "Y")
         return null;
      if ($this->m_DataFormat != "array" && $this->m_Sortable == "Y") {
         //$rule = "[" . $this->m_BizFieldName . "]";
         $rule = $this->m_Name;
         $function = $this->m_BizFormName . ".SortRecord(" . $rule . ")";
         $val = "<a href=javascript:CallFunction('" . $function . "')>" . $this->m_DisplayName . "</a>";
         if ($this->m_SortFlag == "ASC")
            $val .= "<img src=\"../images/up_arrow.gif\">";
         else if ($this->m_SortFlag == "DESC")
            $val .= "<img src=\"../images/down_arrow.gif\">";
      } else {
         $val = $this->m_DisplayName;
      }
      return $val;
   }

   /**
    * FieldControl::Render() - Draw the control according to the mode
    *
    * @returns stirng HTML text
    */
   public function Render()
   {
      $val = $this->m_Value;
      $temp = ($this->m_FunctionType==null) ? "" : ",'".$this->m_FunctionType."'";
      if ($this->m_Image)
         $val = "<img src=\"../images/".$this->m_Image."\" border=0> $val";

      // todo: don't use deperated m_Function and m_FunctionType
      if ($val!==null && $this->m_Function) {
         $funcExpr = Expression::EvaluateExpression($this->m_Function, $this->GetFormObj());
         $val = "<a href=\"javascript:CallFunction('" . $funcExpr . "'$temp)\">$val</a>";
      }

      //if ($this->m_Mode != MODE_E && $this->m_Mode != MODE_N && $this->m_Mode != MODE_Q)
      //   $tmpMode = 'READ';
      if($this->m_Mode == null) {
         $tmpMode = 'READ';
      } else {
         $tmpMode = $this->m_Mode;
      }

      if (($val===null || $val==="") && $tmpMode == MODE_R) {
         $val = "&nbsp;";
      }

      if ($tmpMode == MODE_R && $this->m_Link) {
         $link = $this->GetLink();
         $val = "<a href=\"$link\">" . $val . "</a>";
      }

      if ($tmpMode == MODE_R && $this->m_DrillDownLink) {
         $otherCtrl = $this->GetFormObj()->GetControl($this->m_DrillDownLink["my_ctrl"]);
         $this->m_DrillDownLink["my_ctrl_val"] = $otherCtrl->GetValue();
         $rule = $this->m_DrillDownLink["link_form"] . "." . $this->m_DrillDownLink["link_ctrl"] . "=\'" . $this->m_DrillDownLink["my_ctrl_val"] . "\'";
         $val = "<a href=javascript:DrillDownToView('" . $this->m_DrillDownLink["link_view"] . "','$rule')>" . $val . "</a>";
      }

      if ($tmpMode != MODE_R)
      {
         $ctrlName = $this->m_Name;
         $cType = strtoupper($this->m_Type);
         if ($cType == "DATE")   $val = $this->RenderDate();
         else if ($cType == "DATETIME")   $val = $this->RenderDatetime();
         else {
            $val = parent::Render();
            if ($this->m_ValuePicker != null) {
               $function = $this->m_BizFormName . ".ShowPopup(" . $this->m_ValuePicker . "," . $ctrlName . ")";
               $val .= " <input type=button onClick=\"CallFunction('$function','Popup');\" value=\"...\" style='width:20px;'>";
            }
         }
      }

      return $val;
   }

   protected function RenderDate()
   {
      // get the raw date value by unformatting it or geting the raw data from dataobj
      $format = $this->GetFormObj()->GetDataObj()->GetField($this->m_BizFieldName)->m_Format;
		$val = parent::Render();
		$showTime = 'false';
      $img = "<img src=\"../images/calendar.gif\" border=0 title=\"Select date...\" align='absoultemiddle' hspace='2'>";
      $val .= "<a href=\"javascript: void(0);\" onclick=\"return showCalendar('".$this->m_Name."', '".$format."', ".$showTime.", true); return false;\"  onmousemove='window.status=\"Select a date\"' onmouseout='window.status=\"\"'>" . $img . "</a>";
      return $val;
   }

   protected function RenderDatetime()
   {
      // get the raw date value by unformatting it or geting the raw data from dataobj
      $format = $this->GetFormObj()->GetDataObj()->GetField($this->m_BizFieldName)->m_Format;
		$val = parent::Render();
		$showTime = "'24'";
      $img = "<img src=\"../images/calendar.gif\" border=0 title=\"Select date...\" align='absoultemiddle' hspace='2'>";
      $val .= "<a href=\"javascript: void(0);\" onclick=\"return showCalendar('".$this->m_Name."', '".$format."', ".$showTime.", true); return false;\"  onmousemove='window.status=\"Select a datetime\"' onmouseout='window.status=\"\"'>" . $img . "</a>";
      return $val;
   }

}
/*
class TreeNodeCtrl extends FieldControl
{
   public function Render()
   {
      // get the folder image and leave image
      $origImg = $this->m_Image;
      list($img1, $img2) = split(";", $this->m_Image);
      $img1 = trim($img1); $img2 = trim($img2);
      // get expand function
      $origFunc = $this->m_Function;
      list($f1, $f2) = split(";", $this->m_Function);
      $f1 = trim($f1); $f2 = trim($f2);

      // if the record has child
      $formObj = $this->GetFormObj();
      $chldFlagCtrl = $formObj->GetControl("TreeNodeChldFlag");
      //$recArr = $formObj->GetDataObj()->GetRecord(0);
      //$childFlag = $recArr[$chldFlagCtrl->m_BizFieldName];
      if ($chldFlagCtrl->GetValue() == $chldFlagCtrl->m_SelectFrom) {
         $this->m_Image = $img1;
         $this->m_Function = $f1;
      }
      else {
         $this->m_Image = $img2;
         $this->m_Function = $f2;
      }

      $rdr = parent::Render();
      // set the original attributes back
      $this->m_Function = $origFunc;
      $this->m_Image = $origImg;
      return $rdr;
   }
}
*/
?>
