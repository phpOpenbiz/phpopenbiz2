<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
  <title>{$view_description}</title>
  <link rel="stylesheet" href="../css/openbiz.css" type="text/css">
  
  <script type='text/javascript' src='../js/prototype.js'></script>
  
  {$style_sheets}
</head>
<body bgcolor="#EDEDED">
{$scripts}
{$controls[0]}
<table width=100% border=0 cellspacing=10 cellpadding=0>
{section name=i loop=$controls}
   {if $smarty.section.i.index != 0}
   <tr><td>
   {$controls[i]}
   </td></tr>
   {/if}
{sectionelse}
   <b>Array $controls has no entries</b>
{/section}
</table>
<center>
{t}Powered by Openbiz{/t}
</center>
</body>
</html>
