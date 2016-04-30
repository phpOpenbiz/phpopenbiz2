<form id='{$name}' name='{$name}' onclick="SetActiveForm('{$name}')" class='inactive_form'>
<table width=100% border=0 cellspacing=0 cellpadding=0 style="border:1px solid silver">
<tr><td width=0% class=title style="height:25px; padding-left:5px">{$title}</td></tr>
<tr><td>
<div id="{$name}_toolbar">
<table width=100% cellspacing=0 cellpadding=1 class=tbl_toolbar>
<tr>
<td style="font-size: 12px; font-family:Arial; border-bottom:1px solid silver" colspan=2>
{$parents_links}
</td>
</tr>
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
<td>{$navbar.btn_first}</td><td>{$navbar.btn_prev}</td><td class="nav_b">
<input type='text' class='nav' value='{$navbar.curPage} of {$navbar.totalPage}' onkeypress="return navKeyPress(this, event);">
</td><td>{$navbar.btn_next}</td><td>{$navbar.btn_last}</td>
</tr></table>
{/if}
</td>
</table>
</div>
</td></tr>
<tr><td>
<div id="{$name}_data">
{$block}
</div>
</td></tr>
</table>
</form>