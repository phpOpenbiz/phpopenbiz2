<?php
class FMAuth extends BizForm 
{ 
   protected $userid;
   protected $password;
   private $m_hasError = false;
   
   public function Login()
   {
      // get the user id and password
      global $g_BizSystem;
      $this->userid = $g_BizSystem->GetClientProxy()->GetFormInputs("userid");
      $this->password = $g_BizSystem->GetClientProxy()->GetFormInputs("password");
      
      // login must be between 4 and 15 chars containing alphanumeric chars only:
   	if (($error = $this->field_validator("login name", $this->userid, "alphanumeric", 4, 15)) !== true) {
   	   return $this->ReRenderWithError($error);
   	}
   
   	// password must be between 4 and 15 chars - any characters can be used:
   	if (($error = $this->field_validator("password", $this->password, "string", 4, 15)) !== true) {
   		return $this->ReRenderWithError($error);
   	}
   	
   	$svcobj = $g_BizSystem->GetService("authService");
   	if ($svcobj->AuthenticateUser($this->userid,$this->password)) {
   	   $profile = $g_BizSystem->InitUserProfile($this->userid);
   	   return $this->ReRender();
   	}
   	else 
         $error = "Invalid userid and password, please try again.";
   	     
      /*
      if ($this->CheckPassword($this->userid,$this->password)) {
         // save $userid into session context
         $g_BizSystem->GetSessionContext()->SetVar("UserId",$userid);
         //return $g_BizSystem->GetClientProxy()->RedirectPage("../EventMgr.htm");
         return $this->ReRender();
      }
      else 
         $error = "Invalid userid and password, please try again.";
      */
         
      return $this->ReRenderWithError($error);
   }
   
   protected function ReRenderWithError($error)
   {
      $this->m_hasError = true;
      $recArr["userid"] = $this->userid;
      $recArr["password"] = $this->password;
      $this->UpdateActiveRecord($recArr);
      
      $txtErr_Ctrl = $this->GetControl("txt_err");
      if ($txtErr_Ctrl)
         $txtErr_Ctrl->m_Caption = $error;
      return $this->ReRender();
   }
   
   protected function GetPostAction()
   {
      if ($this->m_hasError)
         return null;
      return parent::GetPostAction();
   }
   /*
   protected function CheckPassword($userid, $password)
   {
      // todo: BizSystem->Authenicate() who calls authService method
      global $g_BizSystem;
      $boAuth = $g_BizSystem->GetObjectFactory()->GetObject("shared.BOAuth");
      if (!$boAuth)
         return false;
      $boAuth->ClearSearchRule();
      $boAuth->SetSearchRule("[User Id]='$userid' AND [Password]='$password'");
      $boAuth->RunSearch(-1);
      $recArray = $boAuth->GetRecord();
      if (!$recArray)
         return false;
      if ($recArray["Password"]==$password)
         return true;
      return false;
   }
   */
   
   # function validates HTML form field data passed to it:
   protected function field_validator($field_descr, $field_data, $field_type, $min_length="", $max_length="", $field_required=1) 
   {
   	/*
   	Field validator:
   	This is a handy function for validating the data passed to
   	us from a user's <form> fields.
   
   	Using this function we can check a certain type of data was
   	passed to us (email, digit, number, etc) and that the data
   	was of a certain length.
   	*/
   	
   	# first, if no data and field is not required, just return now:
   	if(!$field_data && !$field_required)
   	{ return true; }
   
   	# initialize a flag variable - used to flag whether data is valid or not
   	$field_ok=false;
   
   	# this is the regexp for email validation:
   	$email_regexp="^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|";
   	$email_regexp.="(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$";
   
   	# a hash array of "types of data" pointing to "regexps" used to validate the data:
   	$data_types=array(
   		"email"=>$email_regexp,
   		"digit"=>"^[0-9]$",
   		"number"=>"^[0-9]+$",
   		"alpha"=>"^[a-zA-Z]+$",
   		"alpha_space"=>"^[a-zA-Z ]+$",
   		"alphanumeric"=>"^[a-zA-Z0-9]+$",
   		"alphanumeric_space"=>"^[a-zA-Z0-9 ]+$",
   		"string"=>""
   	);
   	
   	# check for required fields
   	if ($field_required && empty($field_data)) {
   		return "$field_descr is a required field.";
   	}
   	
   	# if field type is a string, no need to check regexp:
   	if ($field_type == "string") {
   		$field_ok = true;
   	} else {
   		# Check the field data against the regexp pattern:
   		$field_ok = ereg($data_types[$field_type], $field_data);		
   	}
   	
   	# if field data is bad, add message:
   	if (!$field_ok) {
   		return "Please enter a valid $field_descr.";
   	}
   	
   	# field data min length checking:
   	if ($field_ok && $min_length) {
   		if (strlen($field_data) < $min_length) {
   			return "$field_descr is invalid, it should be at least $min_length character(s).";
   		}
   	}
   	
   	# field data max length checking:
   	if ($field_ok && $max_length) {
   		if (strlen($field_data) > $max_length) {
   			return "$field_descr is invalid, it should be less than $max_length characters.";
   		}
   	}
   	return true;
   }
}

?>