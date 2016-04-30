<form id={$name} name={$name}>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr><td>
<div id="{$name}_toolbar">
<table width=100% cellspacing=0 cellpadding=0 >
<tr>
<td width=0% class=title>{$title}</td>
</tr>
<tr>
<td align=left>
<table cellspacing=0 cellpadding=2 class='tbl_toolbar' width=100%  style="border:1px solid silver"><tr><td> 
{foreach item=btn from=$toolbar}
{$btn}
{/foreach}
</td></tr></table>
</td>
</table>
</div>
</td></tr>
<tr><td>
<table width=100% border=0 cellspacing=1 cellpadding=3 class=tbl  style="border:1px solid silver">
<tr><td width=400 valign=top>
<table border=0 cellspacing=3 cellpadding=3>
{assign var="i" value=0}
{foreach item=item from=$fields}
{if $i < 7}<tr>
<td width=100 style='font-size: 12px;' align=right valign=top>{$item.label}</td><td valign=top class=cell_edit>{$item.control}</td>
</tr>{/if}
{assign var="i" value=$i+1}
{/foreach}
</td></tr>
</table>
</td>
<td width=300 valign=top>
<table border=0 cellspacing=3 cellpadding=3>
{assign var="i" value=0}
{foreach item=item from=$fields}
{if $i >= 7}<tr>
<td width=100 style='font-size: 12px;' align=right valign=top>{$item.label}</td><td valign=top class=cell_edit>{$item.control}</td>
</tr>{/if}
{assign var="i" value=$i+1}
{/foreach}
</td>
</tr>
</table>
<td>&nbsp;</td>
</td></tr>
</table>
</form>