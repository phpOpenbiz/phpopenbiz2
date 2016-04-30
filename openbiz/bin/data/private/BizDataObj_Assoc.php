<?php
/**
 * BizDataObj_Assoc class - class BizDataObj_Assoc takes care of add and remove record according to data object association
 *
 * @package BizDataObj
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access private
 */
class BizDataObj_Assoc
{
   /**
    * BizDataObj_Assoc::AddRecord() - add a new record to current record set
    *
    * @param object $dataobj - the instance of BizDataObj
    * @param array $recArr - the record array to be added in the data object
    * @param boolean &$bPrtObjUpdated - flag that indicates if the parent form needs to be updated
    * @return boolean
    */
   public static function AddRecord($dataobj, $recArr, &$bPrtObjUpdated)
   {
      if ($dataobj->m_Association["Relationship"] == "M-M")
      {
         $bPrtObjUpdated = false;
         return self::AddRecord_MtoM($dataobj, $recArr);
      }
      else if ($dataobj->m_Association["Relationship"] == "M-1" || $dataobj->m_Association["Relationship"] == "1-1")
      {
         $bPrtObjUpdated = true;
         return self::AddRecord_Mto1($dataobj, $recArr);
      }
      else
      {
         throw new BDOException("You cannot add a record in dataobj who doesn't have M-M or M-1 relationship with its parent object");
         return false;
      }
   }

   private static function AddRecord_MtoM($dataobj, $recArr)
   {
      // query on this object to get the corresponding record of this object.
      $searchRule = "[Id] = '".$recArr["Id"]."'";
      $recordList = $dataobj->DirectFetch($searchRule, 1);
      if (count($recordList) == 1) return true;

      // insert a record on XTable
      $db = $dataobj->GetDBConnection();
      $xDataObj = isset($dataobj->m_Association["XDataObj"]) ? $dataobj->m_Association["XDataObj"] : null;
      $val1 = $dataobj->m_Association["FieldRefVal"];
      $val2 = $recArr["Id"];
      if ($xDataObj) {   // get new record from XDataObj
         global $g_BizSystem;
         $xObj = $g_BizSystem->GetObjectFactory()->GetObject($xDataObj);
         $newRecArr = $xObj->NewRecord();
         // verify the main table of XDataobj is same as the XTable
         if ($xObj->m_MainTable != $dataobj->m_Association["XTable"])
         {
            throw new BDOException("Unable to create a record in intersection table: XDataObj's main table is not same as XTable.");
            return false;
         }
         $fld1 = $xObj->GetFieldNameByColumn($dataobj->m_Association["XColumn1"]);
         $newRecArr[$fld1] = $val1;
         $fld2 = $xObj->GetFieldNameByColumn($dataobj->m_Association["XColumn2"]);
         $newRecArr[$fld2] = $val2;
         $ok = $xObj->InsertRecord($newRecArr);
         if ($ok === false) {
            throw new BDOException($xObj->GetErrorMessage());
            return false;
         }
      }
      else {
         $sql_col = "(".$dataobj->m_Association["XColumn1"].",".$dataobj->m_Association["XColumn2"].")";
         $sql_val = "('".$val1."','".$val2."')";
         $sql = "INSERT INTO " . $dataobj->m_Association["XTable"] . " " . $sql_col . " VALUES " . $sql_val;
         try {
            $db->query($sql);
         }
         catch (Exception $e) {
            throw new BDOException("Error in query: " . $sql . ". " . $e->getMessage());
            return false;
         }
      }

      // add the record to object cache. requery on this object to get the corresponding record of this object.
      $searchRule = "[Id] = '".$recArr["Id"]."'";
      $recordList = $dataobj->DirectFetch($searchRule, 1);
      if (count($recordList) == 0)
         return false;
      return true;
   }

   private static function AddRecord_Mto1($dataobj, $recArr)
   {
      global $g_BizSystem;
      // set the $recArr[Id] to the parent table foriegn key column
      // get parent/association dataobj
      $asscObj = $g_BizSystem->GetObjectFactory()->GetObject($dataobj->m_Association["AsscObjName"]);
      // call parent dataobj's updateRecord
      $updateRecArr["Id"] = $asscObj->GetFieldValue("Id");
      $updateRecArr[$dataobj->m_Association["FieldRef"]] = $recArr["Id"];
      $ok = $asscObj->UpdateRecord($updateRecArr);
      if ($ok == false)
         return false;
      // requery on this object
      $dataobj->m_Association["FieldRefVal"] = $recArr["Id"];
      return $dataobj->RunSearch();
   }

   /**
    * BizDataObj_Assoc::RemoveRecord() - remove a record from current record set of current association relationship
    *
    * @param object $dataobj - the instance of BizDataObj
    * @param array $recArr - the record array to be removed from the data object
    * @param boolean &$bPrtObjUpdated - flag that indicates if the parent form needs to be updated
    * @return boolean
    */
   public static function RemoveRecord($dataobj, $recArr, &$bPrtObjUpdated)
   {
      if ($dataobj->m_Association["Relationship"] == "M-M")
      {
         $bPrtObjUpdated = false;
         return self::RemoveRecord_MtoM($dataobj, $recArr);
      }
      else if ($dataobj->m_Association["Relationship"] == "M-1" || $dataobj->m_Association["Relationship"] == "1-1")
      {
         $bPrtObjUpdated = true;
         return self::RemoveRecord_Mto1($dataobj, $recArr);
      }
      else
      {
         throw new BDOException("You cannot add a record in dataobj who doesn't have M-M or M-1 relationship with its parent object");
         return false;
      }
   }

   private static function RemoveRecord_MtoM($dataobj, $recArr)
   {
      // delete a record on XTable
      $db = $dataobj->GetDBConnection();
      
      //TODO: delete using XDataObj if XDataObj is defined
      
      $where = $dataobj->m_Association["XColumn1"] . "='" . $dataobj->m_Association["FieldRefVal"] . "'";
      $where .= " AND " . $dataobj->m_Association["XColumn2"] . "='" . $recArr["Id"] . "'";
      $sql = "DELETE FROM " . $dataobj->m_Association["XTable"] . " WHERE " . $where;

      try {
         $db->query($sql);
      }
      catch (Exception $e) {
         throw new BDOException("Error in query: " . $sql . ". " . $e->getMessage());
         return false;
      }
      return true;
   }

   private static function RemoveRecord_Mto1($dataobj, $recArr)
   {
      global $g_BizSystem;
      // set the $recArr[Id] to the parent table foriegn key column
      // get parent/association dataobj
      $asscObj = $g_BizSystem->GetObjectFactory()->GetObject($dataobj->m_Association["AsscObjName"]);
      // call parent dataobj's updateRecord
      $updateRecArr["Id"] = $asscObj->GetFieldValue("Id");
      $updateRecArr[$dataobj->m_Association["FieldRef"]] = "";
      $ok = $asscObj->UpdateRecord($updateRecArr);
      if ($ok == false)
         return false;
      // requery on this object
      $dataobj->m_Association["FieldRefVal"] = "";
      return $dataobj->RunSearch();
   }
   
}