<form id={$name} name={$name} Enctype="Multipart/Form-Data" method="post">
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
{foreach item=item from=$fields}
<tr>
<td width=100 class=item align=right valign=top>{$item.label}</td><td valign=top class=cell_edit>{$item.control}</td>
</tr>
{/foreach}
</table>
</td></tr>
</table>
<input type="hidden" name="MAX_FILE_SIZE" value="30000" />
</form>