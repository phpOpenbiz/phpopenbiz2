<?php
/**
 * BizDataObj_Lite class - contains data object readonly functions
 *
 * @package BizDataObj
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */

include_once(OPENBIZ_BIN.'data/BizDataObj_Abstract.php');

// constant defination
define('CK_CONNECTOR', "#");  // composite key connector character

class BizDataObj_Lite extends BizDataObj_Abstract 
{
   protected $m_RecordId = null;
   protected $m_CurrentRecord = null;
   protected $m_ErrorMessage = "";
   
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
      $sessCtxt->GetObjVar($this->m_Name, "RecordId", $this->m_RecordId);
      $sessCtxt->GetObjVar($this->m_Name, "SearchRule", $this->m_SearchRule);
      $sessCtxt->GetObjVar($this->m_Name, "SortRule", $this->m_SortRule);
      $sessCtxt->GetObjVar($this->m_Name, "OtherSqlRule", $this->m_OtherSQLRule);
      $sessCtxt->GetObjVar($this->m_Name, "Association", $this->m_Association);
// ??? need to save current record in session ???
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
      $sessCtxt->SetObjVar($this->m_Name, "RecordId", $this->m_RecordId);
      $sessCtxt->SetObjVar($this->m_Name, "SearchRule", $this->m_SearchRule);
      $sessCtxt->SetObjVar($this->m_Name, "SortRule", $this->m_SortRule);
      $sessCtxt->SetObjVar($this->m_Name, "OtherSqlRule", $this->m_OtherSQLRule);
      $sessCtxt->SetObjVar($this->m_Name, "Association", $this->m_Association);
   }

   public function GetErrorMessage() { return $this->m_ErrorMessage; }

   public function GetField($fldname) 
   { 
      return $this->m_BizRecord->get($fldname); 
   }

   // Todo_Maynotuse: since column and join column can have the same name
   public function GetFieldNameByColumn($column) 
   { 
      return $this->m_BizRecord->GetFieldByColumn($column); // main table column
   }

   public function GetFieldValue($fldname, $getActiveOnly=false) {
      $rec = $this->GetActiveRecord();
      return $rec[$fldname];
      //return $this->m_BizRecord->get($fldname)->GetValue();
   }

   /**
	* BizDataObj:SetCurrentRecord() - Set m_CurrentRecord to the passed in parameter
	*
	* @param array $currentRecord
	* @return void
	**/
   public function SetActiveRecord($currentRecord)
   {
      $this->m_CurrentRecord = $currentRecord;
      $this->m_RecordId = $this->m_CurrentRecord['Id'];
   }

   /**
    * BizDataObj::GetActiveRecord() - get the active record
    * @return array - record array
    **/
   // todo: throw BDOException
   public function GetActiveRecord()
   {
      if ($this->m_RecordId == null || $this->m_RecordId == "")
         return null;
      if ($this->m_CurrentRecord == null)
      {
         // query on $recordId
         $records = $this->DirectFetch("[Id]='".$this->m_RecordId."'", 1);
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
   
   /* Fetches SQL result rows as a sequential array according the query rules set before.
    */
   public function Fetch()
   {
      $resultRecords = array();
      $resultSet = $this->_run_search($this->m_Limit);  // regular search or page search
      if ($resultSet !== null)
      {
         while ($recArray = $this->_fetch_record($resultSet))
         {
            $resultRecords[] = $recArray;
         }
      }
      else
        return null;

      return $resultRecords;
   }
   
   /* Fetches SQL result rows as a sequential array without using query rules set before.
    */
   public function DirectFetch($searchRule="", $count=-1, $offset=0)
   {
      $curRecord = $this->m_CurrentRecord;
      $recId = $this->m_RecordId;
      $this->m_CurrentRecord = null;
      
      $oldSearchRule = $this->m_SearchRule;
      $this->ClearSearchRule();
      $this->SetSearchRule($searchRule);

      $oldSortRule = $this->m_SortRule;
      $this->ClearSortRule();

      $limit = ($count == -1) ? null : array('count'=>$count, 'offset'=>$offset);
      
      $resultRecords = array();
      $resultSet = $this->_run_search($limit);
      if ($resultSet !== null)
      {
         while ($recArray = $this->_fetch_record($resultSet))
         {
            $resultRecords[] = $recArray;
         }
      }
      
      $this->m_SortRule = $oldSortRule;
      $this->m_SearchRule = $oldSearchRule;
      $this->m_CurrentRecord = $curRecord;
      $this->m_RecordId = $recId;
      
      return $resultRecords;
   }
   
   public function FetchById($id)
   {
      $searchRule = "[Id] = '$id'";
      $recordList = $this->DirectFetch($searchRule, 1); 
      if (count($recordList)==1)
         return $recordList[0];
      else
         return null;
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
   public function FetchRecords($searchRule, &$resultRecords, $count=-1, $offset=0, $clearSearchRule=true, $noAssociation=false)
   {
      if ($count == 0) return;
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
      $limit = ($count == -1) ? null : array('count'=>$count, 'offset'=>$offset);
      
      $resultRecords = array();
      $resultSet = $this->_run_search($limit);
      if ($resultSet !== null)
      {
         while ($recArray = $this->_fetch_record($resultSet))
         {
            $resultRecords[] = $recArray;
         }
      }
      if ($noAssociation)
         $this->m_Association = $oldAssociation;
      $this->m_SearchRule = $oldSearchRule;
      $this->m_CurrentRecord = $curRecord;
      $this->m_RecordId = $recId;
      return true;
   }
   
   /*
    * Do the search query and return results set as PDOStatement
    */
   public function Find()
   {
      return $this->_run_search($this->m_Limit);
   }
   
   public function Count()
   {
      // get database connection
      $db = $this->GetDBConnection();
      $querySQL = BizDataObj_SQLHelper::BuildQuerySQL($this);
      return $this->_get_number_records($db, $querySQL);
   }
   
   /**
    * BizDataObj::Query() - run query with current search rule and returns PDO statement
    * @param array $limit - if limit is not null, do the limit search
    * @return PDOStatement
    */
   protected function _run_search($limit=null)
   {
      $querySQL = BizDataObj_SQLHelper::BuildQuerySQL($this);
      BizSystem::log(LOG_DEBUG, "DATAOBJ", "Query Sql = ".$querySQL);

      // get database connection
      $db = $this->GetDBConnection();

      if ($limit && count($limit) > 0 && $limit['count'] > 0)
         $sql = $db->limit($querySQL, $limit['count'], $limit['offset']);
      else
         $sql = $querySQL;

         try {
         $resultSet = $db->query($sql);
      }
      catch (Exception $e) {
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
         $this->m_CurrentRecord = $this->m_BizRecord->ConvertSqlArrToRecArr($sqlArr);
         $this->m_CurrentRecord = $this->m_BizRecord->GetRecordArr($sqlArr);
         $this->m_RecordId = $this->m_CurrentRecord["Id"];
      }
      else
         return null;
      return $this->m_CurrentRecord;
   }

   public function ValidateInput() {}
   
   public function NewRecord() {}
   
   public function InsertRecord($recArr) {}
   
   public function UpdateRecord($recArr, $oldRec=null) {}
   
   public function DeleteRecord($recArr) {}
}

?>