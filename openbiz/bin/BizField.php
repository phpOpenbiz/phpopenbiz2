<?PHP

/**
 * BizField - class BizField is the class of a logic field which mapps to a table column
 *
 * @package BizDataObj
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @version 1.2
 * @access public
 **/
class BizField extends MetaObject
{
   public $m_BizObjName;
   public $m_Join = null;
   public $m_Column = null;
   public $m_Index;
   public $m_Type = null;
   public $m_Format = null;
   public $m_ValueExpression = null;// support expression
   public $m_Required = null;       // support expression
   public $m_Validator = null;      // support expression
   public $m_SqlExpression = null;  // support expression
   public $m_DefaultValue = null;
   public $m_OnAudit = false;

   public $m_Value = null; // the real value of the field, not from metadata
   public $m_OldValue = null; // the old value of the field

   function __construct(&$xmlArr, $bizObj)
   {
      $this->m_Name = isset($xmlArr["ATTRIBUTES"]["NAME"]) ? $xmlArr["ATTRIBUTES"]["NAME"] : null;
      $this->m_BizObjName = $bizObj->m_Name;
      $this->m_Package = $bizObj->m_Package;
      $this->m_Join = isset($xmlArr["ATTRIBUTES"]["JOIN"]) ? $xmlArr["ATTRIBUTES"]["JOIN"] : null;
      $this->m_Column = isset($xmlArr["ATTRIBUTES"]["COLUMN"]) ? $xmlArr["ATTRIBUTES"]["COLUMN"] : null;
      $this->m_ValueExpression = isset($xmlArr["ATTRIBUTES"]["VALUE"]) ? $xmlArr["ATTRIBUTES"]["VALUE"] : null;
      $this->m_DefaultValue = isset($xmlArr["ATTRIBUTES"]["DEFAULTVALUE"]) ? $xmlArr["ATTRIBUTES"]["DEFAULTVALUE"] : null;
      $this->m_Type = isset($xmlArr["ATTRIBUTES"]["TYPE"]) ? $xmlArr["ATTRIBUTES"]["TYPE"] : null;
      $this->m_Format = isset($xmlArr["ATTRIBUTES"]["FORMAT"]) ? $xmlArr["ATTRIBUTES"]["FORMAT"] : null;
      $this->m_Required = isset($xmlArr["ATTRIBUTES"]["REQUIRED"]) ? $xmlArr["ATTRIBUTES"]["REQUIRED"] : null;
      $this->m_Validator = isset($xmlArr["ATTRIBUTES"]["VALIDATOR"]) ? $xmlArr["ATTRIBUTES"]["VALIDATOR"] : null;
      $this->m_SqlExpression = isset($xmlArr["ATTRIBUTES"]["SQLEXPR"]) ? $xmlArr["ATTRIBUTES"]["SQLEXPR"] : null;
      if (isset($xmlArr["ATTRIBUTES"]["ONAUDIT"]) && $xmlArr["ATTRIBUTES"]["ONAUDIT"]=='Y')
         $this->m_OnAudit = true;

      $this->m_BizObjName = $this->PrefixPackage($this->m_BizObjName);

      if (!$this->m_Format) $this->UseDefaultFormat();
   }

   protected function UseDefaultFormat()
   {
      if ($this->m_Type == "Date")  $this->m_Format = '%Y-%m-%d';
      else if ($this->m_Type == "Datetime")  $this->m_Format = '%Y-%m-%d %H:%M:%S';
   }

   public function GetProperty($propertyName)
	{
	   $ret = parent::GetProperty($propertyName);
	   if ($ret) return $ret;
	   if ($propertyName == "Value") return $this->GetValue();
	   return $this->$propertyName;
	}

	public function AdjustBizObjName($bizObjName)
	{
	   if ($this->m_BizObjName != $bizObjName)
         $this->m_BizObjName = $bizObjName;
	}

	// get string used in sql - with single quote, or without single quote in case of number
	public function GetSqlValue($input=null)
	{
	   $val = ($input!==null) ? $input : $this->m_Value;
	   //if (!$val)
	   if ($val === null)
	      return "";
	   if ($this->m_Type != 'Number')
	      return "'$val'";
	   return $val;
	}

	public function IsLobField() { return ($this->m_Type == 'Blob' || $this->m_Type == 'Clob'); }
	public function GetInsertLobValue($dbType)
	{
	   if ($dbtype == 'oracle' || $dbtype == 'oci8') {
	      if ($this->m_Type != 'Blob') return 'empty_blob()';
	      if ($this->m_Type != 'Clob') return 'empty_clob()';
	   }
	   return 'null';
	}

   /**
    * BizField::GetValue() - get the value of the field.
    *
    * @return mixed
    */
   public function GetValue($formatted=true)
   {
      // need to ensure that value are retrieved from source/cache
      //if ($this->GetDataObj()->CheckDataRetrieved() == false)
         $this->GetDataObj()->GetActiveRecord();

      $value = $this->m_Value;
      if ($this->m_ValueExpression && trim($this->m_Column)=="") {
         $value = Expression::EvaluateExpression($this->m_ValueExpression,$this->GetDataObj());
      }
	  if ($this->m_Format && $formatted) {
         global $g_BizSystem;
         $value = $g_BizSystem->GetTypeManager()->ValueToFormattedString($this->m_Type, $this->m_Format, $value);
      }
	  return $value;
   }

   /**
    * BizField::SetValue() - set the value of the field.
    *
    * @param mixed $val
    * @return void
    */
   public function SetValue($val)
   {
      $this->m_Value = $val;
   }

   public function SaveOldValue()
   {
      $this->m_OldValue = $this->m_Value;
   }

   public function GetDefaultValue()
   {
      if($this->m_DefaultValue!==null)
         return Expression::EvaluateExpression($this->m_DefaultValue, $this->GetDataObj());
      return "";
   }

   protected function GetDataObj()
   {
      global $g_BizSystem;
	   return $g_BizSystem->GetObjectFactory()->GetObject($this->m_BizObjName);
   }

   /**
    * BizField::CheckRequired() - check if the field is a required field
    *
    * @return boolean
    */
   public function CheckRequired()
   {
      if (!$this->m_Required || $this->m_Required == "")
         return false;
      else if ($this->m_Required == "Y")
         $required = true;
      else if($required != "N")
         $required = false;
      else
         $required = Expression::EvaluateExpression($this->m_Required, $this->GetDataObj());

      return $required;
   }

   /**
    * BizField::Validate() - check if the field has valid value
    *
    * @return boolean
    */
   public function Validate()
   {
      $ret = true;
      if ($this->m_Validator)
         $ret = Expression::EvaluateExpression($this->m_Validator, $this->GetDataObj());
      return $ret;
   }

}

?>
