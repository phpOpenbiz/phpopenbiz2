<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>{$view_description}</title>
  
  <link rel="stylesheet" href="../css/openbiz.css" type="text/css">
  <link rel="stylesheet" href="../css/calendar.css" type="text/css">
  
  <script type='text/javascript' src='../js/prototype.js'></script>
  {$style_sheets}
</head>
<body bgcolor="#EDEDED">
{$scripts}
<!--{if $viewtopic != ""}<div class="view_topic">{$viewtopic}</div>{/if}-->
<table width=100% border=0 cellspacing=0 cellpadding=10>
{section name=i loop=$controls}
   <tr><td>
   {$controls[i]}<br>
   </td></tr>
{sectionelse}
   <b>Array $controls has no entries</b>
{/section}
</table>
</body>
</html>
