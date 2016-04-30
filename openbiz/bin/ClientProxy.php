<?PHP

/**
 * ClientProxy class - a class that is treated as the bi-direction proxy of client. Through this class,
 * others can get client form inputs, redraw client form or call client javascript functions.
 *
 * @package BizSystem
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */
class ClientProxy
{
   protected $m_RequestArgs;
   protected $m_FormInputArray = null;
   protected $m_bRPCFlag = false;

   private $m_FormsOutput = array();   // key-value array
   private $m_OtherOutput = array();   // index array

   private $m_ExtraScripts = array();

   public function HasFormRerendered($formName)
   {
      return key_exists($formName, $this->m_FormsOutput);
   }

   public function GetFormOutput($formName)
   {
      return $this->m_FormsOutput[$formName];
   }

   public function HasOtherOutput() { return count($this->m_OtherOutput)>0; }
   public function HasOutput() { return count($this->m_OtherOutput)+count($this->m_FormsOutput)>0; }

   public function PrintOutput()
   {
      // check if other output array has content
      if (count($this->m_OtherOutput)>0) {
         foreach ($this->m_OtherOutput as $output)
            echo $output;
         return;
      }

      // print forms output
      foreach ($this->m_FormsOutput as $output)
         echo $output;
   }

   public function SetRPCFlag($flag)
   {
      $this->m_bRPCFlag = $flag;
   }

   /**
    * ClientProxy::GetRequestParam() - get the client form data passed by GET or POST
    *
    * @param string $name
    * @return string
    */
   public function GetRequestParam($name)
   {
      $val = (isset($_REQUEST[$name]) ? $_REQUEST[$name] : "");
      return $val;
   }

   /**
    * ClientProxy::SetFormInputData() - called by BizController to parse and save the client form data
    *
    * @param string $formdata
    * @return void
    */
   public function SetFormInputData($formdata)
   {
      /*$input_array = explode("^-^-^", $formdata);
      foreach($input_array as $kvpair) {
         $pos = strpos($kvpair, "=");
         $field = substr($kvpair, 0, $pos);
         $value = substr($kvpair, $pos+1, strlen($kvpair)-$pos);
         if ($field) {
            if(!get_magic_quotes_gpc())
               $value = addslashes($value);
            $this->m_FormInputArray[$field] = $value;
         }
      }*/
   }

   /**
    * ClientProxy::GetFormInputs() - get form all inputs or one input if ctrlName is given
    *
    * @param string $ctrlName
    * @param boolean $toString - Convert array oriented form controls to string
    * @return array or string
    */
   public function GetFormInputs($ctrlName=null, $toString=TRUE)
   {
      if ($ctrlName) {
            if (isset($_REQUEST[$ctrlName])) {
               if (is_array($_REQUEST[$ctrlName]) AND $toString==TRUE) {
                  $array_string='';
                  foreach ($_REQUEST[$ctrlName] as $rec) {
                     $array_string .= $rec . ",";
                  }
                  $result = substr($array_string, 0, strlen($array_string) - 1);
               } else {
                  $result = $_REQUEST[$ctrlName];
               }
               if (get_magic_quotes_gpc() == 0)
                  addslashes($result);
               return $result;
            } else {
               return null;
            }
      }
      else {
         return $_REQUEST;
      }
   }

   /**
    * ClientProxy::UpdateFormElements() - update the form controls on the client UI
    *
    * @param string $formName - name of the html form on client
    * @param array $recArr - name/value pairs
    * @return array or string
    */
   public function UpdateFormElements($formName, &$recArr)
   {
      if ($this->m_bRPCFlag) {
         $rtString = "UPD_FLDS";
         foreach($recArr as $fld=>$val)
            $rtString .= "[".$fld."]=<".$val.">";
         $this->m_OtherOutput[] = $this->_buildTgtCtnt($formName, $rtString);
      }
   }
   /**
    * ClientProxy::ReDrawForm() - replace the form content with the provided html text
    *
    * @param string $formName - name of the html form on client
    * @param string $sHTML - html text to redraw the form
    * @return string - encoded html string returns to browser, it'll be processed by client javascript.
    */
   public function ReDrawForm($formName, &$sHTML)
   {
      if ($this->m_bRPCFlag)
         $this->m_FormsOutput[$formName] = $this->_buildTgtCtnt($formName, $sHTML);
      else
         $this->m_FormsOutput[$formName] = $sHTML;
   }
   /**
    * ClientProxy::ShowClientAlert() - popup an alert window on the browser
    *
    * @param string $alertText
    * @return string - encoded html string returns to browser, it'll be processed by client javascript.
    */
   public function ShowClientAlert($alertText)
   {
      $msg = addslashes($alertText);
      if ($this->m_bRPCFlag)
         $this->m_OtherOutput[] =  $this->CallClientFunction("alert('".$msg."')");
   }

   public function ShowErrorMessage($errMsg)
   {
      $msg = addslashes($errMsg);
      if ($this->m_bRPCFlag) {
         $this->m_OtherOutput[] = $this->CallClientFunction("alert('$msg')");
      }
      else {
         $this->ErrorOutput($errMsg);
      }
   }

   public function ShowPopup($baseForm, $popupForm, $ctrlName="")
   {
      if ($this->m_bRPCFlag) {
         $function = $baseForm . ".ShowPopup(" . $popupForm . "," . $ctrlName . ")";
         $this->m_OtherOutput[] =  $this->CallClientFunction("CallFunction('$function','Popup')");
      }
   }

   public function RunClientScript($script, $type='Form')
   {
      if ($this->m_bRPCFlag) {
         if ($type == 'Form') {
            $this->m_FormsOutput[] = $this->CallClientFunction($script);
         } elseif ($type = 'Other'){
            $this->m_OtherOutput[] = $this->CallClientFunction($script);
         }

      }
      else {
         echo $script;
      }
   }


   private function ErrorOutput($errMsg)
   {
      ob_clean();
      echo "<body style='font-family:Arial; font-size:12px; background-color:#FCFCFC;'>";
      echo "Error message: <font color=maroon>$errMsg</font><br>";
      echo "<input type='button' NAME='btn_back' VALUE='Go back' onClick='history.go(-1);return true;'>";
      echo "</body>";
      exit;
   }

   public function ShowErrorPopup($errMsg)
   {
      //$msg = str_replace("\\", "\\\\", $errMsg);
      //$msg = str_replace("'", "\'", $msg);
      $msg = addslashes($errMsg);

      if ($this->m_bRPCFlag) {
         $this->m_OtherOutput[] = $this->CallClientFunction("popupErrorText('$msg')");
         $this->PrintOutput();
      }
      else {
         echo $errMsg;
         exit;
         //BizSystem::ErrorBacktrace();
      }
   }

   public function ClosePopup()
   {
      if ($this->m_bRPCFlag)
         $this->m_FormsOutput[] = $this->CallClientFunction("close()");
   }

   public function ShowPopupWindow($content, $w, $h)
   {
      if ($this->m_bRPCFlag)
         $this->m_FormsOutput[] = $this->CallClientFunction("popupWindow(\"$content\", $w, $h)");
   }

   private function CallClientFunction($funcStr)
   {
      if ($this->m_bRPCFlag)
         return $this->_buildTgtCtnt("FUNCTION", $funcStr);
   }

   /**
    * BuildTgtCtnt()
    * build target-content string with target str and content string as inputs. After RPC call, this function is usually called to
    * set the HTML text to an UI element.
    *
    * @param string $tgt the HTML element id, i.e. the applet HTML id
    * @param string $ctnt the HTML text to be set as the content of the HTML element referred with the id
    * @return string
    **/
   private function _buildTgtCtnt($tgt, &$ctnt)
   {
      //$tmpStr = "### TARGET:".strlen($tgt).":".$tgt.";";
      //$tmpStr .= "CONTENT:".strlen($ctnt).":".$ctnt;

      $tmpStr = "<___TARGET___>".$tgt."</___TARGET___>";
      $tmpStr .= "<___CONTENT___>".$ctnt."</___CONTENT___>";

      return $tmpStr;
   }

   /**
    * ClientProxy::RedirectPage() - redirect page to the given url
    *
    * @param string $pageURL
    * @return string - encoded html string returns to browser, it'll be processed by client javascript.
    */
   public function RedirectPage($pageURL)
   {
      $this->m_OtherOutput[] = $this->CallClientFunction("RedirectPage('$pageURL')");
   }
   /**
    * ClientProxy::RedirectView() - redirect page to the given view
    *
    * @param string $view
    * @return string - encoded html string returns to browser, it'll be processed by client javascript.
    */
   public function RedirectView($view)
   {
      $this->m_OtherOutput[] = $this->CallClientFunction("GoToView('$view','')");
   }

   // append more scripts - js include, js code, css include, css section
   public function AppendScripts($scriptKey, $scripts, $isFile=true)
   {
      // if has the script key already, ignore
      if (isset($this->m_ExtraScripts[$scriptKey]))
         return;
      // add the scripts
      if ($isFile)
      {
         $_scripts = "<script type='text/javascript' src='../js/$scripts'></script>";
         $this->m_ExtraScripts[$scriptKey] = $_scripts;
      }
      else
         $this->m_ExtraScripts[$scriptKey] = $scripts;
   }

   public function GetAppendedScripts()
   {
      return $this->m_ExtraScripts;
   }

   public function IncludeCalendarScripts()
   {
      if (isset($this->m_ExtraScripts['calendar']))
         return;
      $script = "<style type='text/css'>@import url(../js/jscalendar/calendar-system.css);</style>\n";
   	$script .= "<script type='text/javascript' src='../js/jscalendar/calendar.js'></script>\n";
   	$script .= "<script type='text/javascript' src='../js/jscalendar/lang/calendar-en.js'></script>\n";
   	$script .= "<script type='text/javascript' src='../js/jscalendar/calendar-setup.js'></script>\n";
   	$script .= "<script type='text/javascript' src='../js/calendar.js'></script>";
   	$this->AppendScripts("calendar", $script, false);
   }

   public function IncludeRTEScripts()
   {
      if (isset($this->m_ExtraScripts['rte']))
         return;
      $script = "<script type='text/javascript' src='../js/richtext.js'></script>\n";
   	$script .= "<script language=\"JavaScript\">initRTE('../images/rte/', '../pages/rte/', '', false);</script>";
   	$this->AppendScripts("rte", $script, false);
   }
}

?>