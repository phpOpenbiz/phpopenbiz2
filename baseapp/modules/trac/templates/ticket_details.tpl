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
     <td width="12%" nowrap="" class="edit_label">Ticket Id:</td>
     <td width="37%" class="edit_control">{$fields.bctrl_Id.control}</td>
     <td width="12%" nowrap="" class="edit_label">Reported by:</label></td>
     <td width="37%" class="edit_control">{$fields.bctrl_reporter.control}</td>
   </tr>
   <tr>
     <td width="12%" nowrap="" class="edit_label">Summary:</td>
     <td class="edit_control" colspan="3">{$fields.bctrl_summary.control}</td>
   </tr>
   <tr>
    <td width="12%" nowrap="" class="edit_label">Type:</label></td>
    <td width="37%" class="edit_control">{$fields.bctrl_type.control}</td>
    <td width="12%" nowrap="" class="edit_label">Component:</label></td>
    <td width="37%" class="edit_control">{$fields.bctrl_component.control}</td>
   </tr>
  <tr>
    <td width="12%" nowrap="" class="edit_label">Priority:</label></td>
    <td width="37%" class="edit_control">{$fields.bctrl_priority.control}</td>
    <td width="12%" nowrap="" class="edit_label">Severity:</label></td>
    <td width="37%" class="edit_control">{$fields.bctrl_serverity.control}</td>
   </tr>
   <tr>
    <td width="12%" nowrap="" class="edit_label">Milestone:</label></td>
    <td width="37%" class="edit_control">{$fields.bctrl_milestone.control}</td>
    <td width="12%" nowrap="" class="edit_label">Version:</label></td>
    <td width="37%" class="edit_control">{$fields.bctrl_version.control}</td>
   </tr>
   <tr>
    <td width="12%" nowrap="" class="edit_label">Status:</label></td>
    <td width="37%" class="edit_control">{$fields.bctrl_status.control}</td>
    <td width="12%" nowrap="" class="edit_label">Resolution:</label></td>
    <td width="37%" class="edit_control">{$fields.bctrl_resolution.control}</td>
   </tr>
   <tr>
    <td width="12%" nowrap="" class="edit_label">Keywords:</label></td>
    <td width="37%" class="edit_control">{$fields.bctrl_keywords.control}</td>
    <td width="12%" nowrap="" class="edit_label">CC:</label></td>
    <td width="37%" class="edit_control">{$fields.bctrl_cc.control}</td>
   </tr>
   <tr>
    <td width="12%" nowrap="" class="edit_label">Assigned to:</label></td>
    <td width="37%" class="edit_control" colspan="3">{$fields.bctrl_owner.control}</td>
   </tr>
   <tr>
     <td width="12%" nowrap="" class="edit_label">Description:</td>
     <td class="edit_control" colspan="3">{$fields.bctrl_description.control}</td>
   </tr>
  </table>
</td></tr>
</table>
</form>
