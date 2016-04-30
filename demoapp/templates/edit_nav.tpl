<form id={$name} name={$name}>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr><td>
<div id="{$name}_toolbar">
<table width=100% cellspacing=0 cellpadding=1 class=tbl_title>
<tr>
<td width=50% align=left>
<table cellspacing=2 cellpadding=0><tr>
{foreach item=btn from=$toolbar}
  <td>{$btn}</td>
{/foreach}
</tr></table>
</td>
<td width=50% align=right>
{if $navbar}
<table cellspacing=2 cellpadding=1><tr>
<td>{$navbar.btn_prev}</td><td class="nav_w">{$navbar.curPage} of {$navbar.totalPage}</td><td>{$navbar.btn_next}</td>
</tr></table>
{/if}
</td>
</table>
</div>
</td></tr>
<tr><td>
<table width=100% border=0 cellspacing=1 cellpadding=3 class=tbl>
{foreach item=item from=$fields}
<tr>
<td width=100 class=item align=right valign=top>{$item.label}</td><td valign=top class=cell_edit>{$item.control}</td>
</tr>
{/foreach}
</table>
</td></tr>
</table>
</form>