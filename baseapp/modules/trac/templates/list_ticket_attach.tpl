<form id='{$name}' name='{$name}' style="border-bottom:2px solid #ABC3D7;border-top:2px solid #4E8CCF;">
<h2 class="title">{$title}</h2>
<div style="padding-bottom: 3px">
{foreach item=btn from=$toolbar}
  <span>{$btn}</span>
{/foreach}
</div>
<div  class="listform" style="font-size: 12px">
{section name="i" loop=$table}
{if $smarty.section.i.index != 0}
  <li style="padding-left: 20px">
  {$table[i].bctrl_filename} ({$table[i].bctrl_size} bytes) added by {$table[i].bctrl_author} on {$table[i].bctrl_time}
  <div style="padding-left: 18px"><i>Description: {$table[i].bctrl_description}</i></div>
  </li>
{/if}
{/section}
{if $smarty.section.i.total <= 1}
  <div style="padding: 2 2 2 2; font-size:12px">
  No attachment found
  </div>
{/if}
</div>
<!--<table width="100%" cellspacing="0" cellpadding="0" border="0" class="tabDetailView">
<tbody><tr>
<td width="12%" nowrap="" class="edit_label2">Author:<span class="required">*</span></td>
<td class="edit_control2"><input required="1" value="rocky" id="bctrl_author" name="bctrl_author"/></td>
</tr>
<tr>
<td width="12%" nowrap="" class="edit_label2">Description:</td>
<td class="edit_control2"><input style="width: 500px;" value="file attachment test" id="bctrl_description" name="bctrl_description"/></td>
</tr>
<tr>
<td width="12%" nowrap="" class="edit_label2">File:<span class="required">*</span></td>
<td class="edit_control2"><input type="file" size="50" required="1" value="favicon.ico" id="bctrl_filename" name="bctrl_filename"/></td>
</tr>
<tr>
<td width="12%" nowrap="" class="edit_label2"/>
<td class="edit_control2"><input type="checkbox" checked="" value="" id="bctrl_replace" name="bctrl_replace[]"/> Replace the file with same name</td>
</tr>
</tbody>
</table>-->
</form>