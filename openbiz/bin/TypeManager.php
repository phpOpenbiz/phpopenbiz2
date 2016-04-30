<?PHP
/**
 * TypeManager class - Type management class that has help methods to format data to UI and unformat UI input to data.
 *
 * @package BizSystem
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */
class TypeManager
{
   protected $m_LocaleInfo;
   /**
    * TypeManager::__construct - Constructor of TypeManager, set locale with localcode parameter
    *
    * @param string $localeCode
    * @return void
    **/
   public function __construct($localeCode="")
   {
      setlocale(LC_ALL, $localeCode);
      $this->m_LocaleInfo = localeconv();
      if ($this->m_LocaleInfo['frac_digits']>10)
         $this->m_LocaleInfo = null;
   }
   /**
    * TypeManager::FormattedStringToValue - Convert Formatted String To Value
    *
    * @param string $type - field type
    * @param string $fmt - type format
    * @param string $fmtString - formatted string
    * @return mix value
    **/
   public function FormattedStringToValue($type, $fmt, $fmtString)
   {
      if ($fmtString===null || $fmtString==="")    return null;
      else if ($type=="Number")                    return $this->NumberToValue($fmt, $fmtString);
      else if ($type=="Text")                      return $this->TextToValue($fmt, $fmtString);
      else if ($type=="Date")                      return $this->DateToValue($fmt, $fmtString);
      else if ($type=="Datetime")                  return $this->DatetimeToValue($fmt, $fmtString);
      else if ($type=="Currency")                  return $this->CurrencyToValue($fmt, $fmtString);
      else if ($type=="Phone")                     return $this->PhoneToValue($fmt, $fmtString);
      else                                         return $fmtString;
   }
   /**
    * TypeManager::ValueToFormattedString - Convert Value To Formatted String
    *
    * @param string $type - field type
    * @param string $fmt - type format
    * @param string $value - value
    * @return string formatted string
    **/
   public function ValueToFormattedString($type, $fmt, $value)
   {
      if ($type=="Number")       return $this->ValueToNumber($fmt, $value);
      else if ($type=="Text")    return $this->ValueToText($fmt, $value);
      else if ($type=="Date")    return $this->ValueToDate($fmt, $value);
      else if ($type=="Datetime")return $this->ValueToDatetime($fmt, $value);
      else if ($type=="Currency")return $this->ValueToCurrency($fmt, $value);
      else if ($type=="Phone")   return $this->ValueToPhone($fmt, $value);
      else return $value;
   }

   protected function ValueToNumber($fmt, $value)
   {
      if ($fmt[0]=="%")
         return sprintf($fmt, $value);
      if (!$this->m_LocaleInfo)
         return $value;
      $fmt_num = $value;
      if ($fmt=="Int")
         $fmt_num = number_format($value, 0, $this->m_LocaleInfo['decimal_point'], $this->m_LocaleInfo['thousands_sep']);
      else if ($fmt=="Float")
         $fmt_num = number_format($value,
            $this->m_LocaleInfo['frac_digits'],
            $this->m_LocaleInfo['decimal_point'],
            $this->m_LocaleInfo['thousands_sep']);
      return $fmt_num;
   }
   protected function NumberToValue($fmt, $fmt_value)
   {
      if ($fmt_value === false || $fmt_value === true)
         return null;

      if ($fmt[0]=="%")
         return sscanf($fmt_value, $fmt);
      if (!$this->m_LocaleInfo)
         return $fmt_value;
      $tmp = str_replace($this->m_LocaleInfo['thousands_sep'],null,$fmt_value);
      $tmp = str_replace($this->m_LocaleInfo['decimal_point'],".",$tmp);
      if ($fmt=="Int")
         return (int)$tmp;
      return $tmp;
   }

   protected function ValueToText($fmt, $value)
   {
      return $value;
   }
   protected function TextToValue($fmt, $fmt_value)
   {
      if ($fmt_value === false)
         $fmt_value = "";

      return $fmt_value;
   }

   protected function ValueToDate($fmt, $value)
   {
      // ISO format YYYY-MM-DD as input
      if ($value == "0000-00-00") return "";
      if (!$value)   return "";
      if (strlen(trim($value))<1) return "";
      $tt = strtotime($value);
	   if ($tt != -1)
         return strftime($fmt, strtotime($value));
      return "";
   }
   // return ISO format YYYY-MM-DD
   protected function DateToValue($fmt, $fmt_value)
   {
      if (!$fmt_value) return '';
      $std_format = $this->ConvertDatetimeFormat($fmt_value, $fmt, '%Y-%m-%d');
      return $std_format;
   }

   protected function ValueToDatetime($fmt, $value)
   {
      // ISO format YYYY-MM-DD HH:MM:SS as input
      if ($value == "0000-00-00 00:00:00") return "";
      if($fmt == null) $fmt = DATETIME_FORMAT;
	  return $this->ValueToDate($fmt, $value);
   }
   // return ISO format YYYY-MM-DD HH:MM:SS
   protected function DatetimeToValue($fmt, $fmt_value)
   {
      if (!$fmt_value) return '';
      $std_format = $this->ConvertDatetimeFormat($fmt_value, $fmt, '%Y-%m-%d %H:%M:%S');
      return $std_format;
   }

   protected function ValueToCurrency($fmt, $value)
   {
      if (!$value)   return "";
      if (!$this->m_LocaleInfo)
         return $value;
      $fmt_num = number_format($value,
         $this->m_LocaleInfo['frac_digits'],
         $this->m_LocaleInfo['mon_decimal_point'],
         $this->m_LocaleInfo['mon_thousands_sep']);
      return $this->m_LocaleInfo["currency_symbol"].$fmt_num;
   }
   protected function CurrencyToValue($fmt, $fmt_value)
   {
      if (!$this->m_LocaleInfo)
         return $fmt_value;
      $tmp = str_replace($this->m_LocaleInfo["currency_symbol"],null,$fmt_value);
      $tmp = str_replace($this->m_LocaleInfo['thousands_sep'],null,$tmp);
      return (float)$tmp;
   }

   protected function ValueToPhone($mask, $value)
   {
      if (substr($value,0,1) == "*")   // if phone starts with "*", it's an international number, don't convert it
         return $value;
      if (trim($value)=="")
         return $value;
      $mask_len = strlen($mask);
      $ph_len = strlen($value);
      $ph_fmt = $mask;
      $j=0;
      for($i=0; $i<$mask_len; $i++) {
         if ($mask[$i] == "#") {
            $ph_fmt[$i] = $value[$j];
            $j++;
            if ($j==$ph_len) break;
         }
      }
      return substr($ph_fmt,0,$i+1);
   }
   protected function PhoneToValue($mask, $fmt_value)
   {
      if ($fmt_value[0] == "*")
         return $fmt_value;
      return ereg_replace("[^0-9]",null,$fmt_value);
   }

   public function ConvertDatetimeFormat($fmt_value, $fmt1, $fmt2)
   {
      if ($fmt1 == $fmt2)
         return $fmt_value;
      $timestamp = $this->parseDate($fmt1, $fmt_value);
      return strftime($fmt2, $timestamp);
   }

   private function parseDate($fmt, $fmt_value)
   {
      $y = 0; $m = 0; $d = 0;
   	$hr = 0;	$min = 0; $sec = 0;
   	$a = preg_split("/\W+/", $fmt_value);
   	preg_match_all("/%./", $fmt, $b);
   	for ($i = 0; $i < count($a); ++$i) {
   		if (!$a[$i])
   			continue;
   		switch ($b[0][$i]) {
   		    case "%d":  // the day of the month ( 00 .. 31 )
   		    case "%e":  // the day of the month ( 0 .. 31 )
      			$d = intval($a[$i], 10);
      			break;
   		    case "%m":  // month ( 01 .. 12 )
      			$m = intval($a[$i], 10);
      			break;
   		    case "%Y":  // year including the century ( ex. 1979 )
   		    case "%y":  // year without the century ( 00 .. 99 )
      			$y = intval($a[$i], 10);
      			if ($y < 100)
      			   $y += ($y > 29) ? 1900 : 2000;
      			break;
   		    case "%H":  // hour ( 00 .. 23 )
   		    case "%I":  // hour ( 01 .. 12 )
   		    case "%k":  // hour ( 00 .. 23 )
   		    case "%l":  // hour ( 01 .. 12 )
      			$hr = intval($a[$i], 10);
      			break;
   		    case "%P":  // PM or AM
   		    case "%p":  // pm or am
      			if ($a[$i]=='pm' && $hr < 12)
      				$hr += 12;
      			else if ($a[$i]=='am' && $hr >= 12)
      				$hr -= 12;
      			break;
   		    case "%M":  // minute ( 00 .. 59 )
      			$min = intval($a[$i], 10);
      			break;
      	    case "%S":  // second ( 00 .. 59 )
      	      $sec = intval($a[$i], 10);
      	      break;
      	    //default:
   		}
   	}
		$timestamp = mktime($hr, $min, $sec, $m, $d, $y);
		return $timestamp;
   }
}
?>
