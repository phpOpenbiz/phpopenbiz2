<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
  <title>{$view_description}</title>
  <link rel="stylesheet" href="../css/openbiz.css" type="text/css">
  <link rel="stylesheet" href="../css/menu.css" type="text/css">
  {$style_sheets}
  <script language="javascript" src="../js/clientUtil.js"></script>
  <script language="javascript" src="../js/richtext.js"></script>
  <script language="javascript" src="../js/jsval.js"></script>
  <script language="javascript" src="../js/myjbForm.js"></script>
  <!-- dynarch calendar includes -->
  <style type="text/css">@import url(../js/jscalendar/calendar-win2k-1.css);</style>
	<script type="text/javascript" src="../js/jscalendar/calendar.js"></script>
	<script type="text/javascript" src="../js/jscalendar/lang/calendar-en.js"></script>
	<script type="text/javascript" src="../js/jscalendar/calendar-setup.js"></script>
	<script type="text/javascript" src="../js/calendar.js"></script>
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

// initiate rich text editor. Usage: initRTE(imagesPath, includesPath, cssFile, genXHTML)
initRTE('../images/rte/', '../pages/rte/', '', false);
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
