<?php

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
      //if (!$this->m_prtObj->GetDataObj()) return "";
      $nbar = array();
      $curPage = $this->m_prtObj->GetCurrentPageNumber();
      $totalPage = $this->m_prtObj->GetTotalPageCount();
      foreach($this->m_var as $ctrl) {
         if (!$ctrl->CanDisplayed()) continue;
         if (($curPage == 1) && (strpos($ctrl->m_Function, "PrevPage") === 0 || strpos($ctrl->m_Function, "GotoPage(1)") === 0))
            $ctrl->SetState("DISABLED");
         else if (($curPage == $totalPage) && (strpos($ctrl->m_Function, "NextPage") === 0 || strpos($ctrl->m_Function, "GotoPage(-1)") === 0))
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
?>