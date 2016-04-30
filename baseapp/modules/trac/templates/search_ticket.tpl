<form id={$name} name={$name}>
<fieldset style="font-size:12px; margin-bottom:10px; border: 1px solid #ABC3D7; background-color:#F0F0F0">
<LEGEND><b>{$title}</b></LEGEND>
<table border=0 cellspacing=1 cellpadding=3 style="padding-bottom:5px">
{assign var="i" value=0}
{foreach item=item from=$fields}
{if $i is div by 3}<tr>{/if}
<td align=right valign=top>{$item.label}</td>
<td valign=top>{$item.control}</td>
{if $i+1 is div by 3}</tr>{/if}
{assign var="i" value=$i+1}
{/foreach}
</table>
{foreach item=btn from=$toolbar}
{$btn}
{/foreach}
</fieldset>
</form>