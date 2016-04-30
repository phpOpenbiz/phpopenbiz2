<head>
  <title>{$view_description}</title>
  <link rel="stylesheet" href="../css/openbiz.css" type="text/css">
  {$style_sheets}
  <script language="javascript" src="../jsrsClient.js"></script>
  <script language="javascript" src="../js/clientUtil.js"></script>
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
<table width=100% border=0 cellspacing=0 cellpadding=10>
<tr>
   <td valign="top" width=180>
   {$controls[0]}
   </td>
   <td valign="top" style="padding:10 10 10 10">
   {$controls[1]}
   </td>   
</tr>
</table>
</body>
