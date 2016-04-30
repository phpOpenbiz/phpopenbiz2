<?php
/**
 * @package PluginService
 */
/**
 * emailService - 
 * class emailService is the plug-in service of sending emails 
 * 
 * @package PluginService
 * @author rocky swen 
 * @copyright Copyright (c) 2003
 * @version $Id: emailService.php,v 1.1 2006/04/07 08:01:29 rockys Exp $
 * @access public 
 */
class emailService extends MetaObject 
{
   public $m_Accounts = null;
   protected $m_UseAccount;
   protected $m_ErrorMsg;
   
   function __construct(&$xmlArr)
   {
      $this->ReadMetadata($xmlArr);
   }
   
   protected function ReadMetadata(&$xmlArr)
   {
      parent::ReadMetaData($xmlArr);
      $this->m_Accounts = new MetaIterator($xmlArr["PLUGINSERVICE"]["ACCOUNTS"]["ACCOUNT"],"EmailAccount");
   }
   
   public function UseAccount($accountName)
   {
      $this->m_UseAccount = $accountName;
   }

   public function SendEmail($TOs=null, $CCs=null, $BCCs=null, $subject, $body, $attachments=null, $isHTML=false)
   {
      $acct = $this->m_Accounts->get($this->m_UseAccount);
      if (!$acct)
         return;
         
      include_once(PHPMAILER_DIR."/class.phpmailer.php");
      
      $mail = new PHPMailer();
      
      if ($acct->m_IsSMTP == "Y")
         $mail->IsSMTP();  // send via SMTP
      $mail->Host     = $acct->m_Host; // SMTP servers
      $mail->SMTPAuth = $acct->m_SMTPAuth == "Y" ? true : false;  // turn on SMTP authentication
      $mail->Username = $acct->m_Username;  // SMTP username
      $mail->Password = $acct->m_Password; // SMTP password
      $mail->From     = $acct->m_FromEmail;
      $mail->FromName = $acct->m_FromName;
      //$mail->AddReplyTo("info@site.com","Information");
      
      // add TO addresses
      if ($TOs)
         foreach ($TOs as $to)
            $mail->AddAddress($to);
            //$mail->AddAddress($to["address"], $to["name"]);
      // add CC addresses
      if ($CCs)
         foreach ($CCs as $cc)
            $mail->AddCC($cc);
            //$mail->AddCC($cc["address"], $cc["name"]);
      // add BCC addresses
      if ($BCCs)
         foreach ($BCCs as $bcc)
            $mail->AddBCC($bcc);
            //$mail->AddBCC($bcc["address"], $bcc["name"]);
      
      // add attachments
      if ($attachments)
         foreach ($attachments as $att)
            $mail->AddAttachment($att);
            //$mail->AddAttachment($att["path"], $att["name"]); 
      
      $mail->IsHTML($isHTML);
      
      $mail->Subject  = $subject;
      $body = str_replace("\\n", "\n", $body);
      $mail->Body     = $body;   // body textshold be loaded from some template file
      //$mail->AltBody  = "This is the text-only body";
      
      if(!$mail->Send())
      {
         $this->m_ErrorMsg = "Mailer Error: " . $mail->ErrorInfo;
         return false;
         //throw new BSVException("Mailer Error: " . $mail->ErrorInfo);
         //echo "Message was not sent <p>";
         //echo "Mailer Error: " . $mail->ErrorInfo;
         //exit;
      }
      return true;
   }
   
   public function GetErrorMsg() { return $this->m_ErrorMsg; }
}

class EmailAccount extends MetaObject 
{
   public $m_Name;
   public $m_Host;
   public $m_FromName;
   public $m_FromEmail;
   public $m_IsSMTP;
   public $m_SMTPAuth;
   public $m_Username;
   public $m_Password;
   
   public function __construct($xmlArr)
   {
      $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
      $this->m_Host = $xmlArr["ATTRIBUTES"]["HOST"];
      $this->m_FromName = $xmlArr["ATTRIBUTES"]["FROMNAME"];
      $this->m_FromEmail = $xmlArr["ATTRIBUTES"]["FROMEMAIL"];
      $this->m_IsSMTP = $xmlArr["ATTRIBUTES"]["ISSMTP"];
      $this->m_SMTPAuth = $xmlArr["ATTRIBUTES"]["SMTPAUTH"];
      $this->m_Username = $xmlArr["ATTRIBUTES"]["USERNAME"];
      $this->m_Password = $xmlArr["ATTRIBUTES"]["PASSWORD"];
   }
}

?>