<?php
/**
 * @package PluginService
 */
/**
 * validateService -
 * class validateService is the plug-in service of handling system validation.
 * The service depends on the Zend Framework validator component.
 * REGIX service provided by preg_match ie. perl style
 *
 * @package PluginService
 * @author jim jenkins
 * @copyright Copyright (c) 2003
 * @version $Id: validateService.php,v .1 2007/12/14 jim
 * @access public
 */
class validateService
{
   public function __construct() {}


   /**
    * Function to check if a value is shorter than the $max length
    *
    * @param string $value
    * @param integer $max
    * @return boolean Valid?
    */
   public function shorterThan($value, $max) {
      $result = false;
      if (strlen($value) < $max)
         $result = true;
      return $result;
   }

   /**
    * Function to check if a value is between a $min and $max length
    *
    * @param string $value
    * @param integer $max
    * @param integer $min
    * @return boolean Valid?
    */
   public function betweenLength($value, $max, $min) {
      $result = false;
      if (strlen($value) < $max && strlen($value) > $min)
         $result = true;
      return $result;
   }

   /**
    * Function to check if a value is longer than the $min length
    *
    * @param string $value
    * @param integer $min
    * @return boolean Valid?
    */
   public function longerThan($value, $min) {
      $result = false;
      if (strlen($value) > $min)
         $result = true;
      return $result;
   }



   /**
    * Built-in Zend less than check.  Returns true if and only if $value is less than the minimum boundary.
    *
    * @param integer $value
    * @param integer $max
    * @return boolean Valid?
    */
   public function lessThan($value, $max) {
      require_once 'Zend/Validate/LessThan.php';
      $validator = new Zend_Validate_LessThan($max);
      $result = $validator->isValid($value);
      return $result;
   }

   /**
    * Strong Password checks if the string is "strong", i.e. the password must be at least 8 characters
    * and must contain at least one lower case letter, one upper case letter and one digit
    *
    * @param mixed $value
    * @return boolean Valid?
    */
   public function strongPassword($value) {
      require_once 'Zend/Validate/Regex.php';
      $validator = new Zend_Validate_Regex("/^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$/");
      $result = $validator->isValid($value);
      return $result;
   }



   /**
    * Built-in Zend greater than check.  Returns true if and only if $value is greater than the minimum boundary.
    *
    * @param integer $value
    * @param integer $min
    * @return boolean Valid?
    */
   public function greaterThan($value, $min) {
      require_once 'Zend/Validate/GreaterThan.php';
      $validator = new Zend_Validate_GreaterThan($min);
      $result = $validator->isValid($value);
      return $result;
   }

   /**
    * Built-in Zend between check.  Returns true if and only if $value is between the minimum and maximum boundary values.
    *
    * @param integer $value
    * @param integer $min
    * @param mixed $max
    * @param boolean $inclusive
    *
    * @return boolean Valid?
    */
   public function between($value, $min, $max, $inclusive=true) {
      require_once 'Zend/Validate/Between.php';
      $validator = new Zend_Validate_Between($min, $max, $inclusive);
      $result = $validator->isValid($value);
      return $result;
   }


   /**
    * Built-in Zend email check
    *
    * @param string $email
    * @return boolean Valid?
    */
   public function email($email) {
      require_once 'Zend/Validate/EmailAddress.php';
      
      $validator = new Zend_Validate_EmailAddress();
      $result = $validator->isValid($email);
      return $result;
   }
/**
 * Built-in Zend date check using YYYY-MM-DD
 *
 * @param string in date format $date
 * @return boolean Valid?
 */
   public function date($date) {
      require_once 'Zend/Validate/Date.php';
      $validator = new Zend_Validate_Date();
      $result = $validator->isValid($date);
      return $result;
   }
/**
 * Phone check for US format ###-###-#### or (###)###-####
 *
 * @param string $phone
 * @return boolean Valid?
 */
   public function phone($phone) {
      require_once 'Zend/Validate/Regex.php';
      $validator = new Zend_Validate_Regex("/^[0-9]{3}-[0-9]{3}-[0-9]{4}/");
      $result = $validator->isValid($phone);
      return $result;
   }
/**
 * US Zip check in ##### or #####-####
 *
 * @param string $zip
 * @return boolean Valid?
 */
   public function zip($zip) {
      require_once 'Zend/Validate/Regex.php';
      $validator = new Zend_Validate_Regex("/^[0-9]{5,5}([- ]?[0-9]{4,4})?$/");
      $result = $validator->isValid($zip);
      return $result;
   }
/**
 * Social Security check for US format ###-###-#### or (###)###-####
 *
 * @param string $social
 * @return boolean Valid?
 */
   public function social($social) {
      require_once 'Zend/Validate/Regex.php';
      $validator = new Zend_Validate_Regex("\b[0-9]{3}-[0-9]{2}-[0-9]{4}\b");
      $result = $validator->isValid($social);
      return $result;
   }

/**
 * Credit Card check for US format VISA/AMEX/DISC/MC
 *
 * @param string $credit
 * @return boolean Valid?
 */
   public function credit($credit) {
      require_once 'Zend/Validate/Regex.php';
      $validator = new Zend_Validate_Regex("^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|");
      $result = $validator->isValid($credit);
      return $result;
   }

/**
 * Street Address check for US format #### Memory Lane
 *
 * @param string $street
 * @return boolean Valid?
 */
   public function street($street) {
      require_once 'Zend/Validate/Regex.php';
      $validator = new Zend_Validate_Regex("");
      $result = $validator->isValid($street);
      return $result;
   }


   public function GetErrorMessage($validator, $fieldName) {
      $validator = str_replace('{@validate:', '', $validator);
      $pos1 = strpos($validator, '(');
      $type = substr($validator, 0, $pos1);

      switch ($type) {
      case "date":
         return 'Please enter a valid date. YYYY-MM-DD';
         break;
      case "email":
         return 'Please enter a valid email address. abc@domain.tld';
         break;
      case "phone":
         return 'Please enter a valid telephone number. ###-###-####';
         break;
      case "zip":
         return 'Please enter a valid zip code. ##### or #####-####';
         break;
      case "social":
         return 'Please enter a valid US social security number. ###-##-####';
         break;
      case "credit":
         return 'Please enter a valid card number.';
         break;
      case "street":
         return 'Please enter a valid US street address #### Memory Lane';
         break;
      case "strongPassword":
         return 'Please enter a password containing atleast 8 characters using both upper and lower cases characters as well as atleast one digit.';
         break;
      }
      return 'Please enter a valid '.$fieldName;
   }

}

?>