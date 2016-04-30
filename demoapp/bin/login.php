<?php
ob_start();
$application_homepage = "../bin/home.php";

include_once("app.inc");

$login = $_GET['login'];

if ($login == "success") {
   // Redirect to application homepage
   //header("Location: $application_homepage");
   echo "<script>window.location.href = '$application_homepage';</script>";
   ob_end_flush();
   exit;
}
else {
   global $g_BizSystem;
   $viewobj = $g_BizSystem->GetObjectFactory()->GetObject("shared.SignupView");
   $viewobj->Render();
}
?>
