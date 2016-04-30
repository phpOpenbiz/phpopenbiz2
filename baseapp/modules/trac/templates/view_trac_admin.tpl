<head>
  <title>{$view_description}</title>
  <link rel="stylesheet" href="../css/openbiz.css" type="text/css">
  <link href="../css/ticket.css" rel="stylesheet" type="text/css"/>
{literal}
<script>
var selectedForm = "div_trac_FM_Component";
function showform(formname)
{
	if (formname == selectedForm)
		return;
	$(selectedForm).hide();
	$(formname).show();
	selectedForm = formname;
}
</script>
{/literal}
</head>
<body bgcolor="#EDEDED">
{$scripts}
{$controls[0]}
<table cellspacing=0 cellpadding=0 border=0>
<tr>
<td valign="top" class="leftcol">
   <div class="arrowlistmenu">
   <h3 class="headerbar">Ticket System</h3>
   <ul>
   <li><a href="javascript:showform('div_trac_FM_Component')">Component</a></li>
   <li><a href="javascript:showform('div_trac_FM_Milestone')">Milestone</a></li>
   <li><a href="javascript:showform('div_trac_FM_Version')">Version</a></li>
   <li><a href="javascript:showform('div_trac_FM_Enum')">List of values</a></li>
   </ul>
   </div>
</td>
<td width=100% class="main">
   <div>
   <div id="div_trac_FM_Component">{$forms.trac_FM_Component}</div>
   <div id="div_trac_FM_Version" style="display:none;">{$forms.trac_FM_Version}</div>
   <div id="div_trac_FM_Milestone" style="display:none;">{$forms.trac_FM_Milestone}</div>
   <div id="div_trac_FM_Enum" style="display:none;">{$forms.trac_FM_Enum}</div>
   </div>
</td>
</tr>
</table>
<hr>
<center>
{t}Powered by Openbiz{/t}
</center>
</body>
