<?php
ob_start();
header('Content-Type: text/html; charset=utf-8');
?>

<html>
<head>
  <title>Event management - report</title>
  <link rel="stylesheet" href="../css/openbiz.css" type="text/css">
  <script language="javascript" src="../js/clientUtil.js"></script>
</head>
<body>

<!-- tab -->
<?php
   include_once("app.inc");
?>
<?php
   global $g_BizSystem;
   $tabformobj = $g_BizSystem->GetObjectFactory()->GetObject("demo.Tabs");
   $tabformobj->SetCurrentTab("reports");
   $sHTML = $tabformobj->Render();
   echo $sHTML;
?>

<table width=90% border=0 cellspacing=0 cellpadding=0 style="margin: 10px">
  <tr><td class='title'>
  Openbiz Reports:
  </td></tr>
  <tr><td>
   <table width=500 border=1 cellspacing=0 cellpadding=3 style="font-size: 12px; font-family:Arial;">
   <tr style="background-color:silver; font-weight:bold"><td>Report Name</td>
   <td>HTML report</td>
   <!--<td>PDF report</td>-->
   </tr>
   <tr><td>Event summary report</td>
   <td><a href="../bin/controller.php?view=demo.ReportView"" target="_blank">HTML</a></td>
   <!--<td>
   <a href="../bin/controller.php?F=Invoke&P0=[service.pdfService]&P1=[renderView]&P2=[demo.ReportView]" target="_blank">PDF</a>
   </td>-->
   </tr>
   </table>
   </td></tr>
</table>
</body>
</html>
