<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>{$view_description}</title>
  <link rel="stylesheet" href="../css/openbiz.css" type="text/css">
  <link rel="stylesheet" href="../css/menu.css" type="text/css">
  {$style_sheets}
  <!-- -->
  <!--[if gte IE 5.5]>
   <script language="JavaScript" src="../js/ie_menu.js" type="text/JavaScript"></script>
  <![endif]-->
</head>
<body bgcolor="#FFFFFF">
<script language="JavaScript">
// Preload images for tree
minus = new Image();
minus.src = "../images/minus.gif";
plus = new Image();
plus.src = "../images/plus.gif";

</script>
{$scripts}
{$controls[0]}
<table width=100% border=0 cellspacing=0 cellpadding=3>
   <tr><td><div class='title'>Test tree:</div>{$controls[2]}</td>
   <td valign=top><div class='title'>Test menu:</div>{$controls[1]}</td></tr>
   <tr><td colspan=2>{$controls[3]}</td></tr>
   <tr><td colspan=2>{$controls[4]}</td></tr>
</table>
</body>
</html>
