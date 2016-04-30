{literal}
<SCRIPT TYPE="text/javascript">
<!--
function submitenter(myfield,e)
{
var keycode;
if (window.event) keycode = window.event.keyCode;
else if (e) keycode = e.which;
else return true;

if (keycode == 13)
   {
   myfield.form.submit();
   return false;
   }
else
   return true;
}
//-->
</SCRIPT>
{/literal}

<center>
<p>&nbsp;</p>
<p>&nbsp;</p>
<form id={$name} name={$name}>
<table width=350 border=0 cellspacing=0 cellpadding=0 style="border:1px solid gray">
<tr><td>
<div id="{$name}_toolbar">
<table width=100% cellspacing=0 cellpadding=3>
<tr bgcolor=black>
<td align=left class=title_w>{$title}
</td>
</table>
</div>
</td></tr>
<tr><td>
<table width=100% border=0 cellspacing=1 cellpadding=5 class=tbl>
{foreach item=item from=$fields}
<tr>
<td width=100 class=item align=right valign=top>{$item.label}</td><td width=250 valign=top class=cell_edit>{$item.control}</td>
</tr>
{/foreach}
<tr><td colspan=2>{$toolbar.txt_err}</td></tr>
<tr><td></td><td>{$toolbar.btn_submit}</td></tr>
</table>
</td></tr>
</table>
</form>
</center>
<script>focusFirstInput('{$name}');</script>
