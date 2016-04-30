<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
  <title>{$view_description}</title>
  <link rel="stylesheet" href="../css/openbiz.css" type="text/css">
  <link rel="stylesheet" href="../css/popcalendar.css" type="text/css">
  {$style_sheets}
  <script language="javascript" src="../jsrsClient.js"></script>
  <script language="javascript" src="../js/clientUtil.js"></script>
  <script language="javascript" src="../popcalendar.js"></script>
  <!-- dynarch calendar includes -->
  <style type="text/css">@import url(../js/jscalendar/calendar-win2k-1.css);</style>
	<script type="text/javascript" src="../js/jscalendar/calendar.js"></script>
	<script type="text/javascript" src="../js/jscalendar/lang/calendar-en.js"></script>
	<script type="text/javascript" src="../js/jscalendar/calendar-setup.js"></script>
	<script type="text/javascript" src="../js/calendar.js"></script>
  <!-- -->
</head>
<body bgcolor="#EDEDED" onHelp="return jsrsDebugInfo();">
{$scripts}
<table width=100% border=0 cellspacing=0 cellpadding=5>
<tr>
   <td valign="top" width=180>
   {$controls[0]}
   </td>
   <td valign="top">
   <table width=100% border=0 cellspacing=0 cellpadding=0>
      <tr><td>{$controls[1]}</td></tr>
      <tr><td>{$controls[2]}</td></tr>
   </table>
</tr>
</table>
</body>
</html>
