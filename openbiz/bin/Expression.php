<?PHP
/**
 * Expression - class Expression is the base class of evaluating simple expression
 * 
 * @package BizView
 * @author rocky swen 
 * @copyright Copyright (c) 2005
 * @version 1.2
 * @access public 
 */
class Expression 
{
   function __construct(&$xmlArr)
   {
   }
   
   protected static function ReplaceFieldsExpr($expression, $bizobj)
   {
      $script = "";
      $start = 0;
      
      // replace [field] with field value
      while (true) {
         $pos0 = strpos($expression, "[", $start);
         $pos1 = strpos($expression, "]", $start);
         if ($pos0 === false) {
            $script .= substr($expression, $start);
            break;
         } 
         if ($pos0 >= 0 && $pos1 > $pos0) {
            $script .= substr($expression, $start, $pos0 - $start);
            $start = $pos1 + 1;
            $fieldName = substr($expression, $pos0 + 1, $pos1 - $pos0-1);
            // get field value
            $fldval = $bizobj->GetField($fieldName)->m_Value;
            if ($fldval == null)
               $fldval = $bizobj->GetFieldValue($fieldName);
	       
            if ($fldval !== null)
               $script .= $fldval;
            else {
               //$script .= substr($expression, $pos0, $pos1 - $pos0);
               //return "fail to evaluate $expression";
               return "";
            }
         } 
         elseif ($pos0 >= 0 && $pos1 <= $pos0)
            break;
      } 
      return $script;
   }
   
   // @objname:property, @objname:field[fldname].property, @objname:control[ctrlname].property
   // @:prop = @thisobjname:prop
   protected static function ReplaceVarExpr($expression, $object)
   {
      global $g_BizSystem;
      // replace @objname:property to GetObject()->GetProperty(property)
      while (true) {
         // todo: one clause must be separated by whitespace
         $pattern = "/@(\S*):(\S+)/"; 
         if (!preg_match($pattern, $expression, $matches)) break;
         $macro = $matches[0];
         $objname = $matches[1];  $propExpr = $matches[2];
         $obj = null;
         if ($objname == "profile") {  // @profile:attribute is reserved
            $profile_attr = $g_BizSystem->GetUserProfile($propExpr);
            $expression = str_replace($macro, $profile_attr, $expression);
            continue;
         }
         elseif ($objname == "validate") { //@validate:method is reserved
            $expression = str_ireplace('@validate:', '', $expression);

            $validate_section = Expression::ReplaceFieldsExpr($expression, $object);
            $svcobj = $g_BizSystem->GetService(VALIDATE_SERVICE);

            $param_start = strpos($validate_section, '(')+1;
            $param_len = strlen($validate_section)-$param_start-1;
            $function = substr($validate_section, 0, $param_start-1);
            $param_string = substr($validate_section, $param_start, $param_len);
            $params = explode(',',$param_string);
            $val_result = call_user_func_array(array($svcobj, $function), $params);
            return $val_result;
         }	 
         else if ($objname == "" || $objname == "this")
            $obj = $object;
         else 
            $obj = $g_BizSystem->GetObjectFactory()->GetObject($objname);
         if ($obj == null) {
            throw new Exception("Wrong expression syntax ".$expression.", cannot get object ".$objname);
         }
         $pos = strpos($propExpr, ".");
         if ($pos>0) { // in case of @objname:field[fldname].property
            $property1 = substr($propExpr,0,$pos);
            $property2 = substr($propExpr,$pos+1);
            $property_obj = $obj->GetProperty($property1);
            if ($property_obj == null) {
               throw new Exception("Wrong expression syntax ".$expression.", cannot get property object ".$property1." of object ".$objname);
            }
            $val = $property_obj->GetProperty($property2);
         }
         else // in case of @objname:property
            $val = $obj->GetProperty($propExpr);
         if (!$val) $val = "";
            // throw error
         if (is_string($val))
            $val = "'$val'";
         $expression = str_replace($macro, $val, $expression);
      }
      return $expression;
   }
   
   protected static function ReplaceMacrosExpr($expression)
   {
      global $g_BizSystem;
      // replace macro @var:key to $userProfile[$key]
      while (true) {
         $pattern = "/@(\w+):(\w+)/";
         if (!preg_match($pattern, $expression, $matches)) break;
         $macro = $matches[0];
         $macro_var = $matches[1];  $macro_key = $matches[2];
         $val = $g_BizSystem->getMacroValue($macro_var, $macro_key);
         if (!$val) $val = "";
            // throw error
         $expression = str_replace($macro, $val, $expression);
      }
      return $expression;
   }
   
   /**
    * BizDataObj::EvaluateExpression() - evaluate simple expression
    * expression is combination of text, simple expressiones and field variables
    * simple expression - {...}
    * field variable - [field name]
    * expression samples: text1{[field1]*10}text2{function1([field2],'a')}text3
    *
    * @objname:property, @objname:field[fldname].property, @objname:control[ctrlname].property
    * @:prop = @thisobjname:prop
    * [fldname] = @thisobjname:field[fldname].value
    * @demo.BOEvent:Name, @:Name
    * @demo.BOEvent:Field[EventName].Column, @demo.BOEvent:Field[EventName].Value
    * @demo.FMEvent:Control[evt_name].FieldName, @demo.FMEvent:Control[evt_name].Value
    * [EventName] is @demo.BOEvent:Field[EventName].Value in BOEvent.xml
    * 
    * @param string $expression - simple expression supported by the openbiz
    * @return mixed value
    **/
   public static function EvaluateExpression($expression, $object)
   {
      // TODO: check if it's "\[", "\]", "\{" or "\}"
      $script = "";
      $start = 0;
      
      //if (strpos($expression, "{", $start) === false)    // do nothing if no { symbol
      //   return $expression;
      
      // evaluate the expression between {}
      while (true) {
         $pos0 = strpos($expression, "{", $start);
         $pos1 = strpos($expression, "}", $start);
         if ($pos0 === false) {
            if (substr($expression, $start))
               $script .= substr($expression, $start);
            break;
         } 
         if ($pos0 >= 0 && $pos1 > $pos0) {
            $script .= substr($expression, $start, $pos0 - $start);
            $start = $pos1 + 1;
            $section = substr($expression, $pos0 + 1, $pos1 - $pos0-1);
            //echo "###expression 1: ".$section."<br>";
            $section = Expression::ReplaceVarExpr($section, $object);  // replace variable expr
            //echo "###expression 2: ".$section."<br>";
            if (is_a($object, "BizDataObj") AND strstr($section, '['))
               $section = Expression::ReplaceFieldsExpr($section, $object);  // replace [field] with its value

	         if ($section === false)
               $script = ($script == '') ? $section : ($script . $section);
	       
            if ($section != null AND trim($section) != "" AND $section != false) {
               eval ("\$ret = $section;");
               if (!$ret)
                  $ret = $section;
               $script = ($script == '') ? $ret : ($script . $ret);
               unset($ret);
            }
         } 
         elseif ($pos0 >= 0 && $pos1 <= $pos0)
            break;
      } 
      return $script;
   }
}
?>