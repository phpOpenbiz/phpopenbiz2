<form id={$name} name={$name}>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr><td>

<table width=100% cellspacing=0 cellpadding=0>
<tr>
<td><h2 class="title">{$title}: {$fields.bctrl_Id.value}</h2></td>
</tr>
<tr>
<td align=left>
<table cellspacing=2 cellpadding=0 width=100%>
<tr><td>
{foreach item=btn from=$toolbar}
{$btn}
{/foreach}
</td></tr>
</table>
</td>
</table>

</td></tr>
<tr><td>
  <table class="tabDetailView" width="100%" cellspacing="0" cellpadding="0" border="0">
   <tr>
     <td width="12%" nowrap="" class="edit_label2">Summary:
     <span class='required'>*</span></td>
     <td class="edit_control2" colspan="3">{$fields.bctrl_summary.control}</td>
   </tr>
   <tr>
    <td width="12%" nowrap="" class="edit_label2">Type:</label></td>
    <td width="37%" class="edit_control2">{$fields.bctrl_type.control}</td>
    <td width="12%" nowrap="" class="edit_label2">Component:</label></td>
    <td width="37%" class="edit_control2">{$fields.bctrl_component.control}</td>
   </tr>
  <tr>
    <td width="12%" nowrap="" class="edit_label2">Priority:</label></td>
    <td width="37%" class="edit_control2">{$fields.bctrl_priority.control}</td>
    <td width="12%" nowrap="" class="edit_label2">Severity:</label></td>
    <td width="37%" class="edit_control2">{$fields.bctrl_serverity.control}</td>
   </tr>
   <tr>
    <td width="12%" nowrap="" class="edit_label2">Milestone:</label></td>
    <td width="37%" class="edit_control2">{$fields.bctrl_milestone.control}</td>
    <td width="12%" nowrap="" class="edit_label2">Version:</label></td>
    <td width="37%" class="edit_control2">{$fields.bctrl_version.control}</td>
   </tr>
   <tr>
    <td width="12%" nowrap="" class="edit_label2">Status:</label></td>
    <td width="37%" class="edit_control2">{$fields.bctrl_status.control}</td>
    <td width="12%" nowrap="" class="edit_label2">Resolution:</label></td>
    <td width="37%" class="edit_control2">{$fields.bctrl_resolution.control}</td>
   </tr>
   <tr>
    <td width="12%" nowrap="" class="edit_label2">Keywords:</label></td>
    <td width="37%" class="edit_control2">{$fields.bctrl_keywords.control}</td>
    <td width="12%" nowrap="" class="edit_label2">CC:</label></td>
    <td width="37%" class="edit_control2">{$fields.bctrl_cc.control}</td>
   </tr>
   <tr>
     <td width="12%" nowrap="" class="edit_label2">Assigned to:</label></td>
     <td width="37%" class="edit_control2" colspan="3">{$fields.bctrl_owner.control}</td>
   </tr>
   <tr>
     <td width="12%" nowrap="" class="edit_label2" style="padding-bottom:5px;">Description:</td>
     <td class="edit_control2" colspan="3" style="padding-bottom:5px;">{$fields.bctrl_description.control}</td>
   </tr>
   <tr>
     <td width="12%" nowrap="" class="edit_label2" style="border-top:2px solid silver; padding-top:5px;">{$fields.editor_name.label}
     <span class='required'>*</span></td>
     <td class="edit_control2" colspan="3" style="border-top:2px solid silver; padding-top:5px;">{$fields.editor_name.control}</td>
   </tr>
   <tr>
     <td width="12%" nowrap="" class="edit_label2">New comments:</td>
     <td class="edit_control2" colspan="3">{$fields.editor_comment.control}</td>
   </tr>
  </table>
</td>
</tr>
</table>
</form>
<script>focusFirstInput('{$name}');</script>