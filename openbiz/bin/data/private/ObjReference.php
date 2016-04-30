<?php

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

?>