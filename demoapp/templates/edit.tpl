<form id={$name} name={$name} onclick="SetActiveForm('{$name}')" class='inactive_form'>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr><td>
<div id="{$name}_toolbar">
<table width=100% cellspacing=0 cellpadding=0>
<tr>
<td width=0% class=title>{$title}</td>
</tr>
<tr>
<td align=left>
<table cellspacing=0 cellpadding=2 class='tbl_toolbar' width=100%><tr><td> 
{foreach item=btn from=$toolbar}
{$btn}
{/foreach}
</td></tr></table>
</td>
</table>
</div>
</td></tr>
<tr><td>
<div id="{$name}_data">
<table width=100% border=0 cellspacing=1 cellpadding=3 class=tbl>
{foreach item=item from=$fields}
<tr>
<td width=200 class=item align=right valign=top class=cell>{$item.label}
{if $item.required=="Y"} <span class='required'>*</span>{/if}</td>
<td valign=top class=cell_edit>{$item.control}</td>
</tr>
{/foreach}
</table>
</div>
</td></tr>
</table>
</form>
<script>focusFirstInput('{$name}');</script>