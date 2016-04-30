<form id='{$name}' name='{$name}' onclick="SetActiveForm('{$name}')" class='inactive_form'>
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
<table width=100% border=0 bgcolor=#D9D9D9 cellspacing=1 cellpadding=3 class="tbl">
{section name="i" loop=$table}
{if $smarty.section.i.index == 0}
  <tr>
  {foreach item=cell from=$table[0]}
    <th class=head>{$cell}</th>
  {/foreach}
  </tr>
{else}
  {if $smarty.section.i.index == $formobj->m_CursorIndex}
    <tr class=rowsel  ondblclick="CallFunction('{$name}.EditRecord()');" onclick="CallFunction('{$name}.SelectRecord({$smarty.section.i.index},1)');">
  {elseif $smarty.section.i.index is odd}
    <tr class=rowodd  ondblclick="CallFunction('{$name}.EditRecord()');" onclick="CallFunction('{$name}.SelectRecord({$smarty.section.i.index},1)');">
  {else}
    <tr class=roweven ondblclick="CallFunction('{$name}.EditRecord()');" onclick="CallFunction('{$name}.SelectRecord({$smarty.section.i.index},1)');">
  {/if}
  {foreach item=cell from=$table[i]}
    <td valign=top class=cell>{$cell}</td>
  {/foreach}
</tr>
{/if}
{/section}

</table>
</div>

</td></tr>
</table>
</form>
{$formobj->m_CursorIndex}=?