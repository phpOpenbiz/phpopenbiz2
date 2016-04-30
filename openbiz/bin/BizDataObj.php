<?PHP
// constant defination
define('CK_CONNECTOR', "#");  // composite key connector character

/**
 * BizDataObj class - class BizDataObj is the base class of all data object classes
 *
 * @package BizDataObj
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */

class BizDataObj extends MetaObject implements iSessionObject
{
   // metadata vars are public, necessary for metadata inheritance
   public $m_Database;
   public $m_BaseSearchRule = null;
   public $m_BaseSortRule = null;
   public $m_SearchRule = null;        // support expression
   public $m_SortRule = null;          // support expression
   public $m_OtherSQLRule = null;      // support expression
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

   // todo: provide function to access these vars
   protected $m_CurrentPage = 1;
   protected $m_TotalPages = 0;
   protected $m_TotalRecords = 0;
   protected $m_PageRange = 10;
   protected $m_RecordId = null;

   protected $m_DbConnect = null;
   protected $m_SqlSearchRule = null;
   protected $m_SqlSortRule = null;
   protected $m_SqlOtherSQLRule = null;
   protected $m_QuerySQL = "";
   protected $m_CurrentRecord = null;
   protected $m_Readonly = false;
   protected $m_KeyFldsCols;   /// COMPKEY
   protected $m_ErrorMessage = "";

   protected $m_Association = null;
   protected $m_DataSqlObj = null;

   //protected $m_DataRetrieved = false;
   protected $m_Stateless = false;
   //protected $m_ResultSet = null;
   
   private $m_PageQuery = false;

   // todo: remove this method
   //public function CheckDataRetrieved() { return $this->m_DataRetrieved; }

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

   /**
    * BizDataObj::GetSessionContext() - Retrieve Session data of this object
    *
    * @param SessionContext $sessCtxt
    * @return void
    */
   public function GetSessionVars($sessCtxt)
   {
      if ($this->m_Stateless == "Y")
	       return;
      $sessCtxt->GetObjVar($this->m_Name, "CurrentPage", $this->m_CurrentPage);
      $sessCtxt->GetObjVar($this->m_Name, "PageRange", $this->m_PageRange);
      $sessCtxt->GetObjVar($this->m_Name, "TotalRecords", $this->m_TotalRecords);
      $sessCtxt->GetObjVar($this->m_Name, "RecordId", $this->m_RecordId);
      $sessCtxt->GetObjVar($this->m_Name, "CurrentRecord", $this->m_CurrentRecord);
      $sessCtxt->GetObjVar($this->m_Name, "SearchRule", $this->m_SearchRule);
      $sessCtxt->GetObjVar($this->m_Name, "SortRule", $this->m_SortRule);
      $sessCtxt->GetObjVar($this->m_Name, "OtherSqlRule", $this->m_OtherSQLRule);
      $sessCtxt->GetObjVar($this->m_Name, "Association", $this->m_Association);

      if ($this->m_PageRange>0)
         $this->m_TotalPages = (int) ceil($this->m_TotalRecords / $this->m_PageRange);

      foreach($this->m_BizRecord as $fldName=>$bizFld)
         $bizFld->SetValue($this->m_CurrentRecord[$fldName]);
   }

   /**
    * BizDataObj::SetSessionContext() - Save Session data of this object
    *
    * @param SessionContext $sessCtxt
    * @return void
    */
   public function SetSessionVars($sessCtxt)
   {
      if ($this->m_Stateless == "Y")
	       return;
      $sessCtxt->SetObjVar($this->m_Name, "CurrentPage", $this->m_CurrentPage);
      $sessCtxt->SetObjVar($this->m_Name, "PageRange", $this->m_PageRange);
      $sessCtxt->SetObjVar($this->m_Name, "TotalRecords", $this->m_TotalRecords);
      $sessCtxt->SetObjVar($this->m_Name, "RecordId", $this->m_RecordId);
      $sessCtxt->SetObjVar($this->m_Name, "CurrentRecord", $this->m_CurrentRecord);
      $sessCtxt->SetObjVar($this->m_Name, "SearchRule", $this->m_SearchRule);
      $sessCtxt->SetObjVar($this->m_Name, "SortRule", $this->m_SortRule);
      $sessCtxt->SetObjVar($this->m_Name, "OtherSqlRule", $this->m_OtherSQLRule);
      $sessCtxt->SetObjVar($this->m_Name, "Association", $this->m_Association);
      
      // TODO: only keep association, current record in session
   }

   public function GetBookmark()
   {
      if ($this->m_Stateless == "Y")
	       return;
      $bookmark[] = $this->m_RecordId;
      $bookmark[] = $this->m_CurrentPage;
      $bookmark[] = $this->m_SearchRule;
      $bookmark[] = $this->m_SortRule;
      return $bookmark;
   }

   public function SetBookmark($bookmark)
   {
      if ($this->m_Stateless == "Y")
	       return;
      $this->m_RecordId = $bookmark[0];
      $this->m_CurrentPage = $bookmark[1];
      $this->m_SearchRule = $bookmark[2];
      $this->m_SortRule = $bookmark[3];
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

   public function GetErrorMessage() { return $this->m_ErrorMessage; }

   public function GetOnAuditFields() {
      $fldlist = array();
      foreach ($this->m_BizRecord as $fld) {
         if ($fld->m_OnAudit)
         $fldlist[] = $fld;
      }
      return $fldlist;
   }

   public function GetField($fldname) { return $this->m_BizRecord->get($fldname); }

   public function GetFieldNameByColumn($column) { return $this->m_BizRecord->GetFieldByColumn($column); }

   public function GetFieldValue($fldname, $getActiveOnly=false) {
      $rec = $this->GetActiveRecord();
      return $rec[$fldname];
      //return $this->m_BizRecord->get($fldname)->GetValue();
   }

   public function GetCurrentPageNumber() { return $this->m_CurrentPage; }
   
   public function SetCurrentPageNumber($page) { $this->m_CurrentPage = $page; }

   public function GetTotalPageCount() { return $this->m_TotalPages; }

   public function SetPageRange($range) { $this->m_PageRange = $range; }

   /**
    * BizDataObj:SetRecordID() - Set m_RecordID to the passed in parameter
    *
    * @param array $recordID
    * @return void
    **/
   public function SetRecordID($recordID)
   {
      $this->m_RecordID = $recordID;
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

   public function ClearSearchRule() {
      $this->m_SearchRule = $this->m_BaseSearchRule;
   }

   public function ClearSortRule() {  $this->m_SortRule = $this->m_BaseSortRule; }

   /**
    * BizDataObj::SetSearchRule() - Set search rule as text in sql where clause. i.e. [fieldName] opr Value
    *
    * @param string $rule search rule has format "[fieldName] opr Value"
    * @param boolean $overwrite specify if this rule should overwrite any existing rule
    * @return void
    **/
   public function SetSearchRule($rule = null, $overwrite=false)
   {
      if (!$rule) {
         return;
      } elseif (!$this->m_SearchRule or $overwrite == true) {
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
   public function SetSortRule($rule = null)
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
   protected function SetOtherSQLRule($rule = null)
   {
      // $rule has SQL format "GROUP BY [fieldName] HAVING ...". replace [fieldName] with table.column
      $this->m_OtherSQLRule = $rule;
   }

   /**
    * BizDataObj::RuleToSql() - Convert search/sort rule to sql clause, replace [fieldName] with table.column
    * openbiz SQL expression as "[fieldName] opr 'Value' AND/OR [fieldName] opr 'Value'...". "()" is valid syntax
    *
    * @param string $rule "[fieldName] ..."
    * @return string sql statement
    **/
   protected function RuleToSql($rule)
   {
      global $g_BizSystem;
      //echo $rule;

      if (!$this->m_DataSqlObj) $this->m_DataSqlObj = new BizDataSql();

      $rule = Expression::EvaluateExpression($rule,$this);

      // replace all [field] with table.column
      foreach($this->m_BizRecord as $bizFld)
      {
         if (!$bizFld->m_Column)
            continue;   // ignore if no column mapped
         $fld_pattern = "[".$bizFld->m_Name."]";
         if (strpos($rule, $fld_pattern) === false)
            continue;   // ignore if no [field] found
         else
         {
            $tableColumn = $this->m_DataSqlObj->GetTableColumn($bizFld->m_Join, $bizFld->m_Column);
            $rule = str_replace($fld_pattern, $tableColumn, $rule);
         }
      }

      return $rule;
   }

   private function CompKeyRuleToSql($compColum, $compValue)
   {
      $colArr = split(",", $compColum);
      $valArr = split(CK_CONNECTOR, $compValue);
      $sql = "";
      for ($i=0; $i < count($colArr); $i++)
      {
         $tableColumn = $this->m_DataSqlObj->GetTableColumn("", $colArr[$i]);
         $sql .= " $tableColumn = '" . $valArr[$i] . "' ";
      }
      return $sql;
   }

   public function GetDBConnection()
   {
      global $g_BizSystem;
      return $g_BizSystem->GetDBConnection($this->m_Database);
   }

   /**
    * BizDataObj::RunSearch() - Run the SQL statement, if no database connection, connect the database first
    * @param $resultRecords returned result record array
    * @return boolean - if return false, the caller can call GetErrorMessage to get the error.
    **/
   // todo: throw BDOException
   public function RunSearch(&$resultRecords)
   {
      $resultSet = $this->_run_search($this->m_CurrentPage, true);  // regular search or page search
      if ($resultSet !== null)
      {
         $count = 0;
         while ($recArray = $this->_fetch_record($resultSet))
         {
            $resultRecords[$count] = $recArray;
            $count++;
         }
      }
      else
        return false;

      return true;
   }

   /**
    * BizDataObj::FetchRecords() - run query and get the query results without affecting DataObject internal state
    * by default it gets number of records starting from the first row.
    * if pageNum>0, it gets number of records starting from the first row of the page
    * @param $searchRule search rule applied on the query
    * @param $resultRecord returned result record array
    * @param $recNum number of records to be returned. if -1, all query results returned
    * @param $clearSearchRule indicates if search rule need to be cleared before query
    * @param $noAssociation indicates if current association condition is not used in query
    * @return boolean - if return false, the caller can call GetErrorMessage to get the error.
    */
   public function FetchRecords($searchRule, &$resultRecord, $recNum=-1, $pageNum=-1, $clearSearchRule=true, $noAssociation=false)
   {
      if ($recNum == 0) return;
      $curRecord = $this->m_CurrentRecord;
      $recId = $this->m_RecordId;
      $oldSearchRule = $this->m_SearchRule;
      $this->m_CurrentRecord = null;
      if ($clearSearchRule)
         $this->ClearSearchRule();
      $this->SetSearchRule($searchRule);
      if ($noAssociation) {
         $oldAssociation = $this->m_Association;
         $this->m_Association = null;
      }
      $resultSet = $this->_run_search($pageNum);  // regular search or page search
      if ($resultSet !== null)
      {
         $count = 0;
         while ($recArray = $this->_fetch_record($resultSet))
         {
            $resultRecord[$count] = $recArray;
            $count++;
            if ($recNum>0 && $count==$recNum)
            break;
         }
      }
      else
         return false;
      if ($noAssociation)
         $this->m_Association = $oldAssociation;
      $this->m_SearchRule = $oldSearchRule;
      $this->m_CurrentRecord = $curRecord;
      $this->m_RecordId = $recId;
      return true;
   }

   /**
    * BizDataObj::Query() - run query with current search rule and returns PDO statement
    * @param $page if page>0, run page query, otherwise run normal query
    * @return PDOStatement
    */
   public function Query($page)
   {
      return $this->_run_search($page);
   }

   /**
    * BizDataObj::Query() - run query with current search rule and returns PDO statement
    * @param int $page - If $page>0, do page query, query for the records in given page. If $page<0, RunSearch does a normal query.
    * @return PDOStatement
    */
   protected function _run_search($page=1)
   {
      $this->BuildQuerySQL();
      BizSystem::log(LOG_DEBUG, "DATAOBJ", "Query Sql = ".$this->m_QuerySQL);

      // get database connection
      $db = $this->GetDBConnection();

      if ($this->m_PageRange>0 && $page>0) {
         // get the total row number first
         $pageRange = $this->m_PageRange;
         $this->m_TotalRecords = $this->_get_number_records($db, $this->m_QuerySQL);
         $this->m_TotalPages = ceil($this->m_TotalRecords/$pageRange);
         $offset = ($page-1) * $pageRange;
         $sql = $db->limit($this->m_QuerySQL, $pageRange, $offset);
         $this->m_PageQuery = true;
      }
      else {
         $sql = $this->m_QuerySQL;
         $this->m_PageQuery = false;
      }
      try {
         $resultSet = $db->query($sql);
      }
      catch (Exception $e) {
         //echo 'Caught exception: ',  $e->getMessage(), "\n";
         $this->m_ErrorMessage = "Error in query: " . $this->m_QuerySQL . ". " . $e->getMessage();
         return null;
      }
      return $resultSet;
   }

   private function _get_number_records($db, &$sql)
   {
      if (preg_match("/^\s*SELECT\s+DISTINCT/is", $sql) || preg_match('/\s+GROUP\s+BY\s+/is',$sql)) 
      {
   		// ok, has SELECT DISTINCT or GROUP BY so see if we can use a table alias
   		$rewritesql = preg_replace('/(\sORDER\s+BY\s.*)/is','',$sql);
   		
   		// In order to avoid fetching row's information because there is no need to
         // bc we only want to count the resulting record we can only get '1' per row
         // also this helps us out to avoid problems with duplicate column names that
         // are invalid within a subquery. jgonzalez@innox.com.mx
         $rewritesql = preg_replace('/SELECT(.+?)FROM/is','SELECT 1 AS record FROM',$sql);
         //end

   		$rewritesql = "SELECT COUNT(*) FROM ($rewritesql) _TABLE_ALIAS_"; 
   	} 
   	else 
   	{ 
         // now replace SELECT ... FROM with SELECT COUNT(*) FROM
   		$rewritesql = preg_replace(
   					'/^\s*SELECT\s.*\s+FROM\s/Uis','SELECT COUNT(*) FROM ',$sql);
   
   		// Because count(*) and 'order by' fails with mssql, access and postgresql.
   		// Also a good speedup optimization - skips sorting!
   		$rewritesql = preg_replace('/(\sORDER\s+BY\s.*)/is','',$rewritesql);
   	}

		$result = $db->query($rewritesql);
		$sqlArr = $result->fetch();
		return $sqlArr[0];
   }

   // get record from result setand move the cursor to next row
   // return record array
   private function _fetch_record($resultSet)
   {
      if ($sqlArr = $resultSet->fetch())
      {
         if ($this->m_PageQuery)
         {
            global $g_BizSystem;
            $dbinfo = $g_BizSystem->GetConfiguration()->GetDatabaseInfo($this->m_Database);
            $dbtype = strtolower($dbinfo["Driver"]);
            if ($dbtype == 'oracle' || $dbtype == 'oci8' || $dbtype == 'pdo_oci')
               array_shift($sqlArr);
         }
         $this->m_CurrentRecord = $this->m_BizRecord->ConvertSqlArrToRecArr($sqlArr);
         $this->m_CurrentRecord = $this->m_BizRecord->GetRecordArr($sqlArr);
         $this->m_RecordId = $this->m_CurrentRecord["Id"];
      }
      else
         return null;
      return $this->m_CurrentRecord;
   }

   /**
    * BizDataObj::BuildQuerySQL() - Build the Select SQL statement based on the fields and search/sort rule
    *
    * @return void
    **/
   protected function BuildQuerySQL()
   {
      $this->m_QuerySQL = null;
      // todo: if no searchrule or sortrule change ...
      // build the SQL statement based on the fields and search rule
      if (!$this->m_DataSqlObj) {
         //include_once("BizDataSql.php");
         $this->m_DataSqlObj = new BizDataSql();
         // add table
         $this->m_DataSqlObj->AddMainTable($this->m_MainTable);
         // add join table
         if ($this->m_TableJoins) {
            foreach($this->m_TableJoins as $tableJoin) {
               $tbl_col = $this->m_DataSqlObj->AddJoinTable($tableJoin);
            }
         }
         // add columns
         foreach($this->m_BizRecord as $bizFld) {
            if ($bizFld->m_Column && $bizFld->m_Type == "Blob")   // ignore blob column
               continue;
            if ($bizFld->m_Column && !$bizFld->m_SqlExpression)
               $this->m_DataSqlObj->AddTableColumn($bizFld->m_Join, $bizFld->m_Column);
            if ($bizFld->m_SqlExpression) {
               $this->m_DataSqlObj->AddSqlExpression($this->ConvertSqlExpresion($bizFld->m_SqlExpression));
            }
         }
      }

      $this->m_DataSqlObj->ResetSQL();

      // append SearchRule in the WHERE clause
      $sqlSearchRule = $this->RuleToSql($this->m_SearchRule);
      $this->m_DataSqlObj->AddSqlWhere($sqlSearchRule);

      // append SearchRule in the ORDER BY clause
      $sqlSortRule = $this->RuleToSql($this->m_SortRule);
      $this->m_DataSqlObj->AddOrderBy($sqlSortRule);

      // append SearchRule in the other SQL clause
      $sqlOtherSQLRule = $this->RuleToSql($this->m_OtherSQLRule);
      $this->m_DataSqlObj->AddOtherSQL($sqlOtherSQLRule);

      // append SearchRule in the AccessRule clause
      $sqlAccessSQLRule = $this->RuleToSql($this->m_AccessRule);
      $this->m_DataSqlObj->AddSqlWhere($sqlAccessSQLRule);

      // add association to SQL
      global $g_BizSystem;
      if ($this->m_Association["AsscObjName"] != "" && $this->m_Association["FieldRefVal"] == "") {
         $asscObj = $g_BizSystem->GetObjectFactory()->GetObject($this->m_Association["AsscObjName"]);
         $this->m_Association["FieldRefVal"] = $asscObj->GetFieldValue($this->m_Association["FieldRef"]);
      }
      $this->m_DataSqlObj->AddAssociation($this->m_Association);

      $this->m_QuerySQL = $this->m_DataSqlObj->GetSqlStatement() . " ";

      //echo $this->m_QuerySQL."###<br>";
   }

   /**
    * BizDataObj::BuildUpdateSQL() - build update sql UPDATE table SET col1=val1, col2=val2 ... WHERE idcol1='id1' AND idcol2='id2'
    *
    * @return void
    **/
   protected function BuildUpdateSQL()
   {
      // generate column value pairs. ignore those whose inputValue=fieldValue
      $sqlArr = $this->m_BizRecord->GetSqlRecord();
      $sql = "";
      foreach($sqlArr as $col=>$val) {
         $fldname = $this->m_BizRecord->GetFieldByColumn($col);
         $fldobj = $this->GetField($fldname);
         if ($fldobj->IsLobField())  // take care of blob/clob type later
            continue;

         // ignore the column where old value is same as new value; set the column only if new value is diff than the old value
         if ($fldobj->m_OldValue == $fldobj->m_Value)
            continue;

         $_val = $fldobj->GetSqlValue();
         if ($_val===null || $_val === '') {
            if ($sql!="") $sql .= ", $col=''";
            else $sql .= "$col=''";
         } else {
            if ($sql!="") $sql .= ", $col=$_val";
            else $sql .= "$col=$_val";
         }
      }
      if ($sql == "") return false;
      $sql = "UPDATE " . $this->m_MainTable . " SET " . $sql;

      $whereStr = $this->m_BizRecord->GetKeySearchRule(true, true);  // use old value and column name
      $sql .= " WHERE " . $whereStr;
      return $sql;
   }

   /**
    * BizDataObj::BuildDeleteSQL() - build delete sql DELETE FROM table WHERE idcol1='id1' AND idcol2='id2'
    *
    * @return void
    **/
   protected function BuildDeleteSQL()
   {
      $sql = "DELETE FROM " . $this->m_MainTable;

      $whereStr = $this->m_BizRecord->GetKeySearchRule(false, true);  // use cur value and column name
      $sql .= " WHERE " . $whereStr;
      return $sql;
   }

   /**
    * BizDataObj::BuildInsertSQL() - build insert sql INSERT INTO table_name (column1, column2,...) VALUES (value1, value2,....)
    *
    * @return void
    **/
   protected function BuildInsertSQL()
   {
      // generate column value pairs.
      $sqlArr = $this->m_BizRecord->GetSqlRecord();

      // if Field Id has null value and Id is an identity type, remove the Id's column from the array
      if ($this->m_IdGeneration == "Identity") {
         $idColumn = $this->m_BizRecord->get("Id")->m_Column;
         unset($sqlArr[$idColumn]);
      }
      global $g_BizSystem;
      $dbinfo = $g_BizSystem->GetConfiguration()->GetDatabaseInfo($this->m_Database);
      $dbtype = $dbinfo["Driver"];

      $sql_col = ""; $sql_val = "";
      foreach($sqlArr as $col=>$val) {
         $fldname = $this->m_BizRecord->GetFieldByColumn($col);
         if ($this->GetField($fldname)->IsLobField())  // special value for blob/clob type
            $_val = $this->GetField($fldname)->GetInsertLobValue($dbtype);
         else
            $_val = $this->GetField($fldname)->GetSqlValue();

         if (!$_val || $_val == '') continue;

         $sql_col .= $col. ", ";
         $sql_val .= $_val. ", ";
      }
      $sql_col = substr($sql_col, 0, -2);
      $sql_val = substr($sql_val, 0, -2);
      $sql = "INSERT INTO  " . $this->m_MainTable . " (" . $sql_col . ") VALUES (" . $sql_val.")";
      return $sql;
   }

   /**
    * BizDataObj::GetActiveRecord() - get the active record
    * @return array - record array
    **/
   // todo: throw BDOException
   public function &GetActiveRecord()
   {
      if ($this->m_RecordId == null || $this->m_RecordId == "")
         return null;
      if ($this->m_CurrentRecord == null)
      {
         // query on $recordId
         $ok = $this->FetchRecords("[Id]='".$this->m_RecordId."'", $records, 1);
         if (count($records) == 1)
         {
            $this->m_CurrentRecord = $records[0];
            $this->m_RecordId = $this->m_CurrentRecord["Id"];
         }
         else
         $this->m_CurrentRecord = null;
      }

      return $this->m_CurrentRecord;
   }

   /**
    * BizDataObj::SetActiveRecordId() - set the active record according to the record id
    * @param $recordId record id
    * @return void
    **/
   public function SetActiveRecordId($recordId)
   {
      if ($this->m_RecordId != $recordId)
      {
         $this->m_RecordId = $recordId;
         $this->m_CurrentRecord = null;
      }
   }

   /**
    * BizDataObj::GotoPage() - go to given page of the query result set
    * if page < 0, go to last page. if page > total page, go to last page
    * @param $page page number
    * @return boolean true if page is between first and last page
    **/
   public function GotoPage($page)
   {
      $tgtPage = $page;
      if ($tgtPage == 0) $tgtPage = 1;
      else if ($tgtPage < 0) $tgtPage = $this->m_TotalPages;
      else if ($tgtPage > $this->m_TotalPages) $tgtPage = $this->m_TotalPages;
      if ($tgtPage == $this->m_CurrentPage)
         return false;
      $this->m_CurrentPage = $tgtPage;
      return true;
   }

   /**
    * BizDataObj::NextPage() - jump to next page
    *
    * @return boolean true if page is between current page and last page
    **/
   public function NextPage()
   {
      if ($this->m_CurrentPage >= $this->m_TotalPages) {
         $this->m_CurrentPage = $this->m_TotalPages;
         return false;
      }
      else {
         $this->m_CurrentPage++;
         return true;
      }
   }

   /**
    * BizDataObj::PrevPage() - jump to previous page
    *
    * @return boolean true if page is between first page and current page
    **/
   public function PrevPage()
   {
      if ($this->m_CurrentPage <= 1) {
         $this->m_CurrentPage = 1;
         return false;
      }
      else {
         $this->m_CurrentPage--;
         return true;
      }
   }

   private function RunDOTrigger($triggerType)
   {
      global $g_BizSystem;
      // locate the trigger metadata file BOName_Trigger.xml
      $triggerSvcName = $this->m_Name."_Trigger";
      $xmlFile = BizSystem::GetXmlFileWithPath ($triggerSvcName);
      if (!$xmlFile) return;

      $triggerSvc = $g_BizSystem->GetObjectFactory()->GetObject($triggerSvcName);
      //print_r($triggerSvc);
      if ($triggerSvc == null)
      return;
      // invoke trigger service ExecuteTrigger($triggerType, $currentRecord)
      $triggerSvc->Execute($this, $triggerType);
   }

   /**
    * BizDataObj::UpdateRecord() - Update record using given input record array
    *
    * @param array $recArr - associated array whose keys are field names of this BizDataObj
    * @param array $oldRec - associated array who is the old record field name / value pairs
    * @return boolean - if return false, the caller can call GetErrorMessage to get the error.
    **/
   // todo: throw BDOException
   public function UpdateRecord(&$recArr, &$oldRec=null)
   {
      if (!$this->CanUpdateRecord()) {
         $this->m_ErrorMessage = "You don't have permission to update this record.";
         return false;
      }

      if (!$oldRec)
         $oldRec = $this->GetActiveRecord();

      if (!$recArr["Id"])
         $recArr["Id"] = $this->GetFieldValue("Id");

      // save the old values
      $this->m_BizRecord->SaveOldRecord($oldRec);
      // set the new values
      $this->m_BizRecord->SetInputRecord($recArr);

      if (!$this->ValidateInput()) return false;

      $sql = $this->BuildUpdateSQL();
      if ($sql == false) return true;

      BizSystem::log(LOG_DEBUG, "DATAOBJ", "Update Sql = $sql");
      //echo "<br>update sql = $sql";

      $db = $this->GetDBConnection();

      try {
         $db->query($sql);
      }
      catch (Exception $e) {
         $this->m_ErrorMessage = "Error in query: " . $sql . ". " . $e->getMessage();
         return null;
      }

      $this->_PostUpdateLobFields($recArr);

      $this->m_CurrentRecord = $recArr;

      $this->_PostUpdateRecord($recArr);
      return true;
   }

   // check if the field is blob/clob type. In the lob case, update (lob value only)
   private function _PostUpdateLobFields(&$recArr)
   {
      $searchRule = $this->m_BizRecord->GetKeySearchRule(false, true);
      foreach ($this->m_BizRecord as $field)
      {
         if (isset($recArr[$field->m_Name]) && $field->IsLobField() && $field->m_Column != "") {
            $db = $this->GetDBConnection();
            $sql = "UPDATE " . $this->m_MainTable . " SET " . $field->m_Column . "=? WHERE $searchRule";
            $stmt = $db->prepare($sql);
            $fp = fopen($recArr[$field->m_Name], 'rb');
            $stmt->bindParam(1, $fp, PDO::PARAM_LOB);
            try {
               $stmt->execute();
            }
            catch (Exception $e) {
               $this->m_ErrorMessage = "Error in query: " . $sql . ". " . $e->getMessage();
               fclose($fp);
               return null;
            }
            fclose($fp);
            return true;
         }
      }
      return true;
   }

   private function _PostUpdateRecord($recArr)
   {
      // run DO trigger
      $this->RunDOTrigger("UPDATE");
   }

   /**
    * BizDataObj::NewRecord() - Create an empty record
    *
    * @return array - empty record array with default values
    **/
   public function NewRecord()
   {
      // auto generated SYSID
      $sysid = $this->GenerateId();
      if ($sysid === false)
         return false;
      $recArr = $this->m_BizRecord->GetEmptyRecordArr();

      // if association is 1-M, set the field (pointing to the column) value as the FieldRefVal
      if ($this->m_Association["Relationship"] == "1-M") {
         foreach ($this->m_BizRecord as $fld) {
            if ($fld->m_Column == $this->m_Association["Column"] && !$fld->m_Join) {
               $recArr[$fld->m_Name] = $this->m_Association["FieldRefVal"];
               break;
            }
         }
      }
      if ($sysid)
         $recArr["Id"] = $sysid;

      return $recArr;
   }

   /**
    * BizDataObj::GenerateId() - Generate Id according to the IdGeneration attribute
    *
    * @return mix - long or string or false
    **/
   protected function GenerateId($beforeInsert=true, $tableName=null, $idCloumnName=null)
   {
      if ($this->m_IdGeneration == 'None')
      return null;

      // Identity type id is generated after insert is done. If this method is called before insert, return null.
      if ($beforeInsert && $this->m_IdGeneration == 'Identity')
      return null;

      if (!$beforeInsert && $this->m_IdGeneration != 'Identity') {
         $this->m_ErrorMessage = "Unable to get ID for new inserted record: Id column is not Identiry type.";
         return false;
      }
      global $g_BizSystem;
      $genIdSvc = $g_BizSystem->GetService(GENID_SERVICE);
      $conn = $this->GetDBConnection();
      $dbinfo = $g_BizSystem->GetConfiguration()->GetDatabaseInfo($this->m_Database);
      $dbtype = $dbinfo["Driver"];
      $table = $tableName ? $tableName : $this->m_MainTable;
      $column = $idCloumnName ? $idCloumnName : $this->GetField("Id")->m_Column;

      try {
         $newid = $genIdSvc->GetNewID($this->m_IdGeneration, $conn, $dbtype, $table, $column);
      }
      catch (Exception $e) {
         $this->m_ErrorMessage = $e->getMessage();
         return false;
      }
      return $newid;
   }

   /**
    * BizDataObj::InsertRecord() - insert record using given input record array
    *
    * @param array $recArr - associated array whose keys are field names of this BizDataObj
    * @return boolean - if return false, the caller can call GetErrorMessage to get the error.
    **/
   // todo: throw BDOException
   public function InsertRecord(&$recArr, $exsitingRecord=false)
   {
      $this->m_BizRecord->SetInputRecord($recArr);
      if (!$this->ValidateInput()) return false;

      $sql = $this->BuildInsertSQL();
      BizSystem::log(LOG_DEBUG, "DATAOBJ", "Insert Sql = $sql");

      global $g_BizSystem;
      $db = $this->GetDBConnection();

      try {
         $db->query($sql);
      }
      catch (Exception $e) {
         $this->m_ErrorMessage = "Error in query: " . $sql . ". " . $e->getMessage();
         return false;
      }

      $new_id = $recArr["Id"];
      // if table use identity (auto-generated) id column
      if (!$recArr["Id"] || $recArr["Id"] == "") {
         $new_id = $this->GenerateId(false);
         if ($new_id === false) return false;
         $recArr["Id"] = $new_id;

	//AUTO GENERATED ID ISN'T being returned, maybe this will
         $sql = "select last_insert_id();";
         try {
	    $rs = $db->query($sql);
         }
         catch (Exception $e) {
            $this->m_ErrorMessage = "Error in query: " . $sql . ". " . $e->getMessage();
            return false;
         }
         if (($row = $rs->fetch()) != null) {
            $recArr['Id'] = $row[0];
         }
	 
         $this->m_BizRecord->SetInputRecord($recArr);
      }

      if ($this->_PostUpdateLobFields($recArr) === false) {
         $this->m_ErrorMessage = $db->ErrorMsg();
         return false;
      }

      $this->m_RecordId = $recArr["Id"];
      $this->m_CurrentRecord = $recArr;

      $this->_PostInsertRecord($recArr);

      return true;
   }

   private function _PostInsertRecord($recArr)
   {
      $this->m_TotalRecords++;
      $this->m_TotalPages = (int) ceil($this->m_TotalRecords / $this->m_PageRange);
      // do trigger
      $this->RunDOTrigger("INSERT");
   }

   /**
    * BizDataObj::DeleteRecord() - delete current record or delete the given input record
    *
    * @param array $recArr - associated array whose keys are field names of this BizDataObj
    * @return boolean - if return false, the caller can call GetErrorMessage to get the error.
    **/
   // todo: throw BDOException
   public function DeleteRecord($recArr=null, $cascadeObjNames=null)
   {
      if (!$this->CanDeleteRecord()) {
         $this->m_ErrorMessage = "You don't have permission to delete this record.";
         return false;
      }

      if ($recArr) $this->m_BizRecord->SetInputRecord($recArr);
      else $this->m_BizRecord->SetInputRecord($this->GetActiveRecord());

      // cascade delete
      $ok = $this->CascadeDelete($cascadeObjNames);
      if ($ok === false) {
         $this->m_ErrorMessage = "Cascade delete error: ".$this->GetErrorMessage();
         return false;
      }

      $sql = $this->BuildDeleteSQL();
      BizSystem::log(LOG_DEBUG, "DATAOBJ", "Delete Sql = $sql");

      $db = $this->GetDBConnection();

      try {
         $db->query($sql);
      }
      catch (Exception $e) {
         $this->m_ErrorMessage = "Error in query: " . $sql . ". " . $e->getMessage();
         return false;
      }

      $this->_PostDeleteRecord($this->m_BizRecord->GetKeyValue());
      return true;
   }

   private function _PostDeleteRecord($keyVal)
   {
      $this->m_TotalRecords--;
      $this->m_TotalPages = (int) ceil($this->m_TotalRecords / $this->m_PageRange);
      if ($this->m_CurrentPage > $this->m_TotalPages)
      $this->m_CurrentPage = $this->m_TotalPages;
      // do trigger
      $this->RunDOTrigger("DELETE");
   }

   protected function CascadeDelete($cascadeObjNames=null)
   {
      // if no obj name is given, scan all object refs
      if ($cascadeObjNames == null) {
         $cascadeObjNames = array();
         foreach ($this->m_ObjReferences as $objName=>$objRef)
         $cascadeObjNames[] = $objName;
      }

      $db = $this->GetDBConnection();
      foreach ($cascadeObjNames as $objName) {
         $objRef = $this->m_ObjReferences->get($objName);
         if (!$objRef->m_CascadeDelete)
         continue;
         if ($objRef->m_Relationship == "1-M" || $objRef->m_Relationship == "1-1") {
            $table = $objRef->m_Table;
            $column = $objRef->m_Column;
         }
         else if ($objRef->m_Relationship == "M-M") {
            $table = $objRef->m_XTable;
            $column = $objRef->m_XColumn1;
         }
         else continue;
         $fieldVal = $this->GetFieldValue($objRef->m_FieldRef);
         $sql = "DELETE FROM ".$table." WHERE ".$column."='".$fieldVal."'";
         if (!$fieldVal) {
            $this->m_ErrorMessage = "delete statement error, $sql";
            return false;
         }
         try {
            $db->query($sql);
         }
         catch (Exception $e) {
            $this->m_ErrorMessage = "Error in query: " . $sql . ". " . $e->getMessage();
            return false;
         }
      }
      return true;
   }

   /**
    * BizDataObj::ValidateInput() - Validate user input data and trigger error message and adjust BizField if invalid.
    *
    * @return boolean
    **/
   // todo: throw BDOException
   public function ValidateInput()
   {
      foreach($this->m_BizRecord->m_InputFields as $fld) {
         $bizFld = $this->m_BizRecord->get($fld);
         if ($bizFld->CheckRequired() === true &&
             ($bizFld->m_Value===null || $bizFld->m_Value === "")) {
            $this->m_ErrorMessage = BizSystem::GetMessage("ERROR", "BDO_ERROR_REQUIRED",array($fld));
            return false;
         }
         if ($bizFld->m_Value!==null && $bizFld->Validate() === false) {
            GLOBAL $g_BizSystem;
            $srvcObj = $g_BizSystem->GetService(VALIDATE_SERVICE);
            $this->m_ErrorMessage = $srvcObj->GetErrorMessage($bizFld->m_Validator, $bizFld->m_Name);
            
            if ($this->m_ErrorMessage == false) { //Couldn't get a clear error message so let's try this
               $this->m_ErrorMessage = BizSystem::GetMessage("ERROR", "BDO_ERROR_INVALID_INPUT",array($fld,$value,$bizFld->m_Validator));
            }
            return false;
         }
      }
      return true;
   }

   public function CanUpdateRecord()
   {
      if ($this->m_UpdateCondition)
      return Expression::EvaluateExpression($this->m_UpdateCondition,$this);
      return true;
   }

   public function CanDeleteRecord()
   {
      if ($this->m_DeleteCondition)
      return Expression::EvaluateExpression($this->m_DeleteCondition,$this);
      return true;
   }

   /**
    * BizDataObj::ConvertSqlExpresion() - replace [field name] in the SQL expression with table_alias.column
    *
    * @param string $sqlExpr - SQL expression supported by the database engine. The syntax is FUNC([FieldName1]...[FieldName2]...)
    * @return string real sql expression with column names
    **/
   public function ConvertSqlExpresion($sqlExpr)
   {
      $sqlstr = $sqlExpr;
      $startpos = 0;
      while (true) {
         $fieldname = substr_lr($sqlstr,"[","]",$startpos);
         if ($fieldname == "") break;
         else {
            $bizFld = $this->m_BizRecord->get($fieldname);
            $tableColumn = $this->m_DataSqlObj->GetTableColumn($bizFld->m_Join, $bizFld->m_Column);
            $sqlstr = str_replace("[$fieldname]", $tableColumn, $sqlstr);
            $startpos = strpos($sqlstr, '['); // Move startpos to the first [ (if it exists) in order to be detect by next itteration
         }
      }
      return $sqlstr;
   }


   // get all fields that belong to the same join of the input field
   public function GetJoinFields($joinDataObj)
   {
      // get the maintable of the joindataobj
      $joinTable = $joinDataObj->m_MainTable;
      $returnRecord = array();
      // find the proper join according to the maintable
      foreach ($this->m_TableJoins as $tableJoin) {
         if ($tableJoin->m_Table == $joinTable) {
            // populate the column-fieldvalue to columnRef-fieldvalue
            // get the field mapping to the column, then get the field value
            $joinFieldName = $joinDataObj->m_BizRecord->GetFieldByColumn($tableJoin->m_Column);
            if (!$joinFieldName) continue;
            $refFieldName = $this->m_BizRecord->GetFieldByColumn($tableJoin->m_ColumnRef);
            $returnRecord[$refFieldName] = $joinFieldName;
            // populate joinRecord's field to current record
            foreach ($this->m_BizRecord as $fld) {
               if ($fld->m_Join == $tableJoin->m_Name) {
                  // use join column to match joinRecord field's column
                  $jfldname = $joinDataObj->m_BizRecord->GetFieldByColumn($fld->m_Column);
                  $returnRecord[$fld->m_Name] = $jfldname;
               }
            }
            break;
         }
      }
      return $returnRecord;
   }

   /**
    * BizDataObj::JoinRecord() - pick the joined object's current record to the current record
    *
    * @param BizDataObj $joinDataObj
    * @return boolean
    */
   public function JoinRecord($joinDataObj, $joinName="")
   {
      // get the maintable of the joindataobj
      $joinTable = $joinDataObj->m_MainTable;
      $joinRecord = null;
      $returnRecord = array();
      // find the proper join according to join name and the maintable
      foreach ($this->m_TableJoins as $tableJoin) {
         if (($joinName == $tableJoin->m_Name || $joinName == "")
	          && $tableJoin->m_Table == $joinTable)
	      {
            // populate the column-fieldvalue to columnRef-fieldvalue
            // get the field mapping to the column, then get the field value
            $joinFieldName = $joinDataObj->m_BizRecord->GetFieldByColumn($tableJoin->m_Column);
            if (!$joinFieldName) continue;
            if (!$joinRecord)
            $joinRecord = $joinDataObj->GetActiveRecord();
            $refFieldName = $this->m_BizRecord->GetFieldByColumn($tableJoin->m_ColumnRef);
            $returnRecord[$refFieldName] = $joinRecord[$joinFieldName];
            // populate joinRecord's field to current record
            foreach ($this->m_BizRecord as $fld) {
               if ($fld->m_Join == $tableJoin->m_Name) {
                  // use join column to match joinRecord field's column
                  $jfldname = $joinDataObj->m_BizRecord->GetFieldByColumn($fld->m_Column);
                  $returnRecord[$fld->m_Name] = $joinRecord[$jfldname];
               }
            }
            break;
         }
      }
      // return a modified record with joined record data
      return $returnRecord;
   }

   /**
    * BizDataObj::AddRecord() - add a new record to current record set
    *
    * @param array $recArr
    * @param boolean &$bPrtObjUpdated
    * @return boolean
    */
   public function AddRecord($recArr, &$bPrtObjUpdated)
   {
      if ($this->m_Association["Relationship"] == "M-M")
      {
         $bPrtObjUpdated = false;
         return $this->AddRecord_MtoM($recArr);
      }
      else if ($this->m_Association["Relationship"] == "M-1" || $this->m_Association["Relationship"] == "1-1")
      {
         $bPrtObjUpdated = true;
         return $this->AddRecord_Mto1($recArr);
      }
      else
      {
         $this->m_ErrorMessage = "You cannot add a record in dataobj who doesn't have M-M or M-1 relationship with its parent object";
         return false;
      }
   }

   protected function AddRecord_MtoM($recArr)
   {
      // query on this object to get the corresponding record of this object.
      $searchRule = "[Id] = '".$recArr["Id"]."'";
      $recordList = array();
      if ($this->FetchRecords($searchRule, $recordList, 1) === false)
      return false;
      if (count($recordList) == 1) return true;

      // insert a record on XTable
      $db = $this->GetDBConnection();
      $xDataObj = isset($this->m_Association["XDataObj"]) ? $this->m_Association["XDataObj"] : null;
      $val1 = $this->m_Association["FieldRefVal"];
      $val2 = $recArr["Id"];
      if ($xDataObj) {   // get new record from XDataObj
      global $g_BizSystem;
      $xObj = $g_BizSystem->GetObjectFactory()->GetObject($xDataObj);
      $newRecArr = $xObj->NewRecord();
      // verify the main table of XDataobj is same as the XTable
      if ($xObj->m_MainTable != $this->m_Association["XTable"])
      {
         $this->m_ErrorMessage = "Unable to create a record in intersection table: XDataObj's main table is not same as XTable.";
         return false;
      }
      $fld1 = $xObj->GetFieldNameByColumn($this->m_Association["XColumn1"]);
      $newRecArr[$fld1] = $val1;
      $fld2 = $xObj->GetFieldNameByColumn($this->m_Association["XColumn2"]);
      $newRecArr[$fld2] = $val2;
      $ok = $xObj->InsertRecord($newRecArr);
      if ($ok === false) {
         $this->m_ErrorMessage = $xObj->GetErrorMessage();
         return false;
      }
      }
      else {
         $sql_col = "(".$this->m_Association["XColumn1"].",".$this->m_Association["XColumn2"].")";
         $sql_val = "('".$val1."','".$val2."')";
         $sql = "INSERT INTO " . $this->m_Association["XTable"] . " " . $sql_col . " VALUES " . $sql_val;
         try {
            $db->query($sql);
         }
         catch (Exception $e) {
            $this->m_ErrorMessage = "Error in query: " . $sql . ". " . $e->getMessage();
            return false;
         }
      }

      // add the record to object cache. requery on this object to get the corresponding record of this object.
      $searchRule = "[Id] = '".$recArr["Id"]."'";
      $recordList = array();
      if ($this->FetchRecords($searchRule, $recordList, 1) === false)
      return false;
      // insert the first record to the cache
      if (count($recordList) == 1)
      $this->_PostInsertRecord($recordList[0]);
      return true;
   }

   protected function AddRecord_Mto1($recArr)
   {
      global $g_BizSystem;
      // set the $recArr[Id] to the parent table foriegn key column
      // get parent/association dataobj
      $asscObj = $g_BizSystem->GetObjectFactory()->GetObject($this->m_Association["AsscObjName"]);
      // call parent dataobj's updateRecord
      $updateRecArr["Id"] = $asscObj->GetFieldValue("Id");
      $updateRecArr[$this->m_Association["FieldRef"]] = $recArr["Id"];
      $ok = $asscObj->UpdateRecord($updateRecArr);
      if ($ok == false)
      return false;
      // requery on this object
      $this->m_Association["FieldRefVal"] = $recArr["Id"];
      return $this->RunSearch();
   }

   /**
    * BizDataObj::RemoveRecord() - remove a record from current record set of current association relationship
    *
    * @param array $recArr
    * @param boolean &$bPrtObjUpdated
    * @return boolean
    */
   public function RemoveRecord($recArr, &$bPrtObjUpdated)
   {
      if ($this->m_Association["Relationship"] == "M-M")
      {
         $bPrtObjUpdated = false;
         return $this->RemoveRecord_MtoM($recArr);
      }
      else if ($this->m_Association["Relationship"] == "M-1" || $this->m_Association["Relationship"] == "1-1")
      {
         $bPrtObjUpdated = true;
         return $this->RemoveRecord_Mto1($recArr);
      }
      else
      {
         $this->m_ErrorMessage = "You cannot add a record in dataobj who doesn't have M-M or M-1 relationship with its parent object";
         return false;
      }
   }

   protected function RemoveRecord_MtoM($recArr)
   {
      // insert a record on XTable
      $db = $this->GetDBConnection();
      $where = $this->m_Association["XColumn1"] . "='" . $this->m_Association["FieldRefVal"] . "'";
      $where .= " AND " . $this->m_Association["XColumn2"] . "='" . $recArr["Id"] . "'";
      $sql = "DELETE FROM " . $this->m_Association["XTable"] . " WHERE " . $where;

      try {
         $db->query($sql);
      }
      catch (Exception $e) {
         $this->m_ErrorMessage = "Error in query: " . $sql . ". " . $e->getMessage();
         return false;
      }

      // delete the record from object cache
      $this->_PostDeleteRecord($recArr["Id"]);
      return true;
   }

   protected function RemoveRecord_Mto1($recArr)
   {
      global $g_BizSystem;
      // set the $recArr[Id] to the parent table foriegn key column
      // get parent/association dataobj
      $asscObj = $g_BizSystem->GetObjectFactory()->GetObject($this->m_Association["AsscObjName"]);
      // call parent dataobj's updateRecord
      $updateRecArr["Id"] = $asscObj->GetFieldValue("Id");
      $updateRecArr[$this->m_Association["FieldRef"]] = "";
      $ok = $asscObj->UpdateRecord($updateRecArr);
      if ($ok == false)
         return false;
      // requery on this object
      $this->m_Association["FieldRefVal"] = "";
      return $this->RunSearch();
   }
}

/**
 * TableJoin class - TableJoin defines the table join used in BizDataObj
 *
 * @package BizDataObj
 */
class TableJoin extends MetaObject
{
   public $m_Table;
   public $m_Column;
   public $m_JoinRef;
   public $m_ColumnRef;
   public $m_JoinType;

   function __construct(&$xmlArr, $bizObj)
   {
      $this->m_Name = isset($xmlArr["ATTRIBUTES"]["NAME"]) ? $xmlArr["ATTRIBUTES"]["NAME"] : null;
      $this->m_Package = $bizObj->m_Package;
      $this->m_Description= isset($xmlArr["ATTRIBUTES"]["DESCRIPTION"]) ? $xmlArr["ATTRIBUTES"]["DESCRIPTION"] : null;
      $this->m_Table = isset($xmlArr["ATTRIBUTES"]["TABLE"]) ? $xmlArr["ATTRIBUTES"]["TABLE"] : null;
      $this->m_Column = isset($xmlArr["ATTRIBUTES"]["COLUMN"]) ? $xmlArr["ATTRIBUTES"]["COLUMN"] : null;
      $this->m_JoinRef = isset($xmlArr["ATTRIBUTES"]["JOINREF"]) ? $xmlArr["ATTRIBUTES"]["JOINREF"] : null;
      $this->m_ColumnRef = isset($xmlArr["ATTRIBUTES"]["COLUMNREF"]) ? $xmlArr["ATTRIBUTES"]["COLUMNREF"] : null;
      $this->m_JoinType = isset($xmlArr["ATTRIBUTES"]["JOINTYPE"]) ? $xmlArr["ATTRIBUTES"]["JOINTYPE"] : null;

      $this->m_BizObjName = $this->PrefixPackage($this->m_BizObjName);
   }
}

/**
 * ObjReference class - ObjReference defines the object reference of a BizDataObj
 *
 * @package BizDataObj
 */
class ObjReference extends MetaObject
{
   public $m_Relationship;
   public $m_Table;
   public $m_Column;
   public $m_FieldRef;
   public $m_XTable;
   public $m_XColumn1;
   public $m_XColumn2;
   public $m_XKeyColumn;   // may not be used any more due to XDataObj
   public $m_XDataObj;
   public $m_CascadeDelete=false;
   //public $m_Association;

   function __construct(&$xmlArr, $bizObj)
   {
      $this->m_Name = isset($xmlArr["ATTRIBUTES"]["NAME"]) ? $xmlArr["ATTRIBUTES"]["NAME"] : null;
      $this->m_Package = $bizObj->m_Package;
      $this->m_Description= isset($xmlArr["ATTRIBUTES"]["DESCRIPTION"]) ? $xmlArr["ATTRIBUTES"]["DESCRIPTION"] : null;
      $this->m_Relationship = isset($xmlArr["ATTRIBUTES"]["RELATIONSHIP"]) ? $xmlArr["ATTRIBUTES"]["RELATIONSHIP"] : null;
      $this->m_Table = isset($xmlArr["ATTRIBUTES"]["TABLE"]) ? $xmlArr["ATTRIBUTES"]["TABLE"] : null;
      $this->m_Column = isset($xmlArr["ATTRIBUTES"]["COLUMN"]) ? $xmlArr["ATTRIBUTES"]["COLUMN"] : null;
      $this->m_FieldRef = isset($xmlArr["ATTRIBUTES"]["FIELDREF"]) ? $xmlArr["ATTRIBUTES"]["FIELDREF"] : null;
      $this->m_CascadeDelete = (isset($xmlArr["ATTRIBUTES"]["CASCADEDELETE"]) && $xmlArr["ATTRIBUTES"]["CASCADEDELETE"] == "Y");
      if ($this->m_Relationship == "M-M") {
         $this->m_XTable = isset($xmlArr["ATTRIBUTES"]["XTABLE"]) ? $xmlArr["ATTRIBUTES"]["XTABLE"] : null;
         $this->m_XColumn1 = isset($xmlArr["ATTRIBUTES"]["XCOLUMN1"]) ? $xmlArr["ATTRIBUTES"]["XCOLUMN1"] : null;
         $this->m_XColumn2 = isset($xmlArr["ATTRIBUTES"]["XCOLUMN2"]) ? $xmlArr["ATTRIBUTES"]["XCOLUMN2"] : null;
         $this->m_XKeyColumn = isset($xmlArr["ATTRIBUTES"]["XKEYCOLUMN"]) ? $xmlArr["ATTRIBUTES"]["XKEYCOLUMN"] : null;
         $this->m_XDataObj = isset($xmlArr["ATTRIBUTES"]["XDATAOBJ"]) ? $xmlArr["ATTRIBUTES"]["XDATAOBJ"] : null;
         $this->m_XDataObj = $this->PrefixPackage($this->m_XDataObj);
      }
      //$this->m_Association = @$xmlArr["ATTRIBUTES"]["ASSOCIATION"];

      $this->m_Name = $this->PrefixPackage($this->m_Name);
   }
}

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
            $fld = $this->GetFieldByColumn($col);
            $this->m_KeyFldColMap[$fld] = $col;
         }
      }
   }

   public function GetFieldByColumn($column)
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
      foreach($inputArr as $key=>$value) {   // if allow changing key field, need to keep the old value which is also useful for audit trail
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

   /**
    * BizRecord::GetSqlRecord() - Get sql record as array which is a Column-Value pairs
    *
    * @return array
    **/
   final public function GetSqlRecord()
   {
      $sqlArr = array();
      foreach ($this->m_InputFields as $key) {
         // ignore the composite key Id field
         if ($key == "Id" && count($this->m_KeyFldColMap) > 1)
         continue;
         $fld = $this->m_var[$key];
         // do not consider joined columns
         if ($fld->m_Column && !$fld->m_Join) {
            // replace ' with \' -> not needed since addslashes() is called before
            $sqlArr[$fld->m_Column] = $fld->m_Value;
         }
      }
      return $sqlArr;
   }
}


/**
 * substr_lr() - help function. Get the sub string whose left and right boundary character is $left and $right.
 * The search is in $str, starting from position of $startpos. If $findfirst is true, $left must be the charater on the $startpos.
 *
 * @return string
 **/
function substr_lr(&$str,$left,$right,&$startpos,$findfirst=false)
{
   $pos0 = strpos($str, $left, $startpos);
   if ($pos0 === false) return false;
   $tmp = trim(substr($str,$startpos,$pos0-$startpos));
   if ($findfirst && $tmp!="") return false;

   $posleft = $pos0+strlen($left);
   while(true) {
      $pos1 = strpos($str, $right, $posleft);
      if ($pos1 === false) {
         if (trim($right)=="") {
            $pos1 = strlen($str); // if right is whitespace
            break;
         }
         else return false;
      }
      else {   // avoid \$right is found
      if (substr($str,$pos1-1,1) == "\\")  $posleft = $pos1+1;
      else break;
      }
   }

   $startpos = $pos1 + strlen($right);
   $retStr = substr($str, $pos0 + strlen($left), $pos1-$pos0-strlen($left));
   return $retStr;
}

?>
