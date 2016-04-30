<?PHP
/**
 * HTMLTree - class HTMLTree is the base class of HTML tree
 * 
 * @package BizView
 * @author rocky swen 
 * @copyright Copyright (c) 2005
 * @version 1.2
 * @access public 
 */
class HTMLTree extends MetaObject implements iUIControl 
{
   protected $m_NodesXml = null;
      
   function __construct(&$xmlArr)
   {
      $this->ReadMetadata($xmlArr);
   }
   
   protected function ReadMetadata(&$xmlArr)
   {
      $this->m_Name = $xmlArr["TREE"]["ATTRIBUTES"]["NAME"];
      $this->m_Package = $xmlArr["TREE"]["ATTRIBUTES"]["PACKAGE"];
      $this->m_Class = $xmlArr["TREE"]["ATTRIBUTES"]["CLASS"];
      
      $this->m_NodesXml = $xmlArr["TREE"]["NODE"];
   }
   
   public function Render()
   {
      // list all views and highlight the current view
      $sHTML = "<ul class='expanded'>\n";
      $sHTML .= $this->RenderNodeItems($this->m_NodesXml);
      $sHTML .= "</ul>";
      return $sHTML;
   }
   
   protected function RenderNodeItems(&$nodeItemArray)
   {
      $sHTML = "";
      foreach ($nodeItemArray as $nodeItem)
      {
         $url = $nodeItem["ATTRIBUTES"]["URL"];
         $caption = I18n::getInstance()->translate($nodeItem["ATTRIBUTES"]["CAPTION"]);
         $target = $nodeItem["ATTRIBUTES"]["TARGET"];
         //$img = $nodeItem["ATTRIBUTES"]["IMAGE"];
         if ($nodeItem["NODE"])
            $image = "<img src='../images/plus.gif' class='collapsed' onclick='mouseClickHandler(this)'>";
         else 
            $image = "<img src='../images/topic.gif'>";

         if ($target)
            if ($url)
               $sHTML .= "<li class='tree'>$image <a href=\"".$url."\" target='$target'>".$caption."</a>";
            else
               $sHTML .= "<li class='tree'>$image $caption";
         else
            if ($url)
               $sHTML .= "<li class='tree'>$image <a href=\"".$url."\">".$caption."</a>";
            else
               $sHTML .= "<li class='tree'>$image $caption";
         if ($nodeItem["NODE"]) {
            $sHTML .= "\n<ul class='collapsed'>\n";
            $sHTML .= $this->RenderNodeItems($nodeItem["NODE"]);
            $sHTML .= "</ul>";
         }
         $sHTML .= "</li>\n";
      }
      return $sHTML;
   }
   
   public function ReRender() { return $this->Render(); }
}

?>