<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
  <title>{$view_description}</title>
  <link rel="stylesheet" href="../css/openbiz.css" type="text/css">
  {$style_sheets}
  <script language="javascript" src="../js/clientUtil.js"></script>
  <script language="javascript" src="../js/jsval.js"></script>
  <!-- dynarch calendar includes -->
  <style type="text/css">@import url(../js/jscalendar/calendar-system.css);</style>
	<script type="text/javascript" src="../js/jscalendar/calendar.js"></script>
	<script type="text/javascript" src="../js/jscalendar/lang/calendar-en.js"></script>
	<script type="text/javascript" src="../js/jscalendar/calendar-setup.js"></script>
	<script type="text/javascript" src="../js/calendar.js"></script>
  <!-- -->
</head>
<body bgcolor="#EDEDED">
<script language="JavaScript">
// Preload images for tree
minus = new Image();
minus.src = "../images/minus.gif";
plus = new Image();
plus.src = "../images/plus.gif";
</script>
{$controls[0]}
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=150 valign="top">
{$controls[1]}
</td>
<td>
<iframe src="controller.php?view=admin.company.ViewGenInfo" id="adminContent" name="adminContent" width="100%" height="800" frameborder="0"> </iframe>
</td>
</tr>
</table>
</body>
</html>
