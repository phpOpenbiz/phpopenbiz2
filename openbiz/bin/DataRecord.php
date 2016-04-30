<?php

class DataRecord implements Iterator, ArrayAccess
{
   protected $m_var = array();
   protected $m_var_old = array();
   protected $m_BizObj = null;
   
   public function __construct($recArray, $bizObj)
   {
      if ($recArray != null && is_array($recArray))
      {
         $this->m_var = $recArray;
         $this->m_var_old = $recArray;
      }
      
      $this->m_BizObj = $bizObj;
   }
   
   // Iterator methods
   public function get($key) { return isset($this->m_var[$key]) ? $this->m_var[$key] : null; }
   public function set($key, $val) { $this->m_var[$key] = $val; }
   public function rewind() { reset($this->m_var);  }
   public function current() { return current($this->m_var); }
   public function key() { return key($this->m_var); }
   public function next() { return next($this->m_var); }
   public function valid() { return $this->current() !== false; }
   // ArrayAccess methods
   public function offsetExists($key) { return isset($this->m_var[$key]); }
   public function offsetGet($key) { return $this->get($key); } 
   public function offsetSet($key, $val) { $this->set($key, $val); } 
   public function offsetUnset($key) { unset($this->m_var[$key]); } 
   
   public function __get($field)
   {
      return $this->get($field);
   }
   
   public function __set($field, $value)
   {
      $this->set($field, $value);
   }
   
   // save record - call BizDataObj UpdateRecord method
   public function Save()
   {
      if (count($this->m_var_old) > 0)
         $ok = $this->m_BizObj->UpdateRecord($this->m_var, $this->m_var_old);
      else
         $ok = $this->m_BizObj->InsertRecord($this->m_var);
      return $ok;
   }
   
   // delete record - call BizDataObj DeleteRecord method
   public function Delete()
   {
      return $this->m_BizObj->DeleteRecord($this->m_var);
   }
   
   // return record in array
   public function toArray()
   {
      return $this->m_var;
   }
   
   // get reference object with given object name
   public function GetRefObject($objName)
   {
      return $this->m_BizObj->GetRefObject($objName);
   }
}
?>