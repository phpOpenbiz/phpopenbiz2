<?php

/* configuration
<PluginService ...>
   <access-constraint>
     <view-collection>
       <view name="view1">
         <role name="admin"/>
         <role name="member"/>
       </view>
       <view name="reg_expr"/>
     </view-collection>
   </access-constraint>
</PluginService ...>
*/

// role based view access service
class accessService
{
   private $m_ConfigFile = "accessService.xml";
   private $m_RestrictedViewList; 
   
   function __construct(&$xmlArr)
   {
      $this->ReadMetadata($xmlArr);
   }
   
   protected function ReadMetadata(&$xmlArr)
   {
      $viewCollection = $xmlArr["PLUGINSERVICE"]["ACCESS-CONSTRAINT"]["VIEW-COLLECTION"];
      $this->m_RestrictedViewList = new MetaIterator($xmlArr["PLUGINSERVICE"]["ACCESS-CONSTRAINT"]["VIEW-COLLECTION"]["VIEW"],"RestrictedView");
   }
   
   public function AllowViewAccess($viewName, $role=null)
   {
      if (!$role)
         $role = "";
      $view = $this->GetMatchView($viewName);
      if (!$view)
         return true;
      
      $roleList = $view->GetRoleList();
      if (!$roleList)
         return true;
      if ($roleList->get($role))
         return true;
      
      return false;
   }
   
   protected function GetMatchView($viewName)
   {
      $viewobj = $this->m_RestrictedViewList->get($viewName);
      if ($viewobj)
         return $viewobj;
      foreach ($this->m_RestrictedViewList as $view => $viewobj) {
         $preg_view = "/".$view."/";
         if (preg_match($preg_view, $viewName)) {
            return $viewobj;
         }
      }
      return null;
   }
}

class RestrictedView
{
   public $m_Name;
   private $m_RoleList;
   
   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_RoleList = new MetaIterator($xmlArr["ROLE"],"RestrictedRole");
   }
   public function GetViewName() { return $this->m_Name; }
   public function GetRoleList() { return $this->m_RoleList; }
}

class RestrictedRole
{
   public $m_Name;
   
   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
   }
   public function GetRoleName() { return $this->m_Name; }
}
?>