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
<table cellspacing=2 cellpadding=0 class='tbl_toolbar' width=100%>
<tr><td>
{foreach item=btn from=$toolbar}
{$btn}
{/foreach}
</td></tr>
</table>
</td>
</table>
</div>
</td></tr>
<tr><td>
<table width=100% border=0 cellspacing=1 cellpadding=3 class=tbl>
{assign var="i" value=0}
{foreach item=item from=$fields}
{if $i is div by 2}<tr>{/if}
<td width=100 class=item align=right valign=top class=cell>{$item.label}</td><td valign=top class=cell_edit>{$item.control}</td>
{if $i is not div by 2}</tr>{/if}
{assign var="i" value=$i+1}
{/foreach}
</table>
</td></tr>
</table>
</form>
<script>focusFirstInput('{$name}');</script>