<?php

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
   public $m_OnSaveDataObj;

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
      $this->m_OnSaveDataObj = isset($xmlArr["ATTRIBUTES"]["ONSAVEDATAOBJ"]) ? $xmlArr["ATTRIBUTES"]["ONSAVEDATAOBJ"] : null;

      $this->m_BizObjName = $this->PrefixPackage($this->m_BizObjName);
   }
}

?>