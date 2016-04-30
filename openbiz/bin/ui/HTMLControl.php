<?PHP
/**
 * HTMLControl - class HTMLControl is the base class of HTML controls
 *
 * @package BizView
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @version 1.2
 * @access public
 */
class HTMLControl extends MetaObject implements iUIControl
{
   public $m_Caption;
   public $m_Image;
   public $m_Style;
   public $m_Type;
   public $m_cssClass;
   public $m_Function;
   public $m_FunctionType;
   public $m_DisplayMode;
   public $m_State = "ENABLED";
   public $m_Width = null;
   public $m_Height = null;
   public $m_Required = "N";
   public $m_Enabled = "Y";      // support expression
   public $m_HTMLAttr = "";      // support expression ?
   public $m_SelectFrom = null;  // support expression
   public $m_Hidden = "N";       // support expression
   public $m_Link = null;        // support expression
   public $m_EventHandlers = null;

   public $m_Value = "";
   public $m_BizFormName;

   protected $m_Mode;
   protected $m_DataFormat = "";

   function __construct(&$xmlArr, $formObj)
   {
      $this->m_BizFormName = $formObj->m_Name;
      $this->m_Name = isset($xmlArr["ATTRIBUTES"]["NAME"]) ? $xmlArr["ATTRIBUTES"]["NAME"] : null;
      $this->m_Package = $formObj->m_Package;
      $this->m_Caption = isset($xmlArr["ATTRIBUTES"]["CAPTION"]) ? I18n::getInstance()->translate($xmlArr["ATTRIBUTES"]["CAPTION"]) : null;
      $this->m_Image = isset($xmlArr["ATTRIBUTES"]["IMAGE"]) ? $xmlArr["ATTRIBUTES"]["IMAGE"] : null;
      $this->m_Type = isset($xmlArr["ATTRIBUTES"]["TYPE"]) ? $xmlArr["ATTRIBUTES"]["TYPE"] : null;
      $this->m_cssClass = isset($xmlArr["ATTRIBUTES"]["CSSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSCLASS"] : null;
      $this->m_Style = isset($xmlArr["ATTRIBUTES"]["STYLE"]) ? $xmlArr["ATTRIBUTES"]["STYLE"] : null;
      $this->m_FunctionType = isset($xmlArr["ATTRIBUTES"]["FUNCTIONTYPE"]) ? $xmlArr["ATTRIBUTES"]["FUNCTIONTYPE"] : null;
      $this->m_Function = isset($xmlArr["ATTRIBUTES"]["FUNCTION"]) ? $xmlArr["ATTRIBUTES"]["FUNCTION"] : null;
      $this->m_Width = isset($xmlArr["ATTRIBUTES"]["WIDTH"]) ? $xmlArr["ATTRIBUTES"]["WIDTH"] : null;
      $this->m_Height = isset($xmlArr["ATTRIBUTES"]["HEIGHT"]) ? $xmlArr["ATTRIBUTES"]["HEIGHT"] : null;
      $this->m_Required = isset($xmlArr["ATTRIBUTES"]["REQUIRED"]) ? $xmlArr["ATTRIBUTES"]["REQUIRED"] : null;
      $this->m_BlankOption=isset($xmlArr["ATTRIBUTES"]["BLANKOPTION"]) ? $xmlArr["ATTRIBUTES"]["BLANKOPTION"] : null;
      $this->m_Enabled = isset($xmlArr["ATTRIBUTES"]["ENABLED"]) ? $xmlArr["ATTRIBUTES"]["ENABLED"] : null;
      $this->m_HTMLAttr = isset($xmlArr["ATTRIBUTES"]["HTMLATTR"]) ? $xmlArr["ATTRIBUTES"]["HTMLATTR"] : null;
      $this->m_SelectFrom = isset($xmlArr["ATTRIBUTES"]["SELECTFROM"]) ? $xmlArr["ATTRIBUTES"]["SELECTFROM"] : null;
      $this->m_Hidden = isset($xmlArr["ATTRIBUTES"]["HIDDEN"]) ? $xmlArr["ATTRIBUTES"]["HIDDEN"] : null;
      $this->m_Link = isset($xmlArr["ATTRIBUTES"]["LINK"]) ? $xmlArr["ATTRIBUTES"]["LINK"] : null;
      $this->m_DisplayMode = isset($xmlArr["ATTRIBUTES"]["DISPLAYMODE"]) ? $xmlArr["ATTRIBUTES"]["DISPLAYMODE"] : null;

      $this->PrefixSelectFrom();
      if (!$this->m_Type)
         $this->m_Type = "TEXT";

      // read EventHandler element
      if (isset($xmlArr["EVENTHANDLER"]))  // 2.1 eventhanlders
         $this->m_EventHandlers = new MetaIterator($xmlArr["EVENTHANDLER"],"EventHandler");
      else if (isset($xmlArr["ATTRIBUTES"]["FUNCTION"])) {   // openbiz 2.0 compatible
         $func = isset($xmlArr["ATTRIBUTES"]["FUNCTION"]) ? $xmlArr["ATTRIBUTES"]["FUNCTION"] : null;
         $funcType = isset($xmlArr["ATTRIBUTES"]["FUNCTIONTYPE"]) ? $xmlArr["ATTRIBUTES"]["FUNCTIONTYPE"] : null;
         $postAct = isset($xmlArr["ATTRIBUTES"]["POSTACTION"]) ? $xmlArr["ATTRIBUTES"]["POSTACTION"] : null;
         $shortKey = isset($xmlArr["ATTRIBUTES"]["SHORTCUTKEY"]) ? $xmlArr["ATTRIBUTES"]["SHORTCUTKEY"] : null;
         $tempArr = array("ATTRIBUTES"=>array("NAME"=>"onevent","EVENT"=>"onclick",
                          "FUNCTION"=>$func,
                          "FUNCTIONTYPE"=>$funcType,
                          "POSTACTION"=>$postAct,
                          "SHORTCUTKEY"=>$shortKey));
         $this->m_EventHandlers = new MetaIterator($tempArr,"EventHandler");
      }
      /*if ($xmlArr["EVENTHANDLER"])  // 2.1 eventhanlders
         $this->m_EventHandlers = new MetaIterator($xmlArr["EVENTHANDLER"],"EventHandler");
      else if ($xmlArr["ATTRIBUTES"]["FUNCTION"]) {   // openbiz 2.0 compatible
         $tempArr = array("ATTRIBUTES"=>array("NAME"=>"onevent","EVENT"=>"onclick",
                          "FUNCTION"=>$xmlArr["ATTRIBUTES"]["FUNCTION"],
                          "FUNCTIONTYPE"=>$xmlArr["ATTRIBUTES"]["FUNCTIONTYPE"],
                          "POSTACTION"=>$xmlArr["ATTRIBUTES"]["POSTACTION"],
                          "SHORTCUTKEY"=>$xmlArr["ATTRIBUTES"]["SHORTCUTKEY"]));
         $this->m_EventHandlers = new MetaIterator($tempArr,"EventHandler");
      }*/
      if ($this->m_EventHandlers != null) {
         foreach ($this->m_EventHandlers as $evthdl)
            $evthdl->SetFormName($formObj->m_Name);
      }
   }

   protected function GetFormObj()
   {
      global $g_BizSystem;
	   return $g_BizSystem->GetObjectFactory()->GetObject($this->m_BizFormName);
   }

   // change the BizFormName after inherit from parent form
   public function AdjustBizFormName($bizFormName)
   {
      if ($this->m_BizFormName == $bizFormName)
         return;
      $this->m_BizFormName = $bizFormName;
      if ($this->m_EventHandlers != null) {
         foreach ($this->m_EventHandlers as $evthdl)
            $evthdl->AdjustFormName($this->m_BizFormName);
      }
   }

   private function PrefixSelectFrom()
   {
      // selectFrom can have format: "selectionXmlFile(key)" or "selectionDO[field]" or "value"
      // in case of selectionXmlFile(key) or selectionDO[field], add package as prefix
      if ($this->m_SelectFrom) {
         $dot_pos = strpos($this->m_SelectFrom, ".");
         $leftpr_pos = strpos($this->m_SelectFrom, "(");
         $leftbr_pos = strpos($this->m_SelectFrom, "[");
         if ($leftpr_pos > 0 || $leftbr_pos > 0) {
            $left_pos = $leftpr_pos > 0 ? $leftpr_pos : $leftbr_pos;
            if ($dot_pos > 0 && $dot_pos < $left_pos)
               $this->m_SelectFrom = $this->PrefixPackage($this->m_SelectFrom);
            if ($dot_pos === false)
               $this->m_SelectFrom = $this->PrefixPackage($this->m_SelectFrom);
         }
      }
   }

   /**
    * HTMLControl::SetState() - Set state (Enabled or Disabled) of this control
    *
    * @param string $state
    * @return void
    */
   public function SetState($state)
   {
      $this->m_State = $state;
   }

   /**
    * HTMLControl::SetMode() - Set display mode of this control
    *
    * @param string $mode
    * @return void
    */
   public function SetMode($mode, $dataFormat)
   {
      $this->m_Mode = $mode;
      $this->m_DataFormat = $dataFormat;
   }

   public function SetValue($val)
   {
	  $this->m_Value = $val;
   }

   public function GetValue()
   {
      return $this->m_Value;
   }

   public function GetProperty($propertyName)
	{
	   $ret = parent::GetProperty($propertyName);
	   if ($ret) return $ret;
	   if ($propertyName == "Value") return $this->m_Value;
	   return $this->$propertyName;
	}

   /**
    * HTMLControl::CanDisplayed() - Check if the control can be displayed
    *
    * @return boolean
    */
   public function CanDisplayed()
   {
      if ($this->GetHidden() == "Y")
         return false;
      if (!$this->m_DisplayMode)
         return true;
      if ($this->m_DisplayMode == $this->m_Mode)
         return true;
      if (strpos($this->m_DisplayMode, $this->m_Mode) === false)
         return false;
      return true;
   }

   /**
    * HTMLControl::Render() - render the control by html
    *
    * @return string HTML text
    */
   public function Render()
   {
      $cType = strtoupper($this->m_Type);
      if ($cType == "TEXT")   $sHTML = $this->RenderText();
      else if ($cType == "LISTBOX")   $sHTML = $this->RenderListBox();
      else if ($cType == "TEXTAREA")   $sHTML = $this->RenderTextArea();
      else if ($cType == "CHECKBOX")   $sHTML = $this->RenderCheckBox();
      else if ($cType == "RADIO")   $sHTML = $this->RenderRadio();
      else if ($cType == "BUTTON")   $sHTML = $this->RenderButton();
      else if ($cType == "HTMLBUTTON")   $sHTML = $this->RenderHTMLButton();
      else if ($cType == "RESETBUTTON")   $sHTML = $this->RenderResetButton();
      else if ($cType == "SUBMITBUTTON")   $sHTML = $this->RenderSubmitButton();
      else if ($cType == "PASSWORD")   $sHTML = $this->RenderPassword();
      else if ($cType == "HTMLBLOCK")   $sHTML = $this->RenderHTMLBlock();
      else if ($cType == "FILE")   $sHTML = $this->RenderFile();
      else if ($cType == "HIDDEN")   $sHTML = $this->RenderHidden();
      else if ($cType == "RICHTEXT")   $sHTML = $this->RenderRichText();
      else if ($cType == "AUTOSUGGEST")   $sHTML = $this->RenderAutoSuggest();
      else $sHTML = $this->RenderText();
      return $sHTML;
   }

   protected function GetStyle()
   {
      $cls = $this->m_cssClass ? "CLASS='".$this->m_cssClass."' " : null;

      if ($this->m_Width && $this->m_Width>=0)
         $style .= "width:".$this->m_Width.";";
      if ($this->m_Height && $this->m_Height>=0)
         $style .= "height:".$this->m_Height.";";
      if ($this->m_Style)
         $style .= $this->m_Style;
      if (!isset($style) && !$cls)
         return null;
      if (isset($style))
      {
         $formobj = $this->GetFormObj();
         $style = Expression::EvaluateExpression($style, $formobj);
         $style = "STYLE='$style'";
      }
      if ($cls)
         $style = $cls." ".$style;
      return $style;
   }

   // get shortcut key function map
   public function GetSCKeyFuncMap()
   {
      if (!$this->CanDisplayed()) return null;
      $map = array();
      $formobj = $this->GetFormObj();
      if ($this->m_EventHandlers == null)
         return null;
      foreach ($this->m_EventHandlers as $evthdl)
      {
         if ($evthdl->m_ShortcutKey) {
            $temp = ($evthdl->m_FunctionType==null) ? "" : ",'".$evthdl->m_FunctionType."'";
            $func = "CallFunction('" . $evthdl->m_Function . "'$temp)";
            $func = Expression::EvaluateExpression($func, $formobj);
            $map[$evthdl->m_ShortcutKey] = $func;
         }
      }
      return $map;
   }
   
   public function GetContextMenu()
   {
      if (!$this->CanDisplayed()) return null;
      $menus = array();
      $formobj = $this->GetFormObj();
      if ($this->m_EventHandlers == null)
         return null;
      $i = 0;
      foreach ($this->m_EventHandlers as $evthdl)
      {
         if ($evthdl->m_ContextMenu) {
            $temp = ($evthdl->m_FunctionType==null) ? "" : ",'".$evthdl->m_FunctionType."'";
            $func = "CallFunction('" . $evthdl->m_Function . "'$temp)";
            $func = Expression::EvaluateExpression($func, $formobj);
            $menus[$i]['text'] = $evthdl->m_ContextMenu;
            $menus[$i]['func'] = $func;
            $menus[$i]['key'] = $evthdl->m_ShortcutKey;
         }
         $i++;
      }
      return $menus;
   }

   protected function GetFunction()
   {
      $name = $this->m_Name;
      // loop through the event handlers
      $func = "";
      // *** will link conflict with other attributes like drilldownlink?
      if ($this->m_EventHandlers == null && $this->m_Link != null) {
         $func .= " onclick=\"loadPage('" . $this->GetLink() . "');\"";
         return $func;
      }
      if ($this->m_EventHandlers == null)
         return null;
      foreach ($this->m_EventHandlers as $evthdl)
      {
         $ehName = $evthdl->m_Name;
         $event = $evthdl->m_Event;
         if (!$event) continue;
         // add direct URL support
         if ($evthdl->m_URL)
            $func .= " $event=\"loadPage('" . $evthdl->m_URL . "');\"";
         else if (strpos($evthdl->m_Function, "js:") === 0)
            $func .= " $event=\"".substr($evthdl->m_Function, 3).";\"";
         else {
            $temp = ($evthdl->m_FunctionType==null) ? "" : ",'".$evthdl->m_FunctionType."'";
            $func .= " $event=\"SetOnElement('$name:$ehName'); CallFunction('" . $evthdl->m_Function . "'$temp);\"";
            //$func .= " $event=\"this.style.cursor=''; SetOnElement('$name:$ehName'); CallFunction('" . $evthdl->m_Function . "'$temp);\"";
         }
      }

      $formobj = $this->GetFormObj();
      $func = Expression::EvaluateExpression($func, $formobj);

      /*
      if ($this->m_Function) {
         $cType = strtoupper($this->m_Type);
         if ($cType=="TEXT" || $cType=="TEXTAREA" || $cType=="LISTBOX")
            $func = "onChange=\"SetOnElement('$name'); CallFunction('" . $this->m_Function . "'$temp);\"";
         else
            $func = "onClick=\"SetOnElement('$name'); CallFunction('" . $this->m_Function . "'$temp);\"";
      }
      else if ($this->m_Link) {
         $cType = strtoupper($this->m_Type);
         if ($cType=="TEXT" || $cType=="TEXTAREA" || $cType=="LISTBOX")
            $func = "onChange=\"loadPage('".$this->m_Link."')\"";
         else
            $func = "onClick=\"loadPage('".$this->m_Link."')\"";
      }*/
      return $func;
   }

   protected function GetEnabled()
   {
      $formobj = $this->GetFormObj();
      return Expression::EvaluateExpression($this->m_Enabled, $formobj);
   }

   protected function GetHidden()
   {
      $formobj = $this->GetFormObj();
      return Expression::EvaluateExpression($this->m_Hidden, $formobj);
   }

   protected function GetLink()
   {
      if ($this->m_Link == null)
         return null;
      $formobj = $this->GetFormObj();
      return Expression::EvaluateExpression($this->m_Link, $formobj);
   }

   protected function GetSelectFrom()
   {
      $formobj = $this->GetFormObj();
      return Expression::EvaluateExpression($this->m_SelectFrom, $formobj);
   }

   public function GetPostAction($ehname)
   {
      $formobj = $this->GetFormObj();
      $evthandler = $this->m_EventHandlers->get($ehname);
      if (!$evthandler) return null;
      return Expression::EvaluateExpression($evthandler->m_PostAction, $formobj);
   }

   protected function RenderText()
   {
	   if ($this->m_SelectFrom)
         return $this->RenderListBox();

      $disabledStr = ($this->GetEnabled() == "N") ? "DISABLED=\"true\"" : "";
      $style = $this->GetStyle();
      $func = $this->GetFunction();
      $sHTML = "<INPUT NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" VALUE=\"" . $this->m_Value . "\" $disabledStr $this->m_HTMLAttr $style $func>";
      return $sHTML;
   }

   protected function RenderListBox()
   {
      $fromlist = array();
      $this->GetFromList($fromlist);
      $value_arr = explode(',', $this->m_Value);
      $disabledStr = ($this->GetEnabled() == "N") ? "DISABLED=\"true\"" : "";
      $style = $this->GetStyle();
      $func = $this->GetFunction();
      $sHTML = "<SELECT NAME=\"" . $this->m_Name . "[]\" ID=\"" . $this->m_Name ."\" $disabledStr $this->m_HTMLAttr $style $func>";
      
      
      if ($this->m_BlankOption) // ADD a blank option 
      {
       	$entry=split(",",$this->m_BlankOption);
   		$text=$entry[0];
   		$value=($entry[1]!="") ? $entry[1] : 0;
   		$entrylist=array(array("val" => $value, "txt" => $text ));
   		$fromlist=array_merge($entrylist,$fromlist);
      }
	   
      if ($this->m_Mode == MODE_Q)
         $sHTML .= "<OPTION VALUE=\"\"></OPTION>";	 
      foreach ($fromlist as $opt) {
         $test = array_search($opt['val'], $value_arr);
         if ($test === false) {
            $selectedStr = '';
         } else {
            $selectedStr = "SELECTED";
         }
         $sHTML .= "<OPTION VALUE=\"" . $opt['val'] . "\" $selectedStr>" . $opt['txt'] . "</OPTION>";	 
      }
      $sHTML .= "</SELECT>";
      /* editable combobox
      <div style="position: relative;">
      <select style="position: absolute; width: 146px; height: 18px; z-index: 1; clip: rect(auto, auto, auto, 127px);">
      <option value="" selected="selected"/>
      <option value="Homer">Homer</option>
      <option value="Marge">Marge</option>
      <option value="Bart">Bart</option>
      <option value="Lisa">Lisa</option>
      <option value="Maggie">Maggie</option>
      </select>
      <div>
      <input type="text" style="width: 128px; height: 20px;"/>
      </div>
      </div>
      */
      return $sHTML;
   }

   protected function RenderTextArea()
   {
      $disabledStr = ($this->GetEnabled() == "N") ? "DISABLED=\"true\"" : "";
      $style = $this->GetStyle();
      $func = $this->GetFunction();
      $sHTML = "<TEXTAREA NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" $disabledStr $this->m_HTMLAttr $style $func>".$this->m_Value."</TEXTAREA>";
      return $sHTML;
   }

   protected function RenderRichText()
   {
      $ctrlname = $this->m_Name;
      $ctrlname_container = $ctrlname."_container";
      $value = $this->GetValue();
      $style = $this->GetStyle();
      $w = $this->m_Width ? $this->m_Width : 600;
      $h = $this->m_Height ? $this->m_Height : 300;
      //$func = "onclick=\"editRichText('$ctrlname',$w,$h);\"";
      if(!strlen($value)>0) // fix suggested by smarques
         $value="&nbsp;";
      $sHTML = "<DIV id='$ctrlname_container' $style $func>".$value."</DIV>\n";
      $sHTML .= "<input type='hidden' id='hdn$ctrlname' name='$ctrlname' value=\"".$value."\">"."\n";
      //$sHTML .= "<textarea rows=2 cols=20 id='hdn$ctrlname' name='$ctrlname'>".$value."</textarea>\n";
      $sHTML .= "<script>editRichText('$ctrlname',$w,$h);</script>";
      return $sHTML;
   }

   protected function RenderCheckBox()
   {
      $boolValue = $this->GetSelectFrom();
      $disabledStr = ($this->GetEnabled() == "N") ? "DISABLED=\"true\"" : "";
      $checkedStr = ($boolValue == $this->m_Value) ? "CHECKED" : "";
      $style = $this->GetStyle();
      $func = $this->GetFunction();
      $sHTML = '';
      $fromlist = array();
      $this->GetFromList($fromlist);
      if (count($fromlist) > 1) {
         $value_arr = explode(',', $this->m_Value);

         foreach ($fromlist as $opt) {
            $test = array_search($opt['val'], $value_arr);
            if ($test === false) {
               $checkedStr = '';
            } else {
               $checkedStr = "CHECKED";
            }
            $sHTML .= "<INPUT TYPE=CHECKBOX NAME='".$this->m_Name."[]' ID=\"" . $this->m_Name ."\" VALUE=\"" . $opt['val'] . "\" $checkedStr $disabledStr $this->m_HTMLAttr $func>" . $opt['txt'] . "";
         }
      } else {
         $sHTML = "<INPUT TYPE=\"CHECKBOX\" NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" VALUE='$boolValue' $checkedStr $disabledStr $this->m_HTMLAttr $style $func>";
      }

      return $sHTML;
   }

   protected function RenderRadio()
   {
      $fromlist = array();
      $this->GetFromList($fromlist);
      $disabledStr = ($this->GetEnabled() == "N") ? "DISABLED=\"true\"" : "";
      $style = $this->GetStyle();
      $func = $this->GetFunction();
      foreach ($fromlist as $opt) {
         $checkedStr = ($opt['val'] == $this->m_Value) ? "CHECKED" : "";
         $sHTML .= "<INPUT TYPE=RADIO NAME='".$this->m_Name."' ID=\"" . $this->m_Name ."\" VALUE=\"" . $opt['val'] . "\" $checkedStr $disabledStr $this->m_HTMLAttr $func>" . $opt['txt'] . "";
      }
      return "<SPAN $style>".$sHTML."</SPAN>";
   }

   protected function RenderButton()
   {
      $style = $this->GetStyle();
      $func = $this->m_State == "ENABLED" ? $this->GetFunction() : "";
      $mouseover = "onmouseover=\"window.status='". $this->m_Caption ."'; return true;\"";
      $cls = !$this->m_cssClass ? $this->m_Type : $this->m_cssClass;

      if ($this->m_Image) {
         $out = "<img src=\"../images/" . $this->m_Image . "\" ALIGN=MIDDLE BORDER=0 $mouseover class=$cls title=\"" . $this->m_Caption . "\">";
         if ($func != "")
            $out = "<a href='javascript:void(0);' $this->m_HTMLAttr $func>".$out."</a>";
      }
      else {
         $out = $this->m_Caption;
         $out = "<input type='button' value='$out' class='$cls' $this->m_HTMLAttr $style $func $mouseover>";
         //$out = "<span class='$cls'>$out</span>";
      }

      return $out;
   }

   protected function RenderHTMLButton()
   {
      $disabledStr = ($this->GetEnabled() == "N") ? "DISABLED=\"true\"" : "";
      $func = $this->GetFunction();
      $style = $this->GetStyle();
      $sHTML .= "<INPUT TYPE=BUTTON NAME='$this->m_Name' ID=\"" . $this->m_Name ."\" VALUE='$this->m_Caption' $disabledStr $this->m_HTMLAttr $func $style>";
      return $sHTML;
   }

   protected function RenderResetButton()
   {
      $disabledStr = ($this->GetEnabled() == "N") ? "DISABLED=\"true\"" : "";
      $style = $this->GetStyle();
      $func = $this->GetFunction();
      $sHTML .= "<INPUT TYPE=RESET NAME='$this->m_Name' ID=\"" . $this->m_Name ."\" VALUE='$this->m_Caption' $disabledStr $this->m_HTMLAttr $style $func>";
      return $sHTML;
   }

   protected function RenderSubmitButton()
   {
      $disabledStr = ($this->GetEnabled() == "N") ? "DISABLED=\"true\"" : "";
      $style = $this->GetStyle();
      $func = $this->GetFunction();
      $sHTML .= "<INPUT TYPE=SUBMIT NAME='$this->m_Name' ID=\"" . $this->m_Name ."\" VALUE='$this->m_Caption' $disabledStr $this->m_HTMLAttr $style $func>";
      return $sHTML;
   }

   protected function RenderPassword()
   {
      $disabledStr = ($this->GetEnabled() == "N") ? "DISABLED=\"true\"" : "";
      $style = $this->GetStyle();
      $sHTML .= "<INPUT TYPE=PASSWORD NAME='$this->m_Name' ID=\"" . $this->m_Name ."\" VALUE='$this->m_Value' $disabledStr $this->m_HTMLAttr $style>";
      return $sHTML;
   }

   protected function RenderHTMLBlock()
   {
      $style = $this->GetStyle();
      if ($style)
         return "<span $style>".$this->m_Caption."</span>";
      else
         return $this->m_Caption;
   }

   protected function RenderFile()
   {
      $disabledStr = ($this->GetEnabled() == "N") ? "DISABLED=\"true\"" : "";
      $style = $this->GetStyle();
      $sHTML .= "<INPUT TYPE=FILE NAME='$this->m_Name' ID=\"" . $this->m_Name ."\" VALUE='$this->m_Value' $disabledStr $this->m_HTMLAttr $style>";
      return $sHTML;
   }

   protected function RenderHidden()
   {
      $sHTML = "<INPUT TYPE=HIDDEN NAME='$this->m_Name' ID=\"" . $this->m_Name ."\" VALUE='$this->m_Value' $this->m_HTMLAttr>";
      return $sHTML;
   }

   protected function RenderAutoSuggest()
   {
      $inputName = $this->m_Name;
      $inputChoice = $this->m_Name.'_choices';
      $sHTML = "<input type=\"text\" id=\"$inputName\" name=\"$inputName\" value=\"" . $this->m_Value . "\" onfocus=\"initAutoSuggest('$this->m_BizFormName','AutoSuggest','$inputName','$inputChoice');\"/>";
      $sHTML .= "<div id=\"$inputChoice\" class=\"autocomplete\" style=\"display:none\"></div>";
      return $sHTML;
   }

   public function GetFromList(&$list)
   {
     $selFrom = $this->GetSelectFrom();
     $pos0 = strpos($selFrom, "(");
     $pos1 = strpos($selFrom, ")");
     if ($pos0>0 && $pos1>$pos0) {  // select from xml file
        $xmlFile = substr($selFrom, 0, $pos0);
        $tag = substr($selFrom, $pos0 + 1, $pos1 - $pos0-1);
        $tag = strtoupper($tag);
        $xmlFile = BizSystem::GetXmlFileWithPath ($xmlFile);
        if (!$xmlFile) return;
        $xmlArr = &BizSystem::GetXmlArray($xmlFile);
        if ($xmlArr) {
          $i=0;
          if (!key_exists($tag, $xmlArr["SELECTION"]))
            return;
          foreach($xmlArr["SELECTION"][$tag] as $node) {
            $list[$i]['val'] = $node["ATTRIBUTES"]["VALUE"];
            if ($node["ATTRIBUTES"]["TEXT"])
            {
            	$list[$i]['txt'] = I18n::getInstance()->translate($node["ATTRIBUTES"]["TEXT"]) ;
            }

            else
            {
            	$list[$i]['txt'] = I18n::getInstance()->translate($list[$i]['val']);
            }
            $i++;
          }
        }
        return;
     }

      $pos0 = strpos($selFrom, "[");
      $pos1 = strpos($selFrom, "]");
      if ($pos0>0 && $pos1>$pos0) {  // select from bizObj
         // support BizObjName[BizFieldName] or BizObjName[BizFieldName4Text:BizFieldName4Value]
         $bizobjName = substr($selFrom, 0, $pos0);
         $pos3 = strpos($selFrom, ":");
         if($pos3>$pos0 && $pos3<$pos1) {
            $fldName = substr($selFrom, $pos0+1, $pos3-$pos0-1);
            $fldName_v = substr($selFrom, $pos3+1, $pos1-$pos3-1);
         }
         else {
            $fldName = substr($selFrom, $pos0 + 1, $pos1 - $pos0-1);
            $fldName_v = $fldName;
         }
         $pos_comma = strpos($selFrom, ",", $pos1);
         if ($pos_comma > $pos1)
            $searchRule = trim(substr($selFrom, $pos_comma+1));
         global $g_BizSystem;
         $bizobj = $g_BizSystem->GetObjectFactory()->GetObject($bizobjName);
         if (!$bizobj)
            return;

         $recList = array();
         $old_assoc = $bizobj->m_Association;
         $bizobj->m_Association = null;
         $recList = $bizobj->DirectFetch($searchRule);
         $bizobj->m_Association = $old_assoc;

         foreach ($recList as $rec)
         {
            $list[$i]['val'] = $rec[$fldName_v];
            $list[$i]['txt'] = $rec[$fldName];
            $i++;
         }
         return;
      }
      
      // in case of a|b|c
      $recList = explode('|',$selFrom);
      foreach ($recList as $rec)
      {
         $list[$i]['val'] = $rec;
         $list[$i]['txt'] = $rec;
         $i++;
      }
      return;
   }
  
}

class EventHandler
{
   public $m_Name;
   public $m_Event;
   public $m_Function;     // support expression
   public $m_FunctionType;
   public $m_PostAction;   // support expression
   public $m_ShortcutKey;
   public $m_ContextMenu;
   private $m_FormName;

   // add URL here so that direct url string can be given
   public $m_URL;

   function __construct(&$xmlArr)
   {
      $this->m_Name = isset($xmlArr["ATTRIBUTES"]["NAME"]) ? $xmlArr["ATTRIBUTES"]["NAME"] : null;
      $this->m_Event = isset($xmlArr["ATTRIBUTES"]["EVENT"]) ? $xmlArr["ATTRIBUTES"]["EVENT"] : null;
      $this->m_Function = isset($xmlArr["ATTRIBUTES"]["FUNCTION"]) ? $xmlArr["ATTRIBUTES"]["FUNCTION"] : null;
      $this->m_FunctionType = isset($xmlArr["ATTRIBUTES"]["FUNCTIONTYPE"]) ? $xmlArr["ATTRIBUTES"]["FUNCTIONTYPE"] : null;
      $this->m_PostAction = isset($xmlArr["ATTRIBUTES"]["POSTACTION"]) ? $xmlArr["ATTRIBUTES"]["POSTACTION"] : null;
      $this->m_ShortcutKey = isset($xmlArr["ATTRIBUTES"]["SHORTCUTKEY"]) ? $xmlArr["ATTRIBUTES"]["SHORTCUTKEY"] : null;
      $this->m_ContextMenu = isset($xmlArr["ATTRIBUTES"]["CONTEXTMENU"]) ? $xmlArr["ATTRIBUTES"]["CONTEXTMENU"] : null;
      $this->m_URL = isset($xmlArr["ATTRIBUTES"]["URL"]) ? $xmlArr["ATTRIBUTES"]["URL"] : null;
   }

   public function SetFormName($formname)
   {
      $this->m_FormName = $formname;
      if (strpos($this->m_Function, "js:")===0)
         return;
      // if no class name, add default class name. i.e. NewRecord => ObjName.NewRecord
      if ($this->m_Function) {
         $pos_dot = strpos($this->m_Function, ".");
         $pos_lpt = strpos($this->m_Function, "(");
         if (!$pos_dot || $pos_lpt < $pos_dot)
            $this->m_Function = $this->m_FormName.".".$this->m_Function;
      }
   }

   public function AdjustFormName($formname)
   {
      $this->m_FormName = $formname;
      // if no class name, add default class name. i.e. NewRecord => ObjName.NewRecord
      if ($this->m_Function) {
         $pos = strrpos($this->m_Function, ".");
         if ($pos > 0)
            $this->m_Function = $this->m_FormName.".".substr($this->m_Function, $pos+1);
      }
   }
}
?>
