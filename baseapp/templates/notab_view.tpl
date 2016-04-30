<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>{$view_description}</title>
  <link rel="stylesheet" href="../css/openbiz.css" type="text/css">
  {$style_sheets}
</head>
<body bgcolor="#EDEDED">
{$scripts}
<table width=100% border=0 cellspacing=10 cellpadding=0>
{section name=i loop=$controls}
   <tr><td>
   {$controls[i]}
   </td></tr>
{sectionelse}
   <b>Array $controls has no entries</b>
{/section}
</table>
</body>
</html>
