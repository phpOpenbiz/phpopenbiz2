<?PHP

/**
 * BizDataObj class - class BizDataObj is the base class of all data object classes
 *
 * @package BizDataObj
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */

include_once(OPENBIZ_BIN.'data/BizDataObj_Abstract.php');

//class BizDataObj extends BizDataObj_Abstract
class BizDataObj extends BizDataObj_Lite
{
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
    * BizDataObj::UpdateRecord() - Update record using given input record array
    *
    * @param array $recArr - associated array whose keys are field names of this BizDataObj
    * @param array $oldRec - associated array who is the old record field name / value pairs
    * @return boolean - if return false, the caller can call GetErrorMessage to get the error.
    **/
   // todo: throw BDOException
   public function UpdateRecord($recArr, $oldRec=null)
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

      $sql = BizDataObj_SQLHelper::BuildUpdateSQL($this);
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
      
      // TODO: allow update main table and updatable joined tables
      /*foreach ($this->m_TableJoins as $tableJoin)
      {
         // if join's onsavedataobj is no null
         if ($tableJoin->m_OnSaveDataObj) {
            // get the input for this join
            $rec_join = $this->m_BizRecord->GetJoinInputRecord($tableJoin->m_Name);
            // use daterecord of onsavedataobj to update join table
         }
      }*/
      
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
   public function InsertRecord($recArr)
   {
      $this->m_BizRecord->SetInputRecord($recArr);
      if (!$this->ValidateInput()) return false;

      $sql = BizDataObj_SQLHelper::BuildInsertSQL($this);
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
	 
         $this->m_BizRecord->SetInputRecord($recArr);
      }

      if ($this->_PostUpdateLobFields($recArr) === false) {
         $this->m_ErrorMessage = $db->ErrorMsg();
         return false;
      }

      $this->m_RecordId = $recArr["Id"];
      $this->m_CurrentRecord = $recArr;

      $this->_PostInsertRecord($recArr);
      
      // TODO: allow update main table and updatable joined tables
      /*foreach ($this->m_TableJoins as $tableJoin)
      {
         // if join's onsavedataobj is no null
         // get the input for this join
         // call onsavedataobj->updaterecord($recadd_join)
      }*/

      return true;
   }

   private function _PostInsertRecord($recArr)
   {
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
   public function DeleteRecord($recArr)
   {
      if (!$this->CanDeleteRecord()) {
         $this->m_ErrorMessage = "You don't have permission to delete this record.";
         return false;
      }

      if ($recArr) $this->m_BizRecord->SetInputRecord($recArr);
      else $this->m_BizRecord->SetInputRecord($this->GetActiveRecord());

      // cascade delete
      $ok = $this->CascadeDelete();
      if ($ok === false) {
         $this->m_ErrorMessage = "Cascade delete error: ".$this->GetErrorMessage();
         throw new BDOException($this->m_ErrorMessage);
         return false;
      }

      $sql = BizDataObj_SQLHelper::BuildDeleteSQL($this);
      BizSystem::log(LOG_DEBUG, "DATAOBJ", "Delete Sql = $sql");

      $db = $this->GetDBConnection();

      try {
         $db->query($sql);
      }
      catch (Exception $e) {
         $this->m_ErrorMessage = "Error in query: " . $sql . ". " . $e->getMessage();
         throw new BDOException($this->m_ErrorMessage);
         return false;
      }

      $this->_PostDeleteRecord($this->m_BizRecord->GetKeyValue());
      return true;
   }

   private function _PostDeleteRecord($keyVal)
   {
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

   public function GetOnAuditFields() {
      $fldlist = array();
      foreach ($this->m_BizRecord as $fld) {
         if ($fld->m_OnAudit)
         $fldlist[] = $fld;
      }
      return $fldlist;
   }
   
   private function RunDOTrigger($triggerType)
   {
      global $g_BizSystem;
      // locate the trigger metadata file BOName_Trigger.xml
      $triggerSvcName = $this->m_Name."_Trigger";
      $xmlFile = BizSystem::GetXmlFileWithPath ($triggerSvcName);
      if (!$xmlFile) return;

      $triggerSvc = $g_BizSystem->GetObjectFactory()->GetObject($triggerSvcName);
      if ($triggerSvc == null)
         return;
      // invoke trigger service ExecuteTrigger($triggerType, $currentRecord)
      $triggerSvc->Execute($this, $triggerType);
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
            $joinFieldName = $joinDataObj->m_BizRecord->GetFieldByColumn($tableJoin->m_Column); // joined-main table 
            if (!$joinFieldName) continue;
            $refFieldName = $this->m_BizRecord->GetFieldByColumn($tableJoin->m_ColumnRef); // join table 
            $returnRecord[$refFieldName] = $joinFieldName;
            // populate joinRecord's field to current record
            foreach ($this->m_BizRecord as $fld) {
               if ($fld->m_Join == $tableJoin->m_Name) {
                  // use join column to match joinRecord field's column
                  $jfldname = $joinDataObj->m_BizRecord->GetFieldByColumn($fld->m_Column); // joined-main table 
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
            $joinFieldName = $joinDataObj->m_BizRecord->GetFieldByColumn($tableJoin->m_Column); // joined-main table
            if (!$joinFieldName) continue;
            if (!$joinRecord)
            $joinRecord = $joinDataObj->GetActiveRecord();
            $refFieldName = $this->m_BizRecord->GetFieldByColumn($tableJoin->m_ColumnRef); // join table 
            $returnRecord[$refFieldName] = $joinRecord[$joinFieldName];
            // populate joinRecord's field to current record
            foreach ($this->m_BizRecord as $fld) {
               if ($fld->m_Join == $tableJoin->m_Name) {
                  // use join column to match joinRecord field's column
                  $jfldname = $joinDataObj->m_BizRecord->GetFieldByColumn($fld->m_Column); // joined-main table 
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
      return BizDataObj_Assoc::AddRecord($this, $recArr, $bPrtObjUpdated);
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
      return BizDataObj_Assoc::RemoveRecord($this, $recArr, $bPrtObjUpdated);
   }

}

?>