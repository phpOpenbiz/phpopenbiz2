<form id='{$name}' name='{$name}' style="border-bottom:2px solid #ABC3D7;border-top:2px solid #4E8CCF;">
<h2 class="title">{$title}</h2>
<div  class="listform">
<table width=100% cellspacing=0 cellpadding=1>
<tr>
<td width=50% align=left>
   <table cellspacing=2 cellpadding=0>
   <tr>
   {foreach item=btn from=$toolbar}
     <td>{$btn}</td>
   {/foreach}
   <td><div id='{$name}.load_disp' style="display:none"><img src="../images/indicator.white.gif"/></div></td>
   </tr>
   </table>
</td>
<td width=50% align=right>
{if $navbar}
   <table cellspacing=2 cellpadding=1>
   <tr>
   <td>{$navbar.btn_first}</td><td>{$navbar.btn_prev}</td><td class="nav_b">
   <input type='text' class='nav' value='{$navbar.curPage} of {$navbar.totalPage}' onkeypress="return navKeyPress(this, event);">
   <input type='hidden' name='_currentPage' value='{$navbar.curPage}'/>
   <input type='hidden' name='_totalPage' value='{$navbar.totalPage}'/>
   </td><td>{$navbar.btn_next}</td><td>{$navbar.btn_last}</td>
   </tr>
   </table>
{/if}
</td>
</tr>
<tr>
<td colspan=9>
   <div id="{$name}_data">
   <table width=100% border=0 bgcolor=#D9D9D9 cellspacing=0 cellpadding=3 class="tbl">
   {section name="i" loop=$table}
   {if $smarty.section.i.index == 0}
     <tr>
     {foreach item=cell from=$table[0]}
       <th class=head>{$cell}</th>
     {/foreach}
     </tr>
   {else}
     {if $smarty.section.i.index is odd}
       <tr class=rowodd>
     {else}
       <tr class=roweven>
     {/if}
     {foreach item=cell from=$table[i]}
     {if $cell}
       <td valign=top class=cell>{$cell}</td>
     {else}
       <td valign=top class=cell>&nbsp;</td>
     {/if}
     {/foreach}
   </tr>
   {/if}
   {/section}
   </table>
   </div>
</td>
</tr>
</table>
</div>
</form>
