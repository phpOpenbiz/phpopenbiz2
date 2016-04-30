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
{foreach item=item from=$fields}
<tr>
<td width="12%" nowrap="" class="edit_label2">{$item.label}
{if $item.required=="Y"} <span class='required'>*</span>{/if}
</td>
<td class="edit_control2">{$item.control}</td>
</tr>
{/foreach}
</table>

</td></tr>
</table>
</form>
<script>focusFirstInput('{$name}');</script>