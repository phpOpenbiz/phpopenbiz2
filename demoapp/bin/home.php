<?php
ob_start();
header('Content-Type: text/html; charset=utf-8');
?>

<html>
<head>
  <title>Event management home</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" href="../css/openbiz.css" type="text/css">
  <script language="javascript" src="../js/clientUtil.js"></script>
</head>
<body>

<!-- tab -->
<?php
   include_once("app.inc");
?>
<?php
   /*global $g_BizSystem;
   $obj = $g_BizSystem->GetObjectFactory()->GetObject("demo.BOEvent");
   $obj->FetchRecords($searchRule, $resultRecord);
*/
   global $g_BizSystem;
   $tabformobj = $g_BizSystem->GetObjectFactory()->GetObject("demo.Tabs");
   $tabformobj->SetCurrentTab("home");
   $sHTML = $tabformobj->Render();
   echo $sHTML;
?>

<p align="center">&nbsp;</p>
<p align="center"><img border="0" src="../images/welcome.gif" alt="welcome to event manager"></p>
<p align="center">&nbsp;</p>
<p align="center"><font face="Arial"><a href="../bin/logout.php" target="_parent"> Click to Logout</a></font></p>
<p align="center">&nbsp;</p>
<p align="center"><font face="Arial"><i>Powered by <a href="http://phpopenbiz.org" target="_blank">OpenBiz</a>
- an open framework for business solution</i></font></p>
</body>
</html>
