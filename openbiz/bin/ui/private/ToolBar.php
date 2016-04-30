<?php

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
?>