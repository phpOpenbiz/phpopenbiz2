<form id={$name} name={$name}>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr><td>
<div id="{$name}_toolbar">
<table width=100% cellspacing=0 cellpadding=1>
<tr>
<td width=0% class=title>{$title}</td></tr>
<tr  bgcolor=gray>
<td align=left>
<table cellspacing=2 cellpadding=0><tr>
{foreach item=btn from=$toolbar}
  <td>{$btn}</td>
{/foreach}
</tr></table>
</td>
</table>
</div>
</td></tr>
<tr><td>
<table width=100% border=0 cellspacing=1 cellpadding=3 class=tbl>
{section name=i loop=$controls}
<tr><td width=100 class=item align=right valign=top>{$columns[i]}</td><td class=cell_edit>{$controls[i]}</td></tr>
{/section}
</table>
</td></tr>
</table>
</form>