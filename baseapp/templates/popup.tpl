<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
  <title>{$view_description}</title>
  <link rel="stylesheet" href="../css/openbiz.css" type="text/css">
  {$style_sheets}
  <script language="javascript" src="../js/clientUtil.js"></script>
</head>
<body bgcolor="#EDEDED" style="margin-top: 0; margin-left: 0; margin-right: 0; margin-bottom: 0">
{$scripts}
<table width=100% border=0 cellspacing=5 cellpadding=0>
{section name=i loop=$controls}
   <tr><td>
   {$controls[i]}
   </td></tr>
{sectionelse}
   <b>Array $columns has no entries</b>
{/section}
</table>

</body>
</html>