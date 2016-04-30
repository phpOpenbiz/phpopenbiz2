<form id='{$name}' name='{$name}' onclick="SetActiveForm('{$name}')" class='inactive_form' oncontextmenu="return showmenu(event, '{$name}_contextmenu');">
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr><td width=0% class=title>{$title}</td></tr>
<tr><td>
<div id="{$name}_toolbar">
<table width=100% cellspacing=0 cellpadding=1 class=tbl_toolbar>
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

<!-- add resizble behavior to table columns -->
<script>TableKit.Resizable.init($('{$name}_data').down('table'));</script>
