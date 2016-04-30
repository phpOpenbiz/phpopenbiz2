<form id={$name} name={$name} enctype="multipart/form-data" method="post">
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr><td>

<table width=100% cellspacing=0 cellpadding=0>
<tr>
<td>
<h2 class="title">{$title}</h2>
</td>
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
<td width="12%" nowrap="" class="edit_label2">Author:<span class='required'>*</span></td>
<td class="edit_control2">{$fields.bctrl_author.control}</td>
</tr>
<tr>
<td width="12%" nowrap="" class="edit_label2">Description:</td>
<td class="edit_control2">{$fields.bctrl_description.control}</td>
</tr>
<tr>
<td width="12%" nowrap="" class="edit_label2">File:<span class='required'>*</span></td>
<td class="edit_control2">{$fields.bctrl_filename.control}</td>
</tr>
<tr>
<td width="12%" nowrap="" class="edit_label2"></td>
<td class="edit_control2">{$fields.bctrl_replace.control} {$fields.bctrl_replace.label}</td>
</tr>
</table>

</td></tr>
</table>
{$fields.bctrl_parent_id.control}
</form>
<script>focusFirstInput('{$name}');</script>