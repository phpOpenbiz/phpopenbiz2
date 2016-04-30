<form id={$name} name={$name} style="margin:0">
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr><td width=0% class=title>{$title}</td></tr>
<tr><td>
<div id="{$name}_toolbar">
<table width=100% cellspacing=0 cellpadding=1  class=tbl_toolbar style="border:1px solid silver;border-bottom:0px">
<tr>
<td width=50% align=left>
<table cellspacing=2 cellpadding=0><tr>
{foreach item=btn from=$toolbar}
  <td>{$btn}</td>
{/foreach}
<td><div id='{$name}.load_disp' style="display:none"><img src="../images/indicator.white.gif"/></div></td>
</tr></table>
</td>
<td width=50% align=right>
<table cellspacing=2 cellpadding=1 ><tr>
<td>{$navbar.btn_prev}</td>
{if $header}
  <td>{$header}</td>
{else}
  <td>{$navbar.curPage} of {$navbar.totalPage}</td>
{/if}
<td>{$navbar.btn_next}</td>
</tr></table>
</td>
</table>
</div>
</td></tr>
{if $header}
   <tr><td bgcolor="white"><div class="calHeader">{$header}</div></td></tr>
{/if}
<tr><td>
<div id="{$name}_data" style="border:1px solid silver">
{$block}
</div>
</td></tr>
</table>
</form>