<?php
include_once("2mo.inc.php");
//main
$xmlFiles=dir2arrayXML(META_PATH,array());
$tplFiles=dir2arrayTPL(SMARTY_TPL_PATH,array());
$languages=getAllLanguages();

foreach ($languages as $language){
	$keys=array();
	echo "Creating '$language' language file.".NL;
	getStringsFromXml($xmlFiles,$keys);
	$nbStringXML=count($keys);
	echo 'Extracted '.$nbStringXML.' strings from XML files.'.NL.NL;
	getStringsFromTpl($tplFiles,$keys);
	echo 'Extracted '.(count($keys)-$nbStringXML).' strings from template files.'.NL;
	writeStringsToPo($keys,$language);
	
}

?>

