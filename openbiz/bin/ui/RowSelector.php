<?php

class RowSelector extends FieldControl 
{
   public function RenderHeader()
   {
      $formname = $this->m_BizFormName;
      $name = $this->m_Name.'[]';
      $sHTML = "<INPUT TYPE=\"CHECKBOX\" onclick=\"checkAll(this, $('$formname')['$name']);\"/>";
      return $sHTML;
   }
   
   public function Render()
   {
      $value = $this->m_Value;
      $name = $this->m_Name.'[]';
      $sHTML = "<INPUT TYPE=\"CHECKBOX\" NAME=\"$name\" VALUE='$value' onclick=\"event.cancelBubble=true;\"/>";
      return $sHTML;
   }
}

?>