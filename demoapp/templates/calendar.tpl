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
<table cellspacing=2 cellpadding=1><tr>
<td>{$navbar.btn_prev}</td>
{if $header}
  <td class="nav_w">{$header}</td>
{else}
  <td class="nav_w">{$navbar.curPage} of {$navbar.totalPage}</td>
{/if}
<td>{$navbar.btn_next}</td>
</tr></table>
</td>
</table>
</div>
</td></tr>
<tr><td bgcolor="white"><div class="calHeader">{$header}</div></td></tr>
<tr><td>
<div id="{$name}_data">
{$block}
</div>
</td></tr>
</table>
</form>