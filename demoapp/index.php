<?php ob_start(); ?>

<?php
@require_once('bin/app.inc');

// response ajax call
if (isset($_REQUEST['action']))
{
   if ($_REQUEST['action']=='create_db')
   {
      createDB();
      exit;
   }
   if ($_REQUEST['action']=='fill_db')
   {
      fillDB();
      exit;
   }
   if ($_REQUEST['action']=='replace_db_cfg')
   {
      replaceDbConfig();
      exit;
   }
}

function showStatus($status)
{
    echo "<table border=1><tr><th>Item checked</th><th>Value</th><th>Status</th></tr>\n";
    foreach ($status as $s) {
        if (strpos($s['status'],'OK') === 0) 
            $style="style='color:green;font-weight:bold'";
        else
            $style="style='color:red;font-weight:bold'";
        echo "<tr><td>".$s['item']."</td><td>".$s['value']."</td><td><span $style>".$s['status']."</span></td></tr>\n";
    }
    echo "</table>\n";
}

function connectDB($noDB=false) {
    require_once 'Zend/Db.php';

    // Automatically load class Zend_Db_Adapter_Pdo_Mysql and create an instance of it.
    $param = array(
        'host'     => $_REQUEST['dbHostName'],
        'username' => $_REQUEST['dbUserName'],
        'password' => $_REQUEST['dbPassword'],
        'port'     => $_REQUEST['dbHostPort'],
        'dbname'   => $_REQUEST['dbName']
    );
    if ($noDB) $param['dbname'] = '';
    
    try {
        $db = Zend_Db::factory($_REQUEST['dbtype'], $param);
        $conn = $db->getConnection();
    } catch (Zend_Db_Adapter_Exception $e) {
        // perhaps a failed login credential, or perhaps the RDBMS is not running
        echo 'ERROR: '.$e->getMessage(); 
        exit;
    } catch (Zend_Exception $e) {
        // perhaps factory() failed to load the specified Adapter class
        echo 'ERROR: '.$e->getMessage(); exit;
    }
    return $conn;
}

function createDB() {
	$conn = connectDB(true);
	try {
	   $conn->exec("CREATE DATABASE " . $_REQUEST['dbName']);
	}
	catch (Exception $e) {
	   echo 'ERROR: '.$e->getMessage(); exit;
   }
   unset($conn);

	$conn = connectDB();
    if (!$conn) {
		echo 'ERROR: Unable to create Database!';
		return;
	}
    echo 'SUCCESS: Database '.$_REQUEST['dbName'].' is created';
}

function fillDB()
{
   $conn = connectDB();
   $queryFile = "demoapp.sql";
	$fp    = fopen($queryFile, 'r');

	if (!$fp) {
		echo 'ERROR: Unable to open demoapp.sql!';
		return;
	}

	$query = fread($fp, filesize($queryFile));
	fclose($fp);

	$dbScriptStatements = explode(";", $query);
    foreach ($dbScriptStatements as $sql)
    {
        $sql = trim($sql);
        if ($sql == "") continue;
        try {
            $conn->exec($sql);
        } catch (Exception $e) {
            echo 'ERROR: '.$e->getMessage().' '.$sql;
            return;
        }
    }
    echo 'SUCCESS Data is populated into database.';
}

function replaceDbConfig()
{
   $filename = META_PATH.'/Config.xml';
   $xml = simplexml_load_file($filename);
   $xml->DataSource->Database[0]['Driver'] = $_REQUEST['dbtype'];
   $xml->DataSource->Database[0]['Server'] = $_REQUEST['dbHostName'];
   $xml->DataSource->Database[0]['User'] = $_REQUEST['dbUserName'];
   $xml->DataSource->Database[0]['Password'] = $_REQUEST['dbPassword'];
   $xml->DataSource->Database[0]['DBName'] = $_REQUEST['dbName'];
   //$xml->DataSource->Database[0]['Port'] $_REQUEST['dbHostPort'];
   $fp = fopen ($filename, 'w');
   if (fwrite($fp, $xml->asXML()) === FALSE) {
        echo "Cannot write to file ($filename)";
        exit;
    }
    fclose($fp);
    showDBConfig();
}
function showDBConfig()
{
   $xml = simplexml_load_file(META_PATH.'/Config.xml');
   //print_r($xml);
   echo "<b>Current setting of Default Database:</b>";
   echo '<table border=1><tr>';
   echo '<th>Name</th><th>Driver</th><th>Server</th><th>DBName</th><th>User</th><th>Password</th></tr>';
   echo '<tr>';
   echo '<td>'.$xml->DataSource->Database[0]['Name'].'</td>';
   echo '<td>'.$xml->DataSource->Database[0]['Driver'].'</td>';
   echo '<td>'.$xml->DataSource->Database[0]['Server'].'</td>';
   echo '<td>'.$xml->DataSource->Database[0]['DBName'].'</td>';
   echo '<td>'.$xml->DataSource->Database[0]['User'].'</td>';
   echo '<td>'.$xml->DataSource->Database[0]['Password'].'</td>'; 
   echo '</tr></table>';  
}
?>

<html>
<head>
<title>Demoapp installation</title>
<style><!--
body,th,td,a,p,.h{font-family:arial,sans-serif}
body,th,td {font-size: 12px;}
a:link{color:#039}
a:visited{color:#666}
a:hover{color:#333}
a:active{color:#000}
img{border:0}
input,select,textarea{border: 1px solid gray; font-family:Arial; font-size:12px; }
table{border: 1px solid gray;}
--></style>
<script language="javascript" src="js/prototype.js"></script>
<SCRIPT TYPE="text/javascript">
<!--
var steps = new Array('step1','step2','step3');
var steps_txt = new Array('System check', 'Database configuration', 'Application configuration');
function create_db()
{
    $('create_db_result').innerHTML='';
    new Ajax.Request('index.php?action=create_db', {
      onLoading: function() {
         Element.show('createdb_img'); // or $('createdb_img').show();
      },
      onComplete: function() {
         Element.hide('createdb_img');
      },
      onSuccess: function(transport){
         var response = transport.responseText || "no response text";
         $('create_db_result').innerHTML=response;
      },
      onFailure: function(){ alert('Something went wrong...') },
      parameters: $('setupform').serialize()
   })
}
function fill_db()
{
    $('fill_db_result').innerHTML='';
    new Ajax.Request('index.php?action=fill_db', {
      onLoading: function() {
         Element.show('filldb_img');
      },
      onComplete: function() {
         Element.hide('filldb_img');
      },
      onSuccess: function(transport){
         var response = transport.responseText || "no response text";
         $('fill_db_result').innerHTML = response;
      },
      onFailure: function(){ alert('Something went wrong...') },
      parameters: $('setupform').serialize()
   })
}
function replace_db_cfg()
{
   new Ajax.Request('index.php?action=replace_db_cfg', {
      onLoading: function() {
         Element.show('replace_db_img');
      },
      onComplete: function() {
         Element.hide('replace_db_img');
      },
      onSuccess: function(transport){
         var response = transport.responseText || "no response text";
         $('db_config').innerHTML = response;
      },
      onFailure: function(){ alert('Something went wrong...') },
      parameters: $('setupform').serialize()
   })
}
function go_step(num)
{
   var title='';
   for (i=0; i<steps.length; i++) {
      if (i+1 == num) {
         document.getElementById(steps[i]).style.display = '';
         title += "<font color='#99000'>"+(i+1) + ". "+steps_txt[i]+"</font>";
      }
      else {
         document.getElementById(steps[i]).style.display = 'none';
         title += (i+1) + ". "+steps_txt[i];
      }
      if (i+1 != steps.length)
         title += " > ";
   }
   document.getElementById('title').innerHTML = title;
}

function finish()
{
   window.location = "bin/home.php";
}
//-->
</SCRIPT>
</head>
<body  style="background-image: url(images/main_bg.jpg);">

<h2>Welcome to the Demoapp Setup Wizard</h2>
<p>
This installer creates the Demoapp database tables and sets the configuration files that you need to start.
</p>
<p style="color:blue">
<b>I have done the installation, <a href="bin/home.php">bring me to home</a>.</b>
</p>
<p id='title' style="font-weight:bold">
<font color="#99000">1. System check</font> > 2. Database configuration > 3. Application configuration
</p>
<div id="container" style="border:1px solid gray; padding-left: 10px; padding-bottom:10px; background-image: url(images/main_bg.jpg); ">
<div id="step1" style="display:block">
<p>
<h4>Step 1: System check</h4>
</p>

<?php
$status[0]['item'] = 'Operation System';
$status[0]['value'] = PHP_OS;
$status[0]['status'] = 'OK';

$status[1]['item'] = 'PHP version';
$status[1]['value'] = PHP_VERSION;
//$ver_num = intval(str_replace('.', '', PHP_VERSION));
$status[1]['status'] = version_compare(PHP_VERSION, "5.1.4") >= 0 ? 'OK' : 'FAIL - Zend Framework required PHP5.1.4 or later';

$status[2]['item'] = 'Openbiz Path';
$status[2]['value'] = OPENBIZ_HOME;
$status[2]['status'] = "OK";
if (!file_exists(OPENBIZ_HOME))
$status[2]['status'] = "FAIL - OPENBIZ_HOME doesn't point to Openbiz installed path";

$status[3]['item'] = 'Zend Framework Path';
$status[3]['value'] = defined('ZEND_FRWK_HOME') ? ZEND_FRWK_HOME : 'Undefined';
if (defined('ZEND_FRWK_HOME') && !file_exists(ZEND_FRWK_HOME))
$status[3]['status'] = "FAIL - ZEND_FRWK_HOME doesn't point to Zend Framework installed path. Please modify ZEND_FRWK_HOME in ".OPENBIZ_HOME."/bin/sysheader.inc";
else if (defined('ZEND_FRWK_HOME') && file_exists(ZEND_FRWK_HOME))
$status[3]['status'] = 'OK';
else
$status[3]['status'] = 'FAIL';

if ($status[3]['status'] == 'OK')  {
require_once 'Zend/Version.php';
$status[4]['item'] = 'Zend Framework Version';
$status[4]['value'] = Zend_Version::VERSION;
$status[4]['status'] = Zend_Version::compareVersion('1.0.0') > 0 ? 'OK - Version 1.0.0 or later is recommended' : 'OK';
}

$status[5]['item'] = 'PDO extensions';
$pdos = array();
if (extension_loaded('pdo')) $pdos[] = "pdo";
if (extension_loaded('pdo_mysql')) $pdos[] = "pdo_mysql";
if (extension_loaded('pdo_mssql')) $pdos[] = "pdo_mssql";
if (extension_loaded('pdo_oci')) $pdos[] = "pdo_oci";
if (extension_loaded('pdo_pgsql')) $pdos[] = "pdo_pgsql";
$status[5]['value'] = implode(", ", $pdos);
$status[5]['status'] = $pdos[0]=='pdo' ? 'OK' : 'FAIL - PDO extensions are required.';

showStatus($status);
?>
<br>
<input type="button" value="Next >" onclick="return go_step(2);"/>
</div>

<div id="step2" style="display:none">
<p>
<h4>Step 2: Database configuration</h4>
</p>

<p>Please enter your database configuration information below. If you are
unsure of what to fill in, we suggest that you use the default values.</p>
<form id="setupform" name="setupform" method="post" action="index.php">
<table  border="1">
<tr>
    <th colspan="3" align="left">Database Configuration</td>
</tr>
<tr>
	<td>Database Type</td>
	<td>
    <SELECT NAME="dbtype">
    <OPTION VALUE="Pdo_Mysql"<?php if($_REQUEST['dbtype']=="Pdo_Mysql") echo " selected='selected'";?>>MySQL
    <OPTION VALUE="Pdo_Pgsql"<?php if($_REQUEST['dbtype']=="Pdo_Pgsql") echo " selected='selected'";?>>PostgreSQL
    <OPTION VALUE="Pdo_OCi"<?php if($_REQUEST['dbtype']=="Pdo_OCi") echo " selected='selected'";?>>Oracle 
    <OPTION VALUE="Pdo_Mssql"<?php if($_REQUEST['dbtype']=="Pdo_Mssql") echo " selected='selected'";?>>SQL Server
    </SELECT>
    </td>
</tr>
<tr>
	<td>Database Host Name</td>
	<td><input type="text" name="dbHostName" value="<?php echo  isset($_REQUEST['dbHostName']) ? $_REQUEST['dbHostName'] : 'localhost'?>" tabindex="1" ></td>
</tr>
<tr>
	<td>Database Host Port</td>
	<td><input type="text" maxlength="4" size="4" name="dbHostPort" value="<?php echo  isset($_REQUEST['dbHostPort']) ? $_REQUEST['dbHostPort'] : '3306'?>" tabindex="2" ></td>
</tr>
<tr>
	<td>Database Name</td>
	<td><input type="text" name="dbName" value="<?php echo  isset($_REQUEST['dbName']) ? $_REQUEST['dbName'] : 'demo'?>" tabindex="3"></td>
</tr>
<tr>
	<td >Priviledged Database Username</td>
	<td><input type="text" name="dbUserName" value="<?php echo  isset($_REQUEST['dbUserName']) ? $_REQUEST['dbUserName'] : 'root'?>" tabindex="4"> *</td>
</tr>
<tr>
	<td >Priviledged Database User Password</td>
	<td><input type="password" name="dbPassword" value="<?php echo  isset($_REQUEST['dbPassword']) ? $_REQUEST['dbPassword'] : ''?>" tabindex="5" > *</td>
</tr>
</table>
<br>
<input type="button" value="Create Database" onclick="create_db();"/>
<img id="createdb_img" src="images/indicator.white.gif" style="display:none"/>
<span id="create_db_result"></span>
<br><br>
<input type="button" value="Fill Database tables" onclick="fill_db();"/>
<img id="filldb_img" src="images/indicator.white.gif" style="display:none"/>
<span id="fill_db_result"></span>
</form>
<input type="button" value="< Back" onclick="go_step(1);"/>
<input type="button" value="Next >" onclick="go_step(3);"/>
</div>

<div id="step3" style="display:none">
<p>
<h4>Step 3: Application configuration</h4>
<p>Writable directories:</p>
<?php
unset($status);
$status[0]['item'] = 'Application Metadata path';
$status[0]['value'] = META_PATH;
$status[0]['status'] = is_writable(META_PATH) ? 'OK' : 'FAIL - not writable';

$status[1]['item'] = 'Openbiz Metadata path';
$status[1]['value'] = OPENBIZ_META;
$status[1]['status'] = is_writable(OPENBIZ_META) ? 'OK' : 'FAIL - not writable';

$status[2]['item'] = 'Session path';
$status[2]['value'] = SESSION_PATH;
$status[2]['status'] = is_writable(SESSION_PATH) ? 'OK' : 'FAIL - not writable';

$status[3]['item'] = 'Smarty template path';
$status[3]['value'] = SMARTY_TPL_PATH;
$status[3]['status'] = is_writable(SMARTY_TPL_PATH) ? 'OK' : 'FAIL - not writable';

$status[4]['item'] = 'Log path';
$status[4]['value'] = LOG_PATH;
$status[4]['status'] = is_writable(LOG_PATH) ? 'OK' : 'FAIL - not writable';

showStatus($status);
?>
<p>Set Default Database in metadata/Config.xml</p>
<div id="db_config">
</div>
<p>
<input type="button" value="Create Default database entry&#x0Awith the input from previous step" onclick="replace_db_cfg();"/>
<img id="replace_db_img" src="images/indicator.white.gif" style="display:none"/>
</p>
<b><font color=red>Note: if you are using Openbiz Eclipse plugin to edit metadata files, please update the database entry in eclipse config.xml</font></b>
</p>
<input type="button" value="< Back" onclick="go_step(2);"/>
<input type="button" value="Finish" onclick="finish();"/>
</div>
</div> <!--container-->
</body>
</html>