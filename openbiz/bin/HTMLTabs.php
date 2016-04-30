<?PHP
/**
 * HTMLTabs - class HTMLTabs is the base class of HTML tabs
 * 
 * @package BizView
 * @author rocky swen 
 * @copyright Copyright (c) 2005
 * @version 1.2
 * @access public 
 */
class HTMLTabs extends MetaObject implements iUIControl 
{
   public $m_TemplateFile;
   public $m_TabViews = null;
   protected $m_CurrentTab = null;
      
   function __construct(&$xmlArr)
   {
      global $g_BizSystem;

      $this->ReadMetadata($xmlArr);
   }
   
   protected function ReadMetadata(&$xmlArr)
   {
      $this->m_Name = $xmlArr["TABS"]["ATTRIBUTES"]["NAME"];
      $this->m_Package = $xmlArr["TABS"]["ATTRIBUTES"]["PACKAGE"];
      $this->m_Class = $xmlArr["TABS"]["ATTRIBUTES"]["CLASS"];
      $this->m_TemplateFile = $xmlArr["TABS"]["ATTRIBUTES"]["TEMPLATEFILE"];
      $this->m_TabViews = new MetaIterator($xmlArr["TABS"]["TABVIEWS"]["VIEW"],"TabView");
   }
   
   public function SetCurrentTab($viewName)
   {
      $this->m_CurrentTab = $viewName;
   }
   
   public function Render()
   {
      global $g_BizSystem;
      $curView = $g_BizSystem->GetCurrentViewName();
      $curViewobj = ($curView) ? $g_BizSystem->GetObjectFactory()->GetObject($curView) : null;
				   		   
      $profile = $g_BizSystem->GetUserProfile();
      $svcobj = $g_BizSystem->GetService("accessService");
      $role = isset($profile["ROLE"]) ? $profile["ROLE"] : null;
      
      // list all views and highlight the current view
      // pass $tabs(caption, url, target, icon, current) to template
      $smarty = BizSystem::GetSmartyTemplate();
      $tabs = array();
      $i = 0;
      foreach ($this->m_TabViews as $tview)
      {
       
      	// tab is renderd if  access is granted in accessservice.xml
 	     	// tab is renderd if  no definition  is found in accessservice.xml (default)
         if ($svcobj->AllowViewAccess($tview->m_View, $role))
         {
       
	         $tabs[$i]['caption'] = $tview->m_Caption;
	         $tabs[$i]['url'] = $tview->m_URL ? $tview->m_URL : "javascript:GoToView('".$tview->m_View."')";
	         $tabs[$i]['target'] = $tview->m_Target;
	         $tabs[$i]['icon'] = $tview->m_Icon;
	         if ($this->m_CurrentTab)
	            $tabs[$i]['current'] = ($this->m_CurrentTab == $tview->m_Name) ? 1 : 0;
	         else if ($tview->m_ViewSet) {
	            // check if current view's viewset == tview->m_ViewSet
	            $tabs[$i]['current'] = ($curViewobj->GetViewSet() == $tview->m_ViewSet) ? 1 : 0;
	         }
	         else
	            $tabs[$i]['current'] = ($curView == $tview->m_View) ? 1 : 0;
	         $i++;
	       }  
      }
      $smarty->assign_by_ref("tabs", $tabs);
      return $smarty->fetch($this->m_TemplateFile);
      
      /*$sHTML = "<ul class='tabsmenu'>\n";
      foreach ($this->m_TabViews as $tview)
      {
         $icon = $tview->m_Icon;
         $img = $icon ? "<img src='../images/$icon' class=icon> " : "";
         $target = $tview->m_Target ? " target='".$tview->m_Target."'" : "";
         if ($curView == $tview->m_Name)
            $sHTML .= "<li><a href=\"javascript:GoToView('".$tview->m_Name."')\" class='current' $target>$img".$tview->m_Caption."</a></li>\n";
         else if ($tview->m_Name)
            $sHTML .= "<li><a href=\"javascript:GoToView('".$tview->m_Name."')\" class='other' $target>$img".$tview->m_Caption."</a></li>\n";
         else if ($tview->m_URL)
            $sHTML .= "<li><a href=\"".$tview->m_URL."\" $target><span>".$tview->m_Caption."</span></a></li>\n";
      }
      $sHTML .= "</ul>";
      return $sHTML;*/
   }
   
   public function ReRender() { return $this->Render(); }
}

class TabView
{
   public $m_Name;
   public $m_View;
   public $m_ViewSet;
   public $m_Caption;
   public $m_URL;
   public $m_Target;
   public $m_Icon;

   function __construct(&$xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_View = $xmlArr["ATTRIBUTES"]["VIEW"];
      $this->m_ViewSet = $xmlArr["ATTRIBUTES"]["VIEWSET"];
      $this->m_Caption =I18n::getInstance()->translate($xmlArr["ATTRIBUTES"]["CAPTION"]);
      $this->m_URL = $xmlArr["ATTRIBUTES"]["URL"];
      $this->m_Target = $xmlArr["ATTRIBUTES"]["TARGET"];
      $this->m_Icon = $xmlArr["ATTRIBUTES"]["ICON"];
   }
}
?>