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
<table width=100% border="0" class=tbl><tr><td>
<table width=50% border=0 cellspacing=1 cellpadding=3 class=tbl>
<tr>
<td width=200 class=item align=right valign=top class=cell>{$fields.combo1.label}
{if $item.required=="Y"} <span class='required'>*</span>{/if}</td>
<td valign=top class=cell_edit>{$fields.combo1.control}</td>
</tr>
<tr>
<td width=200 class=item align=right valign=top class=cell>{$fields.edit1.label}
{if $item.required=="Y"} <span class='required'>*</span>{/if}</td>
<td valign=top class=cell_edit>{$fields.edit1.control}</td>
</tr>
<tr>
<td width=200 class=item align=right valign=top class=cell>{$fields.edit2.label}
{if $item.required=="Y"} <span class='required'>*</span>{/if}</td>
<td valign=top class=cell_edit>{$fields.edit2.control}</td>
</tr>
<tr>
<td width=200 class=item align=right valign=top class=cell>{$fields.combo2.label}
{if $item.required=="Y"} <span class='required'>*</span>{/if}</td>
<td valign=top class=cell_edit>{$fields.combo2.control}</td>
</tr>
<tr>
<td width=200 class=item align=right valign=top class=cell>{$fields.edit3.label}
{if $item.required=="Y"} <span class='required'>*</span>{/if}</td>
<td valign=top class=cell_edit>{$fields.edit3.control}</td>
</tr>
</table>
</td>
<td valign=top>
<table width=50% border=0 cellspacing=1 cellpadding=3 class=tbl>
<tr>
<td width=200 class=item align=right valign=top class=cell>{$fields.attd_fname.label}
{if $item.required=="Y"} <span class='required'>*</span>{/if}</td>
<td valign=top class=cell_edit>{$fields.attd_fname.control}</td>
</tr>
</table>
</td>
</tr>
</table>
</div>
</td></tr>
</table>
</form>