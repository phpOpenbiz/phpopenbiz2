<?php
/**
 * BizRecord class - BizRecord implements basic function of handling record
 *
 * @package BizDataObj
 */
class BizRecord extends MetaIterator
{
   protected $m_KeyFldColMap = array();
   protected $m_ColFldMap = array();
   public $m_InputFields;

   function __construct(&$xmlArr, $className, $prtObj=null)
   {
      parent::__construct($xmlArr, $className, $prtObj);
      $this->InitSetup();
   }

   public function merge(&$anotherMIObj)
   {
      parent::merge($anotherMIObj);
      $this->InitSetup();
   }
   private function InitSetup()
   {
      unset($this->m_ColFldMap); $this->m_ColFldMap = array();
      unset($this->m_KeyFldColMap); $this->m_KeyFldColMap = array();
      $i = 0;
      // generate column index if the column is one of the basetable (m_Column!="")
      foreach($this->m_var as $key=>$fld) {  // $key is fieldname, $fld is fieldobj
         ////////////////////////////////////////////////////////////////////
         // TODO: join fields and nonjoin fields may have same column name
         ////////////////////////////////////////////////////////////////////
         if ($fld->m_Column && !$fld->m_Join)  // ignore the joined field column
            $this->m_ColFldMap[$fld->m_Column] = $key;
         if ($fld->m_Column || $fld->m_SqlExpression) {
            $fld->m_Index = $i++;
         }
      }
      // create key field column map to support composite key
      if (isset($this->m_var["Id"]) && $this->m_var["Id"]->m_Column)
      {
         $keycols = explode(",", $this->m_var["Id"]->m_Column);
         foreach ($keycols as $col) {
            $fld = $this->GetFieldByColumn($col);  // main table
            $this->m_KeyFldColMap[$fld] = $col;
         }
      }
   }

   // TODO: 2 columns can have the same name in case of joined fields
   public function GetFieldByColumn($column, $table=null)
   {
      if(array_key_exists($column, $this->m_ColFldMap))
         return $this->m_ColFldMap[$column];
      return null;
   }

   /**
    * BizRecord::GetEmptyRecordArr() - Get an empty record array. Called by BizDataObj::NewRecord()
    *
    * @return array
    **/
   final public function GetEmptyRecordArr()
   {
      $recArr = array();
      foreach ($this->m_var as $key=>$fld) {
         $recArr[$key] = $fld->GetDefaultValue();
      }
      return $recArr;
   }

   /**
    * BizRecord::GetKeyValue() - Get key (Id) value. If Id is defined as composite key, the returned key value is the combination of key columns
    *
    * @return array
    **/
   final public function GetKeyValue($useOldValue=false)
   {
      $keyValue = "";
      foreach($this->m_KeyFldColMap as $fldname=>$colname) {
         $val = $useOldValue ? $this->m_var[$fldname]->m_OldValue : $this->m_var[$fldname]->m_Value;
         if ($keyValue == "")
            $keyValue .= $val;
         else
            // use base64 (a-zA-Z1-9+-) to encode the key and connect them with "#"
            $keyValue .= CK_CONNECTOR . $val;
      }
      return $keyValue;
   }

   /**
    * BizRecord::GetKeyFields() - Get a list of fields (name) who are defined as keys columns
    *
    * @return array
    **/
   final public function GetKeyFields()
   {
      $keyFlds = array();
      foreach($this->m_KeyFldColMap as $fldname=>$colname) {
         $keyFlds[$fldname] = $this->m_var[$fldname];
      }
      return $keyFlds;
   }

   public function GetKeySearchRule($useOldValue=false, $useColumnName=false)
   {
      $keyFlds = $this->GetKeyFields();
      $retStr = "";
      foreach ($keyFlds as $fldname=>$fldobj) {
         $lhs = $useColumnName ? $fldobj->m_Column : "[$fldname]";
         $rhs = $useOldValue ? $fldobj->m_OldValue : $fldobj->m_Value;
         if($retStr=="") $retStr .= $lhs."='".$rhs."'";
         else $retStr .= " AND ".$lhs."='".$rhs."'";
      }
      return $retStr;
   }

   public function SetRecordArr($recArr)
   {
      if (!$recArr) return;
      foreach ($this->m_var as $key=>$fld) {
         if (key_exists($key, $recArr))
         $recArr[$key] = $fld->SetValue($recArr[$key]);
      }
   }

   /**
    * BizRecord::SetInputRecord() - assign a record array as the internal record of the BizRecord
    *
    * @param array $inpuArr
    * @return void
    **/
   final public function SetInputRecord(&$inputArr)
   {
      global $g_BizSystem;
      // unformat the inputs
      unset($this->m_InputFields);
      foreach($inputArr as $key=>$value) {   // if allow changing key field, need to keep the old value which is also useful for audit trail
         if (!$value)
            continue;
         $bizFld = $this->m_var[$key];
         if (!$bizFld) continue;

         // todo: need to optimize on lob column
         $realVal = $g_BizSystem->GetTypeManager()->FormattedStringToValue($bizFld->m_Type, $bizFld->m_Format, $value);
         $bizFld->SetValue($realVal);
         $this->m_InputFields[] = $key;
      }
      //$this->m_var["Id"]->SetValue($this->GetKeyValue());
   }

   final public function SaveOldRecord(&$inputArr)
   {
      foreach($inputArr as $key=>$value) {
         $bizFld = $this->m_var[$key];
         if (!$bizFld) continue;
         $bizFld->SaveOldValue();
      }
   }

   /**
    * BizRecord::GetRecordArr() - Get record array by converting input Column-Value array to Field-Value pairs
    *
    * @return array
    **/
   final public function GetRecordArr($sqlArr=null)
   {
      if ($sqlArr)
      $this->SetSqlRecord($sqlArr);
      $recArr = array();
      foreach ($this->m_var as $key=>$fld)
         $recArr[$key] = $fld->GetValue();
      return $recArr;
   }

   public function ConvertSqlArrToRecArr($sqlArr)
   {
      $recArr = array();
      foreach ($this->m_var as $key=>$fld)
      {
         if ($fld->m_Column || $fld->m_SqlExpression) {
            $recArr[$key] = $sqlArr[$fld->m_Index];
         }
         else
         $recArr[$key] = "";
      }
      return $recArr;
   }

   private function SetSqlRecord($sqlArr)
   {
      foreach ($this->m_var as $key=>$fld)
      {
         if ($fld->m_Column || $fld->m_SqlExpression) {
            $fld->SetValue($sqlArr[$fld->m_Index]);
         }
      }
      $this->m_var["Id"]->SetValue($this->GetKeyValue());
   }
   
   public function GetJoinInputRecord($join)
   {
      $recArr = array();
      foreach ($inputFields as $key=>$value) {
         $fld = $this->m_var[$key];
         // do not consider joined columns
         if ($fld->m_Join == $join) {
            $recArr[$key] = $value;
         }
      }
      return $recArr;
   }

   /**
    * BizRecord::GetToSaveFields() - Get insert/update fields
    *
    * @return array
    **/
   final public function GetToSaveFields($type)
   {
      // TODO: if join != null, get columns only for the join
      $sqlFields = array();
      // expand input fields with oncreate or onupdate fields
      $inputFields = $this->m_InputFields;
      foreach ($this->m_var as $key=>$fld)
      {
         if (($type=='UPDATE' && $fld->m_ValueOnUpdate != null)
             || ($type=='CREATE' && $fld->m_ValueOnCreate != null))
         {
             if (!in_array($key, $this->m_InputFields))
             	$inputFields[] = $key;
         }
      }

      foreach ($inputFields as $key) {
         // ignore the composite key Id field
         if ($key == "Id" && count($this->m_KeyFldColMap) > 1)
            continue;
         $fld = $this->m_var[$key];
         // do not consider joined columns
         if ($fld->m_Column && !$fld->m_Join) {
            $sqlFields[] = $fld;
         }
      }
      return $sqlFields;
   }
}
?>