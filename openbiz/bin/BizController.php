<?PHP

ob_start();
header('Content-Type: text/html; charset=utf-8');
include_once("sysheader.inc");

$bizCtrller = new BizController();
$bizCtrller->DispatchRequest();

/**
 * BizController class - BizController is the class that dispatches client requests to proper objects
 *
 * @package BizController
 * @author
 * @copyright Copyright (c) 2005
 * @access public
 */
class BizController
{
   private $m_UserTimeoutView = "shared.UserTimeoutView";
   private $m_AccessDeniedView = "shared.AccessDeniedView";
   /**
    * BizController::DispatchRequest() - dispatches client requests to proper objects, print the returned html text.
    *
    * @return void
    */
   public function DispatchRequest()
   {
      $profile = $this->GetUserProfile();
      if ($this->CheckSessionTimeout($profile))  // show timeout view
      {
         global $g_BizSystem;
         $g_BizSystem->GetSessionContext()->Destroy();
         return $this->RenderView($this->m_UserTimeoutView);
      }

      // ?view=...&form=...&rule=...&mode=...&...
      // ?vw=...&fm=...&rl=...&md=...&...
      $getKeys = array_keys($_GET);
      if ($getKeys[0] == "view") {
         $form = isset($_GET['form']) ? $_GET['form'] : "";
         $rule = isset($_GET['rule']) ? $_GET['rule'] : "";
         $hist = isset($_GET['hist']) ? $_GET['hist'] : "";
         $viewName = $_GET['view'];
         $params = $this->GetParameters();
         if (!$this->CheckViewAccess($viewName, $profile))  //access denied error
            return $this->RenderView($this->m_AccessDeniedView);
         return $this->RenderView($viewName,$form,$rule,$params,$hist);
      }

      $retval = $this->Invoke();

      print($retval." ");
      exit();
   }

   private function GetParameters()
   {
      $getKeys = array_keys($_GET);
      $params = null;
      // read parameters "param:name=value"
      foreach ($getKeys as $key) {
         if (substr($key, 0, 6) == "param:") {
            $paramName = substr($key, 6);
            $paramValue = $_GET[$key];
            $params[$paramName] = $paramValue;
         }
      }
      return $params;
   }

   // get user profile array. "USERID", "ROLE" ...
   private function GetUserProfile()
   {
      global $g_BizSystem;
      return $g_BizSystem->GetUserProfile();
   }
   // check if session timed out. true - session timed out, false - session alive
   private function CheckSessionTimeout($profile)
   {
      global $g_BizSystem;
      if (isset($profile["USERID"]) && $profile["USERID"] != "")
         return $g_BizSystem->GetSessionContext()->IsTimeout();
      return false;
   }
   // role-base view access control
   private function CheckViewAccess($viewName, $profile)
   {
      // load accessService
      global $g_BizSystem;
      $svcobj = $g_BizSystem->GetService("accessService");
      $role = isset($profile["ROLE"]) ? $profile["ROLE"] : null;
      return $svcobj->AllowViewAccess($viewName, $role);
   }

   /**
    * BizController::RenderView() - render a bizview
    *
    * @param string $viewName name of bizview
    * @param string $rule the search rule of a bizform who is not depent on (a subctrl of) another bizform
    * @return void
    */
   public function RenderView($viewName, $form="", $rule="", $params=null, $hist="")
   {
      //BizController::showPageLoading();
      global $g_BizSystem;

      if ($viewName == "__DynPopup")
      {
         $viewobj = $g_BizSystem->GetObjectFactory()->GetObject($viewName);
         $viewobj->Render();
         return;
      }

      // if previous view is different with the to-be-loaded view, clear the previous session objects
      $prevViewName = $g_BizSystem->GetCurrentViewName();
      $prevViewSet = $g_BizSystem->GetCurrentViewSet();

      // need to set current view before get view object
      $g_BizSystem->SetCurrentViewName($viewName);
      $viewobj = $g_BizSystem->GetObjectFactory()->GetObject($viewName);
      if(!$viewobj)
         return;
      $viewSet = $viewobj->GetViewSet();
      $g_BizSystem->SetCurrentViewSet($viewSet);

      if ($prevViewSet && $viewSet && $prevViewSet == $viewSet)   // keep prev view session objects if they have same viewset
         $g_BizSystem->GetSessionContext()->ClearSessionObjects(true);
      else
         $g_BizSystem->GetSessionContext()->ClearSessionObjects(false);

      if ($hist == "N") // clean view history
         $viewobj->CleanViewHistory();
      $viewobj->ProcessRule($form, $rule);
      $viewobj->SetParameters($params);
      if (isset($_GET['mode']))   // can specify mode of form
         $viewobj->SetFormMode($form, $_GET['mode']);
      $viewobj->Render();
      //BizController::hidePageLoading();
   }

   /**
    * BizController::Invoke() - render a bizview
    *
    * @return HTML text
    */
   protected function Invoke()
   {
      $func = (isset($_REQUEST['F']) ? $_REQUEST['F'] : "");
      $arg_list = array();
      $i = 0;
      if ($func != "") {
         eval("\$P$i = (isset(\$_REQUEST['P$i']) ? \$_REQUEST['P$i']:'');");
         $Ptmp = "P". $i;

         while ($$Ptmp!="") {
            $parm = $$Ptmp;
            $parm = substr($parm,1,strlen($parm)-2);
            $arg_list[] = $parm;
            $i++;
            eval("\$P$i = (isset(\$_REQUEST['P$i']) ? \$_REQUEST['P$i']:'');");
            $Ptmp = "P". $i;
         }
      }
      else
         return;

      $target = (isset($_REQUEST['_tgt']) ? $_REQUEST['_tgt'] : "");

      global $g_BizSystem;
      if ($func != "RPCInvoke" && $func != "Invoke") {
         trigger_error("$func is not a valid invocation", E_USER_ERROR);
         return;
      }
      if ($func == "RPCInvoke")
         $g_BizSystem->GetClientProxy()->SetRPCFlag(true);

      // todo: need to verify the form is a form in current view

      // save formdata to ClientProxy
      $formdata = (isset($_REQUEST['__FormData']) ? $_REQUEST['__FormData'] : "");
      $formdata = substr($formdata,1,strlen($formdata)-2);
      if ($formdata != "") {
         $g_BizSystem->GetClientProxy()->SetFormInputData($formdata);
      }

      // invoke the function
      $num_arg = count($arg_list);
      if ($num_arg<2) {
         $errmsg = BizSystem::GetMessage("ERROR", "SYS_ERROR_RPCARG",array($class));
         trigger_error($errmsg, E_USER_ERROR);
      }
      else
      {
         $objName = $arg_list[0];
         $methodName = $arg_list[1];
         array_shift($arg_list); array_shift($arg_list); // remove the first 2 args
         $obj= $g_BizSystem->GetObjectFactory()->GetObject($objName);

         if ($obj)
         {
            if (method_exists($obj, $methodName)) {
               $rt_val = call_user_func_array(array(&$obj, $methodName),$arg_list);
            }
            else {
               $errmsg = BizSystem::GetMessage("ERROR", "SYS_ERROR_METHODNOTFOUND",array($objName,$methodName));
               trigger_error($errmsg, E_USER_ERROR);
            }
         }
         else {
            $errmsg = BizSystem::GetMessage("ERROR", "SYS_ERROR_CLASSNOTFOUND",array($objName));
            trigger_error($errmsg, E_USER_ERROR);
         }

         if ($func == "Invoke")  // no RPC invoke, page reloaded -> rerender view
         {
            if ($g_BizSystem->GetClientProxy()->HasOtherOutput())
               $g_BizSystem->GetClientProxy()->PrintOutput();
            else {
               // check if the object is a BizForm, and not target to other window
               if ($obj instanceof BizForm && $target!="other") {
                  //$obj->GetViewObject()->ReRender();
                  $obj->GetViewObject()->ReRender();
               }
               else
                  return $rt_val;
            }
         }
         elseif ($func == "RPCInvoke")  // RPC invoke
         {
            if ($g_BizSystem->GetClientProxy()->HasOutput())
               $g_BizSystem->GetClientProxy()->PrintOutput();
            else
               return $rt_val;
         }
      }
   }

   static public function showPageLoading()
   {
       $out = "<div id='loading' style='font-family:Arial;font-size:12px;'>View is loading, plesae wait ...";
       echo str_pad($out,4090) . "</div>";
       ob_flush();
       flush();
   }

   static public function hidePageLoading()
   {
       echo "<script>document.body.removeChild(document.getElementById('loading'));</script>";
   }
}
?>