<?PHP
/**
 * HTMLMenus - class HTMLMenus is the base class of HTML menus
 * 
 * @package BizView
 * @author rocky swen 
 * @copyright Copyright (c) 2005
 * @version 1.2
 * @access public 
 */
class HTMLMenus extends MetaObject implements iUIControl 
{
   protected $m_MenuItemsXml = null;
      
   function __construct(&$xmlArr)
   {
      $this->ReadMetadata($xmlArr);
   }
   
   protected function ReadMetadata(&$xmlArr)
   {
      $this->m_Name = $xmlArr["MENU"]["ATTRIBUTES"]["NAME"];
      $this->m_Package = $xmlArr["MENU"]["ATTRIBUTES"]["PACKAGE"];
      $this->m_Class = $xmlArr["MENU"]["ATTRIBUTES"]["CLASS"];
      
      $this->m_MenuItemsXml = $xmlArr["MENU"]["MENUITEM"];
   }
   
   public function Render()
   {
      // list all views and highlight the current view
      $sHTML = "<ul id='navmenu'>\n";
      $sHTML .= $this->RenderMenuItems($this->m_MenuItemsXml);
      $sHTML .= "</ul>";
      return $sHTML;
   }
   
   protected function RenderMenuItems(&$menuItemArray)
   {
      $sHTML = "";
      if (isset($menuItemArray["ATTRIBUTES"]))
      {
         $sHTML .= $this->RenderSingleMenuItem($menuItemArray);
      }
      else 
      {
         foreach ($menuItemArray as $menuItem)
         {
            $sHTML .= $this->RenderSingleMenuItem($menuItem);
         }
      }
      return $sHTML;
   }
   
   protected function RenderSingleMenuItem(&$menuItem)
   {
    
      global $g_BizSystem;		   		   
      $profile = $g_BizSystem->GetUserProfile();
      $svcobj = $g_BizSystem->GetService("accessService");
      $role = isset($profile["ROLE"]) ? $profile["ROLE"] : null;



    
    
      $sHTML = "";
      $url = $menuItem["ATTRIBUTES"]["URL"];
      $view = $menuItem["ATTRIBUTES"]["VIEW"];
      
      // menuitem is renderd if  access is granted in accessservice.xml
      // menuitem is renderd if  no definition  is found in accessservice.xml (default)
      if ($svcobj->AllowViewAccess($view, $role)) 
      {
      
      $caption = I18n::getInstance()->translate($menuItem["ATTRIBUTES"]["CAPTION"]);
      $target = $menuItem["ATTRIBUTES"]["TARGET"];
      $icon = $menuItem["ATTRIBUTES"]["ICON"];
      $img = $icon ? "<img src='../images/$icon' class=menu_img> " : "";

		if ($view) 
      	$url="javascript:GoToView('".$view."')";
            
      if ($target)
         $sHTML .= "<li><a href=\"".$url."\" target='$target'>$img".$caption."</a>";
      else
         $sHTML .= "<li><a href=\"".$url."\">$img".$caption."</a>";
      if ($menuItem["MENUITEM"]) {
         $sHTML .= "\n<ul>\n";
         $sHTML .= $this->RenderMenuItems($menuItem["MENUITEM"]);
         $sHTML .= "</ul>";
      }
      $sHTML .= "</li>\n";
      }
      return $sHTML;
   }
   
   public function ReRender() { return $this->Render(); }
}

?>