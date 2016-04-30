<form id={$name} name={$name} style="margin:0">
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr><td width=0% class=title>{$title}</td></tr>
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
<div id="{$name}_data">
{$fmttable}
</div>
</td></tr>
</table>
<table align="right"><tr>
<td class='calBusyIcon'></td><td class=calStatus>Busy</td><td>&nbsp;</td><td class='calOverlapIcon'></td><td class=calStatus>Overlap</td>
</tr></table>
</form>