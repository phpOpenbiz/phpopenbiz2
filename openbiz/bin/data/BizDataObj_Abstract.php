<?php

/**
 * BizDataObj_Abstract class - contains data object metadata functions
 *
 * @package BizDataObj
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */

include_once(OPENBIZ_BIN.'data/private/BizRecord.php');
include_once(OPENBIZ_BIN.'data/private/TableJoin.php');
include_once(OPENBIZ_BIN.'data/private/ObjReference.php');
include_once(OPENBIZ_BIN.'data/private/BizDataObj_Assoc.php');
include_once(OPENBIZ_BIN.'data/private/BizDataObj_SQLHelper.php');
include_once(OPENBIZ_BIN.'data/BizField.php');

abstract class BizDataObj_Abstract extends MetaObject implements iSessionObject
{
   // metadata vars are public, necessary for metadata inheritance
   public $m_Database;
   public $m_BaseSearchRule = null;    // support expression
   public $m_BaseSortRule = null;      // support expression
   public $m_BaseOtherSQLRule = null;  // support expression
   public $m_MainTable = "";
   public $m_BizRecord = null;
   public $m_InheritFrom;
   public $m_AccessRule = null;        // support expression
   public $m_UpdateCondition = null;   // support expression
   public $m_DeleteCondition = null;   // support expression
   public $m_IdGeneration = null;      // support expression
   public $m_ObjReferences = null;
   public $m_TableJoins = null;
   public $m_Parameters = null;
   public $m_Stateless = null;
   
   public $m_SearchRule = null;        // support expression
   public $m_SortRule = null;          // support expression
   public $m_OtherSQLRule = null;      // support expression
   protected $m_Limit = array();

   /**
    * BizDataObj::__construct(). Initialize BizDataObj with xml array
    *
    * @param array $xmlArr
    * @return void
    */
   function __construct(&$xmlArr)
   {
      $this->ReadMetadata($xmlArr);

      $this->InheritParentObj();
   }

   protected function ReadMetadata(&$xmlArr)
   {
      parent::ReadMetaData($xmlArr);
      $this->m_InheritFrom = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["INHERITFROM"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["INHERITFROM"] : null;
      $this->m_SearchRule = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["SEARCHRULE"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["SEARCHRULE"] : null;
      $this->m_BaseSearchRule = $this->m_SearchRule;
      $this->m_SortRule = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["SORTRULE"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["SORTRULE"] : null;
      $this->m_BaseSortRule = $this->m_SortRule;
      $this->m_OtherSQLRule = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["OTHERSQLRULE"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["OTHERSQLRULE"] : null;
      $this->m_BaseOtherSQLRule = $this->m_OtherSQLRule;
      $this->m_AccessRule = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["ACCESSRULE"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["ACCESSRULE"] : null;
      $this->m_UpdateCondition = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["UPDATECONDITION"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["UPDATECONDITION"] : null;
      $this->m_DeleteCondition = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["DELETECONDITION"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["DELETECONDITION"] : null;
      $this->m_Database = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["DBNAME"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["DBNAME"] : null;
      if ($this->m_Database == null)
         $this->m_Database = "Default";
      $this->m_MainTable = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["TABLE"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["TABLE"] : null;
      $this->m_IdGeneration = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["IDGENERATION"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["IDGENERATION"] : null;
      $this->m_Stateless = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["STATELESS"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["STATELESS"] : null;

      $this->m_Name = $this->PrefixPackage($this->m_Name);
      $this->m_InheritFrom = $this->PrefixPackage($this->m_InheritFrom);

      // build BizRecord
      $this->m_BizRecord = new BizRecord($xmlArr["BIZDATAOBJ"]["BIZFIELDLIST"]["BIZFIELD"],"BizField",$this);
      // build TableJoins
      $this->m_TableJoins = new MetaIterator($xmlArr["BIZDATAOBJ"]["TABLEJOINS"]["JOIN"],"TableJoin",$this);
      // build ObjReferences
      $this->m_ObjReferences = new MetaIterator($xmlArr["BIZDATAOBJ"]["OBJREFERENCES"]["OBJECT"],"ObjReference",$this);
      // read in parameters
      $this->m_Parameters = new MetaIterator($xmlArr["BIZDATAOBJ"]["PARAMETERS"]["PARAMETER"],"Parameter");
   }

   // Name, Package, Class cannot be inherited
   protected function InheritParentObj()
   {
      if (!$this->m_InheritFrom) return;
      global $g_BizSystem;
      $prtObj = $g_BizSystem->GetObjectFactory()->GetObject($this->m_InheritFrom);

      $this->m_Description = $this->m_Description ? $this->m_Description : $prtObj->m_Description;
      $this->m_SearchRule = $this->m_SearchRule ? $this->m_SearchRule : $prtObj->m_SearchRule;
      $this->m_BaseSearchRule = $this->m_SearchRule;
      $this->m_SortRule = $this->m_SortRule ? $this->m_SortRule: $prtObj->m_SortRule;
      $this->m_BaseSortRule = $this->m_SortRule;
      $this->m_OtherSQLRule = $this->m_OtherSQLRule ? $this->m_OtherSQLRule: $prtObj->m_OtherSQLRule;
      $this->m_AccessRule = $this->m_AccessRule ? $this->m_AccessRule: $prtObj->m_AccessRule;
      $this->m_UpdateCondition = $this->m_UpdateCondition ? $this->m_UpdateCondition: $prtObj->m_UpdateCondition;
      $this->m_DeleteCondition = $this->m_DeleteCondition ? $this->m_DeleteCondition: $prtObj->m_DeleteCondition;
      $this->m_Database = $this->m_Database ? $this->m_Database: $prtObj->m_Database;
      $this->m_MainTable = $this->m_MainTable ? $this->m_MainTable: $prtObj->m_MainTable;
      $this->m_IdGeneration = $this->m_IdGeneration ? $this->m_IdGeneration: $prtObj->m_IdGeneration;
      $this->m_Stateless = $this->m_Stateless ? $this->m_Stateless: $prtObj->m_Stateless;

      $this->m_BizRecord->merge($prtObj->m_BizRecord);
      foreach ($this->m_BizRecord as $fld) $fld->AdjustBizObjName($this->m_Name);
         $this->m_TableJoins->merge($prtObj->m_TableJoins);
      $this->m_ObjReferences->merge($prtObj->m_ObjReferences);
      $this->m_Parameters->merge($prtObj->m_Parameters);
   }
   
   public function GetSessionVars($sessCtxt) {}
   
   public function SetSessionVars($sessCtxt) {}
   
   public function ClearSearchRule()   // reset searchrule
   {
      $this->m_SearchRule = $this->m_BaseSearchRule;
   }

   public function ClearSortRule()  // reset sortrule
   {  
      $this->m_SortRule = $this->m_BaseSortRule; 
   }
   
   public function ClearOtherSQLRule()  // reset othersqlrule
   {  
      $this->m_OtherSQLRule = $this->m_BaseOtherSQLRule;
   }
   
   public function ClearAllRules()  // reset sortrule
   {  
      $this->m_SearchRule = $this->m_BaseSearchRule;
      $this->m_SortRule = $this->m_BaseSortRule; 
      $this->m_OtherSQLRule = $this->m_BaseOtherSQLRule;
      $this->m_Limit = array();
   }

   /**
    * BizDataObj::SetSearchRule() - Set search rule as text in sql where clause. i.e. [fieldName] opr Value
    *
    * @param string $rule search rule has format "[fieldName] opr Value"
    * @param boolean $overwrite specify if this rule should overwrite any existing rule
    * @return void
    **/
   public function SetSearchRule($rule, $overwrite=false)
   {
      if (!$rule || $rule == "")
         return;
      if (!$this->m_SearchRule || $overwrite == true) {
         $this->m_SearchRule = $rule;
      } elseif (strpos($this->m_SearchRule, $rule) === false) {
            $this->m_SearchRule .= " AND " . $rule;
      }
   }

   /**
    * BizDataObj::SetSortRule() - Set search rule as text in sql order by clause. i.e. [fieldName] DESC|ASC
    *
    * @param string $rule sort rule has format "[fieldName] DESC|ASC"
    * @return void
    **/
   public function SetSortRule($rule)
   {
      // sort rule has format "[fieldName] DESC|ASC", replace [fieldName] with table.column
      $this->m_SortRule = $rule;
   }

   /**
    * BizDataObj::SetOtherSQLRule() - Append extra SQL statment in sql. i.e. GROUP BY [fieldName]
    *
    * @param string $rule has SQL format "GROUP BY [fieldName] HAVING ..."
    * @return void
    **/
   public function SetOtherSQLRule($rule)
   {
      // $rule has SQL format "GROUP BY [fieldName] HAVING ...". replace [fieldName] with table.column
      $this->m_OtherSQLRule = $rule;
   }
   
   public function SetLimit($count, $offset=0)
   {
      $this->m_Limit['count'] = $count;
      $this->m_Limit['offset'] = $offset;
   }

   /**
    * BizDataObj::GetDBConnection() - get database connection
    *
    * @return Zend_DB_Adaptor
    **/
   public function GetDBConnection()
   {
      global $g_BizSystem;
      return $g_BizSystem->GetDBConnection($this->m_Database);
   }
   
   public function GetProperty($propertyName)
   {
      $ret = parent::GetProperty($propertyName);
      if ($ret) return $ret;
      if ($propertyName == "Table") return $this->m_Table;
      if ($propertyName == "SearchRule") return $this->m_SearchRule;
      // get control object if propertyName is "Field[fldname]"
      $pos1 = strpos($propertyName, "[");
      $pos2 = strpos($propertyName, "]");
      if ($pos1>0 && $pos2>$pos1) {
         $propType = substr($propertyName, 0, $pos1);
         $fldname = substr($propertyName, $pos1+1,$pos2-$pos1-1);
         if ($propType == "param") {   // get parameter
         return $this->m_Parameters->get($fldname);
         }
         return $this->GetField($fldname);
      }
   }

   public function GetParameter($paramName)
   {
      return $this->m_Parameters[$paramName]->m_Value;
   }

   /**
    * BizDataObj::GetRefObject() - Get the object instance defined in the object reference
    *
    * @param string $objName the object name list in the ObjectReference part
    * @return mixed object instance
    */
   public function GetRefObject($objName)
   {
      global $g_BizSystem;
      // see if there is such object in the ObjReferences
      $objRef = $this->m_ObjReferences->get($objName);
      if (!$objRef)
      return null;
      // apply association on the object
      // $assc = $this->EvaluateExpression($objRef->m_Association);

      // get the object instance
      $obj = $g_BizSystem->GetObjectFactory()->GetObject($objName);
      $obj->SetAssociation($objRef, $this);
      return $obj;
   }
   
   /**
    * BizDataObj:GetAssociation() - Return the Association
    *
    * @return protected m_Association
    **/
   public function GetAssociation()
   {
      return $this->m_Association;
   }

   /**
    * BizDataObj::SetAssociation() - set the association of the object
    *
    * @param ObjReference $objRef
    * @param BizDataObj $asscObj
    * @return void
    */
   protected function SetAssociation($objRef, $asscObj)
   {
      $this->m_Association["AsscObjName"] = $asscObj->m_Name;
      $this->m_Association["Relationship"] = $objRef->m_Relationship;
      $this->m_Association["Table"] = $objRef->m_Table;
      $this->m_Association["Column"] = $objRef->m_Column;
      $this->m_Association["FieldRef"] = $objRef->m_FieldRef;
      $this->m_Association["FieldRefVal"] = $asscObj->GetFieldValue($objRef->m_FieldRef);
      if ($objRef->m_Relationship == "M-M") {
         $this->m_Association["XTable"] = $objRef->m_XTable;
         $this->m_Association["XColumn1"] = $objRef->m_XColumn1;
         $this->m_Association["XColumn2"] = $objRef->m_XColumn2;
         $this->m_Association["XKeyColumn"] = $objRef->m_XKeyColumn;
         $this->m_Association["XDataObj"] = $objRef->m_XDataObj;
      }
   }
   
   abstract public function NewRecord();
   
   abstract public function InsertRecord($recArr);
   
   abstract public function UpdateRecord($recArr, $oldRec=null);
   
   abstract public function DeleteRecord($recArr);   
   
   /* Fetches SQL result rows as a sequential array according the query rules set before.
    * sample code: 
      $do->ResetRules();
      $do->SetSearchRule($search_rule1);
      $do->SetSearchRule($search_rule2);
      $do->SetSortRule($sort_rule);
      $do->SetOtherRule($groupby);
      $total = $do->Count();
      $do->SetLimit($count, $offset=0);
      $recordSet = $do->Fetch();
    */
   abstract public function Fetch();
   
   /* Fetches SQL result rows as a sequential array without using query rules set before.
    * sample code:
      // fetch all record with firstname starting with Mike
      $do->DirectFetch("[FirstName] LIKE 'Mike%'");
      // fetch first 10 records with firstname starting with Mike
      $do->DirectFetch("[FirstName] LIKE 'Mike%'", 10);
      // fetch 20th-30th records with firstname starting with Mike
      $do->DirectFetch("[FirstName] LIKE 'Mike%'", 10, 20);
    */
   abstract public function DirectFetch($searchRule="", $count=-1, $offset=0);
   
   /*
    * Do the search query and return results set as PDOStatement
    * sample code: 
      $do->ResetRules();
      $do->SetSearchRule($search_rule1);
      $do->SetSearchRule($search_rule2);
      $do->SetSortRule($sort_rule);
      $do->SetOtherRule($groupby);
      $total = $do->Count();
      $do->SetLimit($count, $offset=0);
      $resultSet = $do->Find();
      $do->GetDBConnection()->setFetchMode(PDO::FETCH_ASSOC);
      while ($record = $resultSet->fetch()) 
      {
         print_r($record);
      }
    */
   abstract public function Find();
   
   // count the number of record of the search results. it ignores limit setting
   abstract public function Count();
}

?>