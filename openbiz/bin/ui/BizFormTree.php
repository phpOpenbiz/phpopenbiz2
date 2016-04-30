<?php
/**
 * BizFormTree class - extension of BizForm to support field selection from a popup form
 * 
 * @package BizView
 */
class BizFormTree extends BizForm 
{
   private $m_Parents = array();
   private $m_DirectParentId = "";
   
   public function GetSessionVars($sessCtxt)
	{
      $sessCtxt->GetObjVar($this->m_Name, "DirectParent", $this->m_DirectParentId);
      parent::GetSessionVars($sessCtxt);
	}
	
   public function SetSessionVars($sessCtxt)
	{
      $sessCtxt->SetObjVar($this->m_Name, "DirectParent", $this->m_DirectParentId);
      parent::SetSessionVars($sessCtxt);
	}
   
   // function to find all parents of a record
   private function getAllLevelParents($rec, $includeSelf=false)
   {
      $_pid = $rec['PId'];
      $parents = array();
      if ($includeSelf)
         $parents[] = $rec;
      do
      {
         $prt = $this->getNodeRecord($_pid);
         if ($prt == null)
            break;
         else {
            $parents[] = $prt;
         }
         if ($prt['PId'] == null || $prt['PId'] == '')
            break;
         $_pid = $prt['PId'];
      } while ($pid == "");
      return $parents;
   }
   
   private function getNodeRecord($id)
   {
      $recordList = $this->GetDataObj()->DirectFetch("[Id]='$id'");
      if (count($recordList) == 1)
         return $recordList[0];
      return null;
   }
   
   /*
    * List all children records of a given record
    */
   public function ListChildren($id)
   {
      $rec = $this->getNodeRecord($id);
      $parents = $this->getAllLevelParents($rec);
      $this->m_Parents[] = $rec;
      foreach ($parents as $prt)
         $this->m_Parents[] = $prt;
      
      $this->m_DirectParentId = $rec["Id"];
      
      $this->m_SearchRule = "[PId] = '$id'";
      $this->m_ClearSearchRule = true;
      $this->m_CursorIndex = 0;
      return $this->ReRender();
   }
   
   /*
    * List all sibling records of a given record
    */
   public function ListSiblings($id)
   {
      $rec = $this->getNodeRecord($id);
      if ($rec['PId'] != null && $rec['PId'] != '')
         $this->m_Parents = $this->getAllLevelParents($rec);
      //print_r($parents);
      $pid = (count($this->m_Parents) == 0) ? '' : $this->m_Parents[0]['Id'];
      $this->m_DirectParentId = $pid;
      
      // set the search rule
      if ($pid == '')
         $this->m_SearchRule = "[PId] = '' or [PId] is NULL";
      else
         $this->m_SearchRule = "[PId] = '$pid'";
      $this->m_ClearSearchRule = true;
      $this->m_CursorIndex = 0;
      return $this->ReRender();
   }
   
   // NewRecord() - add correct pid
   public function NewRecord()
   {
      global $g_BizSystem;
      $this->SetDisplayMode(MODE_N);
      $recArr = $this->GetDataObj()->NewRecord();
      if (!$recArr) 
         return $this->ProcessDataObjError();
      // add correct pid
      $recArr['PId'] = $this->m_DirectParentId;
      $this->UpdateActiveRecord($recArr);
      return $this->ReRender();
   }
   
   // DeleteRecord() - allow delete only if no child node?
   public function DeleteRecord()
   {
      $rec = $this->GetActiveRecord();
      if (!$rec) return;
      $id = $rec['Id'];
      $recordList = $this->GetDataObj()->DirectFetch("[PId]='$id'");
      if (count($recordList) > 0) 
      {
         global $g_BizSystem;
         $errorMsg = "Unable to delete the record that has 1 or more children nodes.";
         $g_BizSystem->GetClientProxy()->ShowErrorMessage($errorMsg);
         return;
      }
      return parent::DeleteRecord();
   }

   protected function RenderHTML()
   {
      // TODO: need to consider history into the searchrule
      //echo "History:";
      //print_r($this->m_HistoryInfo);
      
      if ($this->m_DirectParentId)
         $this->SetSearchRule("[PId] = '".$this->m_DirectParentId."'");
      else {
         $root_searchRule = $this->GetParameter("Root_SearchRule");
         if (!$root_searchRule)
            $this->SetSearchRule("[PId] = '' or [PId] is NULL");
         else
            $this->SetSearchRule($root_searchRule);         
      }
      $this->m_ClearSearchRule = true;

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
         $smarty->assign_by_ref("table", $this->RenderTable());
      else if ($dispmode->m_DataFormat == "block" && $dispmode->m_FormatStyle)
         $smarty->assign_by_ref("block", $this->RenderFormattedTable());
      
      $smarty->assign_by_ref("navbar", $this->m_NavBar->Render()); 
      
      if (count($this->m_Parents) == 0)
      {
         $rec = $this->GetActiveRecord();
         if ($rec)
            $this->m_Parents = $this->getAllLevelParents($rec);
         else
         {
            $rec = $this->getNodeRecord($this->m_DirectParentId);
            $this->m_Parents = $this->getAllLevelParents($rec, true);
         }
      }
      $objname = $this->m_Name;
      $prts_txt = "";
      for ($i=count($this->m_Parents)-1; $i>=0; $i--)
      {
         $prtid = $this->m_Parents[$i]['Id'];
         $prtname = $this->m_Parents[$i]['Name'];
         if ($prts_txt == "")
            $prts_txt .= "<a href=\"javascript:CallFunction('$objname.ListSiblings($prtid))')\">$prtname</a>";
         else
            $prts_txt .= " > <a href=\"javascript:CallFunction('$objname.ListSiblings($prtid))')\">$prtname</a>";
      }
      $smarty->assign_by_ref("parents_links", $prts_txt); 
      
	   return $smarty->fetch(BizSystem::GetTplFileWithPath($dispmode->m_TemplateFile, $this->m_Package))
	          . "\n" . $this->RenderShortcutKeys()
	          . "\n" . $this->RenderContextMenu();
   }
}
?>