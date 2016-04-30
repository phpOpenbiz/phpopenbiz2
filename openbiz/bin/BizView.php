<?PHP
/**
 * BizView class - BizView is the class that contains list of forms. View is same as html page.
 * 
 * @package BizView
 * @author rocky swen 
 * @copyright Copyright (c) 2005
 * @access public 
 */
class BizView extends MetaObject implements iSessionObject 
{
   protected $m_Template;
   protected $m_ViewSet;
   protected $m_ChildFormList = array();
   protected $m_MetaChildFormList = array();
   protected $m_Parameters = null;
   protected $m_IsPopup = false;
   protected $m_Height;
   protected $m_Width;
   protected $m_ConsoleOutput = true;

   /**
    * BizView::__construct(). Initialize BizView with xml array
    * 
    * @param array $xmlArr
    * @return void 
    */
   public function __construct(&$xmlArr)
   {
      $this->ReadMetadata($xmlArr);
      $this->InitAllForms();
   }
   
   // initialize all form objects. called only once by BizController in RenderView()
   protected function InitAllForms()
   {
      global $g_BizSystem;
      // build forms included in the view
      foreach($this->m_MetaChildFormList as $form) {
         $pkg_form = $this->PrefixPackage($form["FORM"]);
         $formobj = $g_BizSystem->GetObjectFactory()->GetObject($pkg_form);
         if (method_exists($formobj, "SetSubForms"))
            $formobj->SetSubForms($form["SUBCTRLS"]);
         $this->AddChildForm($formobj);
      } 
      foreach($this->m_ChildFormList as $formobj)
      {
         if (method_exists($formobj, "GetSubForms")) {
            $subForms = $formobj->GetSubForms();
            if ($subForms) {
               foreach ($subForms as $subformName) {
                  $this->m_ChildFormList[$subformName]->SetParentForm($formobj->m_Name);
               }
            }
         }
      }
   }
   
   protected function ReadMetadata(&$xmlArr)
   {
      parent::ReadMetaData($xmlArr);
      $this->m_Name = $this->PrefixPackage($this->m_Name);
      $this->m_Template = isset($xmlArr["BIZVIEW"]["ATTRIBUTES"]["TEMPLATE"]) ? $xmlArr["BIZVIEW"]["ATTRIBUTES"]["TEMPLATE"] : null;
      $this->m_ViewSet = isset($xmlArr["BIZVIEW"]["ATTRIBUTES"]["VIEWSET"]) ? $xmlArr["BIZVIEW"]["ATTRIBUTES"]["VIEWSET"] : null;
      
      // build ControlList
      $tmpList = null;
      if (isset($xmlArr["BIZVIEW"]["CONTROLLIST"]["CONTROL"]))
         $this->ReadMetaCollection($xmlArr["BIZVIEW"]["CONTROLLIST"]["CONTROL"], $tmpList);
      if (!$tmpList) return;
      foreach ($tmpList as $ctrl)
         $this->m_MetaChildFormList[] = $ctrl["ATTRIBUTES"];
         
      // read in parameters
      if (isset($xmlArr["BIZVIEW"]["PARAMETERS"]["PARAMETER"]))
         $this->m_Parameters = new MetaIterator($xmlArr["BIZVIEW"]["PARAMETERS"]["PARAMETER"],"Parameter");
   }
   
   /**
    * BizView::GetSessionContext() - Retrieve Session data of this object
    * 
    * @param SessionContext $sessCtxt
    * @return void 
    */
   public function GetSessionVars($sessCtxt)
	{
	   $sessCtxt->GetObjVar($this->m_Name, "Parameters", $paramArray);
	   $this->SetParameters($paramArray);
	}
	/**
    * BizView::SetSessionContext() - Save Session data of this object
    * 
    * @param SessionContext $sessCtxt
    * @return void 
    */
	public function SetSessionVars($sessCtxt)
	{
	   $paramArray = $this->GetParameterArray();
	   $sessCtxt->SetObjVar($this->m_Name, "Parameters", $paramArray);
	}
	
	public function GetParameterArray()
	{
	   $paramArray = null;
	   if (!$this->m_Parameters) return null;
	   foreach ($this->m_Parameters as $param)
	      $paramArray[$param->m_Name] = $param->m_Value;
	   return $paramArray;
	}
	
	public function GetParameter($paramName)
	{
	   return $this->m_Parameters->get($paramName);
	}
	
	public function SetParameters($paramArray)
	{
	   if (!$paramArray) return;
	   foreach ($paramArray as $paramName=>$paramValue) {
	      if ($this->m_Parameters->get($paramName))
	        $this->m_Parameters->get($paramName)->m_Value = $paramValue;
	      else {
	        $xmlArr["ATTRIBUTES"]["NAME"] = $paramName;
	        $xmlArr["ATTRIBUTES"]["VALUE"] = $paramValue;
	        $paramobj = new Parameter($xmlArr);
	        $this->m_Parameters->set($paramName, $paramobj);
	      }
	   }
	}
	
	public function GetProperty($propertyName)
	{
	   $ret = parent::GetProperty($propertyName);
	   if ($ret) return $ret;
      // get control object if propertyName is "type[ctrlname]"
      $pos1 = strpos($propertyName, "[");
      $pos2 = strpos($propertyName, "]");
      if ($pos1>0 && $pos2>$pos1) {
         $propType = substr($propertyName, 0, $pos1);
         $ctrlname = substr($propertyName, $pos1+1,$pos2-$pos1-1);
         if ($propType == "param") {   // get parameter
            return $this->m_Parameters->get($ctrlname);
         }
      }
	}
	
	public function GetViewSet() { return $this->m_ViewSet; }
	
	public function CleanViewHistory()
	{
	   global $g_BizSystem;
	   foreach($this->m_ChildFormList as $ctrl)
	   {
	      if (method_exists($ctrl, "CleanHistoryInfo"))
	        $ctrl->CleanHistoryInfo();
	   }
	}
	
	public function SetConsoleOutput($bConsoleOutput)
	{
	   $this->m_ConsoleOutput = $bConsoleOutput;
	} 
	
	protected function AddChildForm($ctrl)
	{
	   $this->m_ChildFormList[$ctrl->m_Name] = $ctrl;
	}
	
	/**
    * BizView::SetPopupSize() - Set the view as a popup window and set its size
    * 
    * @param integer $w, window width
    * @param integer $h, window height
    * @return void 
    */
	public function SetPopupSize($w,$h)
   {
      $this->m_IsPopup = true;
      $this->m_Width = $w;
      $this->m_Height = $h;
   }
   
   public function SetFormMode($form="", $mode="")
   {
      if (!$form || !$mode)
         return;
      $bizForm = $this->m_ChildFormList[$form];
      $bizForm->SetDisplayMode($mode);
   }
   
   /**
    * BizView::ProcessRule() - Convert rule to search rule of bizform
    * 
    * @param string $rule. It can be "[field] opr value ..." OR form.ctrl opr value
    *        opr can be =,>,>=,<,<=,!=. "LIKE %" SQL format is also valid rule
    * @return void 
    */
   public function ProcessRule($form="", $rule="")
   { 
      // convert \' to '
      $addSearchRule = str_replace("\'", "'", $rule);
      
      // case 1: form=... $rule=[field] opr value
      if ($form) {
         $bizForm = $this->m_ChildFormList[$form];
         if ($rule) {
            // set dependent search rule which is remembered in the session
            $bizForm->SetFixSearchRule($addSearchRule);
         } 
         return;
      }
      
      // case 2: form.ctrl opr value
      if ($rule) {
         // replace package.form.ctrl with [field]
         // search for all child forms to match form name.
         foreach ($this->m_ChildFormList as $bizForm)
         {
            $bFind = false;
            $formName = $bizForm->m_Name;
            while (preg_match("/$formName\.[a-zA-Z0-9_]+/i",$addSearchRule,$matches))
            {
               $match = $matches[0];
               $ctrlName = substr($match, strlen($formName)+1);
               $ctrlFieldName = $bizForm->GetControl($ctrlName)->m_BizFieldName; // not ctrl_id
               $addSearchRule = str_replace($match,"[".$ctrlFieldName."]",$addSearchRule);
               $bFind = true;
            }
            if ($bFind) {
               //echo "###".$bizForm->m_Name.",".$addSearchRule;
               // set dependent search rule which is remembered in the session
               $bizForm->SetFixSearchRule($addSearchRule);
            }
         }
      }
   } 
   
	/**
    * BizView::Render() - Render this view.
    *
    * @return void
    */
	public function Render()
	{
      return $this->_render();
	}

	public function ReRender()
	{
      return $this->_render(true);
	}
	
	protected function _render($bReRender=false)
	{
	   $smarty = BizSystem::GetSmartyTemplate();
	   global $g_BizSystem;
	   
	   if ($bReRender == false)
         $this->SetClientScripts();
      
      // todo: should enforce rendering parent form before rendering subforms, 
      // because subform's dataobj is a objreference of the parent dataobj.
      foreach ($this->m_ChildFormList as $form=>$formobj) {
         if ($bReRender) {
            if ($g_BizSystem->GetClientProxy()->HasFormRerendered($form) == false)
               $formobj->ReRender();
            $sHTML = $g_BizSystem->GetClientProxy()->GetFormOutput($form);
         }
         $sHTML = $formobj->Render();
         $controls[] = "\n<div id='" . $formobj->m_Name . "_container'>\n" . $sHTML . "\n</div>\n";
         if (isset($formobj->m_jsClass))
            $newClntObjs .= "NewObject('" . $formobj->m_Name . "','" . $formobj->m_jsClass . "'); \n";
         //$newClntObjs .= "var fobj=GetObject('".$formobj->m_Name."');\n";
      }
      
      // add clientProxy scripts 
      if ($bReRender == false)
      {
         $includedScripts = implode("\n", $g_BizSystem->GetClientProxy()->GetAppendedScripts());
      }
      
      if ($this->m_IsPopup && $bReRender==false) {
         $moveToCenter = "moveToCenter(self, ".$this->m_Width.", ".$this->m_Height.");";
         $scripts = $includedScripts."\n<script>\n" . $newClntObjs . $moveToCenter . "</script>\n";
      }
      else 
         $scripts = $includedScripts."\n<script>\n" . $newClntObjs . "</script>\n";
      $smarty->assign("scripts", $scripts);
      $smarty->assign_by_ref("view_description", $this->m_Description); 
      
      $smarty->assign_by_ref("controls", $controls); 
      if ($this->m_ConsoleOutput)
         $smarty->display($this->m_Template);
      else
         return $smarty->fetch($this->m_Template);
	}
   
   public static function GetPopupViewXML($package, $formName)
   {
      // generate an xml attribute array for a dynamic bizview
      $xmlArr["BIZVIEW"]["ATTRIBUTES"]["NAME"] = "__DynPopup";
      $xmlArr["BIZVIEW"]["ATTRIBUTES"]["DESCRIPTION"] = "Openbiz Popup";
      $xmlArr["BIZVIEW"]["ATTRIBUTES"]["PACKAGE"] = $package;
      $xmlArr["BIZVIEW"]["ATTRIBUTES"]["CLASS"] = "BizView";
      $xmlArr["BIZVIEW"]["ATTRIBUTES"]["TEMPLATE"] = "popup.tpl";
      $xmlArr["BIZVIEW"]["CONTROLLIST"]["CONTROL"]["ATTRIBUTES"]["FORM"] = $formName;
      return $xmlArr;
   }
   
   protected function SetClientScripts()
   {
      global $g_BizSystem;
      $g_BizSystem->GetClientProxy()->AppendScripts("prototype", "prototype.js");
      $g_BizSystem->GetClientProxy()->AppendScripts("clientUtil", "clientUtil.js");
      $g_BizSystem->GetClientProxy()->AppendScripts("jsval", "jsval.js");
      //$g_BizSystem->GetClientProxy()->AppendScripts("bsn.AutoSuggest", "bsn.AutoSuggest_2.1.3_comp.js");
   }
}

?>