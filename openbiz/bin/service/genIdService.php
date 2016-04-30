<?php
/**
 * @package PluginService
 */
/**
 * genIdService - 
 * class genIdService is the plug-in service of generating ID for new record
 * 
 * @package PluginService
 * @author rocky swen 
 * @copyright Copyright (c) 2003
 * @version $Id: logService.php,v 1.1 2006/04/07 08:01:29 rockys Exp $
 * @access public 
 */
class genIdService
{
   public function __construct() {}

   public function GetNewID($idGeneration, $conn, $dbtype, $table=null, $column=null)
   {
      try {
         if (!$idGeneration || $idGeneration == 'Openbiz') {
            $newid = $this->GetNewSYSID($conn, $table, true);
         }
         else if ($idGeneration == 'Identity') {
            $newid = $this->GetNewIdentity($conn, $dbtype, $table, $column);
         }
         else if (strpos($idGeneration, 'Sequence:')===0) {
            $seqname = substr($idGeneration, 9);
            $newid = $this->GetNewSequence($conn, $dbtype, $seqname);
         }
         else if ($idGeneration == 'GUID') {
            $newid = $this->GetNewGUID($conn, $dbtype, $table, $column);
         }
         else if ($idGeneration == 'UUID') {
            $newid = $this->GetNewUUID();
         }
         else {
            throw new Exception("Error in generating new ID: unsupported generation type.");
         }
      }
      catch (Exception $e) {
         throw new Exception($e->getMessage());
      }
      return $newid;
   }
   
      /**
    * genIdService::GetNewSYSID()
    * Get a new SYSID from the id_table. You can get SYSID for a table with prefix and base converting
    * 
    * @param ADOConnection $conn
    * @param string $tablename
    * @param boolean $includePrefix
    * @param integer $base to encode
    * @return string
    **/
   protected function GetNewSYSID($conn, $tablename, $includePrefix=false, $base=-1)
   {
      $maxRetry = 10;
      // try to update the table idbody column
      for ($try=1; $try <= $maxRetry; $try++)
      {
         $sql = "SELECT * FROM ob_sysids WHERE TABLENAME='$tablename'";
         try {
            $rs = $conn->query($sql);
         }
         catch (Exception $e) {
            throw new Exception("Error in query: " . $sql . ". " . $e->getMessage());
            return false;
         }
         $row = $rs->fetch();
         unset($rs);
         list($tblname, $prefix, $idbody) = $row;
         if (!$row)
            throw new Exception("Error in generating new system id: '$tablename' is not in ob_sysids table.");
         if ($row) {
            if ($idbody == null && $prefix) // idbody is empty, return false
              throw new Exception("Error in generating new system id: ob_sysids table does not have a valid sequence for '$tablename'.");
         }
         // try to update the table idbody column
         $sql = "UPDATE ob_sysids SET IDBODY=IDBODY+1 WHERE TABLENAME='$tablename' AND IDBODY=$idbody";
         try {
            $rs = $conn->query($sql);
         }
         catch (Exception $e) {
            throw new Exception("Error in query: " . $sql . ". " . $e->getMessage());
            return false;
         }
         if ($rs->rowCount() > 0) {
				$idbody += 1;
				break;
         }
      }
      if ($try <= $maxRetry) {
         if ($base>=2 && $base<=36)
            $idbody = dec2base($idbody, $base);
         if ($includePrefix)
            return $prefix."_".$idbody;
         return $idbody;
      }
      else 
         throw new Exception("Error in generating new system id: unable to get a valid id.");
      return false;
   }
   
   // ID is generated after insert sql is executed
   protected function GetNewIdentity($conn, $dbtype, $table=null, $column=null)
   {
      $dbtype = strtoupper($dbtype);
      if ($dbtype == 'mysql' || $dbtype == 'PDO_MYSQL') {
         $sql = "select last_insert_id()";
      }
      else if ($dbtype == 'mssql' || $dbtype == 'PDO_DBLIB')
         $sql = "select @@identity";
      else if ($dbtype == 'sybase' || $dbtype == 'PDO_DBLIB')
         $sql = "select @@identity";
      else if ($dbtype == 'db2' || $dbtype == 'PDO_ODBC')
         $sql = "values identity_val_local()";
      else if ($dbtype == 'postgresql' || $dbtype == 'PDO_PGSQL')
         $sql = "select currval('$table_$column_seq')";
      else 
         throw new Exception("Error in generating new identity: unsupported database type.");
      // execute sql to get the id
      $newid = $this->GetIdWithSql($conn, $sql);
      if ($newid === false)
         throw new Exception("Error in generating new identity: unable to get a valid id.");
      return $newid;
   }

   // ID is generated before executing insert sql
   protected function GetNewSequence($conn, $dbtype, $sequenceName)
   {
      $dbtype = strtoupper($dbtype);
      if ($dbtype == 'oracle' || $dbtype == 'oci8' || $dbtype == 'PDO_OCI')
         $sql = "select $sequenceName.nextval from dual";
      else if ($dbtype == 'db2' || $dbtype == 'PDO_ODBC')
         $sql = "values nextval for $sequenceName";
      else if ($dbtype == 'postgresql' || $dbtype == 'PDO_PGSQL')
         $sql = "select nextval('$sequenceName')";
      else if ($dbtype == 'informix' || $dbtype == 'PDO_INFORMIX')
         "select $sequenceName.nextval from systables where tabid=1";
      else 
         throw new Exception("Error in generating new sequence: unsupported database type.");
      // execute sql to get the id
      $newid = $this->GetIdWithSql($conn, $sql);
      if ($newid === false)
         throw new Exception("Error in generating new sequence: unable to get a valid id.");
      return $newid;
   }

   // ID is generated before executing insert sql
   protected function GetNewGUID($conn, $dbtype, $table=null, $column=null)
   {
      $dbtype = strtoupper($dbtype);
      if ($dbtype == 'mysql' || $dbtype == 'PDO_DBLIB')
         $sql = "select uuid()";
      else if ($dbtype == 'oracle' || $dbtype == 'oci8' || $dbtype == 'PDO_OCI')
         $sql = "select rawtohex(sys_guid()) from dual";
      else if ($dbtype == 'msql' || $dbtype == 'PDO_MYSQL')
         $sql = "select newid()";
      else 
         throw new Exception("Error in generating new GUID: unsupported database type.");
      // execute sql to get the id
      $newid = $this->GetIdWithSql($conn, $sql);
      if ($newid === false)
         throw new Exception("Error in generating new GUID: unable to get a valid id.");
      return $newid;
   }
   
   // ID is generated before executing insert sql
   protected function GetNewUUID()
   {
      return uniqid();
   }
   
   private function GetIdWithSql($conn, $sql)
   {
      try {
         $rs = $conn->query($sql);
      }
      catch (Exception $e) {
         $this->m_ErrorMessage = "Error in query: " . $sql . ". " . $e->getMessage();
         return false;
      }
      if (($row = $rs->fetch()) != null) {
         return $row[0];
      }
      return false;
   }
}

?>