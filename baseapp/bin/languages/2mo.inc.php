<?php
include_once("../app.inc");

require_once(OPENBIZ_HOME."/bin/I18n.php");

$nl="\n";
if (isset($_SERVER['SERVER_ADDR']))
{
	$nl="<br/>";
}
define('NL',$nl);

$app_home=str_replace('\\','/',APP_HOME);
$dirparts=split('/', $app_home);
define('APP_DIRNAME',$dirparts[count($dirparts)-1]);


function endsWith( $str, $sub ) {
	return ( substr( $str, strlen( $str ) - strlen( $sub ) ) === $sub );
}

function dir2arrayXML($startDir,$xmlFiles)
{

	foreach (glob($startDir."/*",GLOB_ONLYDIR ) as $dir) {
		if (!endsWith($dir ,"__cmp") && !endsWith($dir,"service"))
		{
			$files=glob($dir."/"."*.xml");
			$xmlFiles=array_merge($xmlFiles,$files) ;
			$xmlFiles=dir2arrayXML($dir,$xmlFiles);
		}

	}
	return $xmlFiles;
}

function dir2arrayTPL($startDir,$tplFiles)
{
	
	$files=glob($startDir."/"."*.tpl");
	$tplFiles=array_merge($tplFiles,$files) ;
	
	foreach (glob($startDir."/*",GLOB_ONLYDIR ) as $dir) {
		if (!endsWith($dir ,"cpl") && !endsWith($dir,"cfg"))
		{
			$files=glob($dir."/"."*.tpl");
			$tplFiles=array_merge($tplFiles,$files) ;
			$tplFiles=dir2arrayTPL($dir,$tplFiles);
		}

	}
	
	return $tplFiles;
}

function writeTabsStrings($xmlArr,&$keys,$xmlFileShort)
{
	if (isset($xmlArr["TABS"]["TABVIEWS"]['VIEW']))
	{

		if (isset($xmlArr["TABS"]["TABVIEWS"]['VIEW']['ATTRIBUTES']))
		{
			$CAPTION = $xmlArr["TABS"]["TABVIEWS"]['VIEW']["ATTRIBUTES"]["CAPTION"];
			if ($CAPTION !='')
			{
				$keys[$CAPTION][]=$xmlFileShort;
			}
		}else{


			foreach ($xmlArr["TABS"]["TABVIEWS"]['VIEW'] as $view){

				$CAPTION = $view["ATTRIBUTES"]["CAPTION"];
				if ($CAPTION !='')
				{
					$keys[$CAPTION][]=$xmlFileShort;
				}
			}
		}
	}



}

function writeSelectionStrings($xmlArr,&$keys,$xmlFileShort){

	foreach ($xmlArr["SELECTION"] as $key=>$value){
		foreach ($value as $key2=>$value2){
			if (isset($value2["ATTRIBUTES"]["TEXT"]))
			{
				$txt = $value2["ATTRIBUTES"]["TEXT"];
				if ($txt != '')
				{
					$keys[$txt][]=$xmlFileShort;
				}
			}
			else
			{
				$val = $value2["ATTRIBUTES"]["VALUE"];
				if ($val !='')
				{
					$keys[$val][]=$xmlFileShort;
				}
			}

		}
	}
}

function writeMenuStrings($xmlArr,&$keys,$xmlFileShort)
{

	if (isset($xmlArr["ATTRIBUTES"]))
	{
		if (isset($xmlArr["ATTRIBUTES"]["CAPTION"]))
		{
			$CAPTION=$xmlArr["ATTRIBUTES"]["CAPTION"];

			if ($CAPTION !='')
			{
				$keys[$CAPTION][]=$xmlFileShort;
			}
		}
		if (isset($xmlArr["MENUITEM"]))
		{
			writeMenuStrings($xmlArr["MENUITEM"],$keys,$xmlFileShort);
		}
	}
	else{
		foreach ($xmlArr as $menuItem)
		{
			writeMenuStrings($menuItem,$keys,$xmlFileShort);
		}

	}

}

function writeBizFormStrings($xmlArr,&$keys,$xmlFileShort)
{

	$TITLE=$xmlArr["BIZFORM"]["ATTRIBUTES"]["TITLE"];
	if ($TITLE !='')
	{
		$keys[$TITLE][]=$xmlFileShort;
	}
	if (isset($xmlArr["BIZFORM"]["BIZCTRLLIST"]['BIZCTRL']))
	{
		if (isset($xmlArr["BIZFORM"]["BIZCTRLLIST"]['BIZCTRL']['ATTRIBUTES']))
		{
			$DISPLAYNAME = $xmlArr["BIZFORM"]["BIZCTRLLIST"]['BIZCTRL']["ATTRIBUTES"]["DISPLAYNAME"];
			if ($DISPLAYNAME !='')
			{
				$keys[$DISPLAYNAME][]=$xmlFileShort;


			}
		}
		else{


			foreach ($xmlArr["BIZFORM"]["BIZCTRLLIST"]['BIZCTRL'] as $bizControl)

			{
				$DISPLAYNAME = $bizControl["ATTRIBUTES"]["DISPLAYNAME"];
				if ($DISPLAYNAME !='')
				{
					$keys[$DISPLAYNAME][]=$xmlFileShort;


				}
			}
		}
	}
	if (isset($xmlArr["BIZFORM"]["TOOLBAR"]["CONTROL"]))
	{
		foreach ($xmlArr["BIZFORM"]["TOOLBAR"]["CONTROL"] as $tool)

		{
			$CAPTION = $tool["ATTRIBUTES"]["CAPTION"];
			if ($CAPTION !='')
			{
				$keys[$CAPTION][]=$xmlFileShort;

			}
		}
	}

	if (isset($xmlArr["BIZFORM"]["NAVBAR"]["CONTROL"]))
	{
		foreach ($xmlArr["BIZFORM"]["NAVBAR"]["CONTROL"] as $navelement)

		{
			$CAPTION = $navelement["ATTRIBUTES"]["CAPTION"];
			if ($CAPTION !='')
			{
				$keys[$CAPTION][]=$xmlFileShort;
			}
		}
	}

}

function getAllLanguages()
{
	$app_language_path= APP_HOME."/".I18n::LANGUAGE_PATH_1;
	$languages=array();
	foreach (glob($app_language_path."/*",GLOB_ONLYDIR ) as $dir) {
		$lang= basename($dir);
		if ($lang != 'tmp')
		{
			$languages[]=$lang;
		}
	}
	return $languages;
}
function getStringsFromXml($xmlFiles,&$keys)

{


	foreach ($xmlFiles as $xmlFile)
	{
		$xmlArr = BizSystem::GetXmlArray($xmlFile);
		$xmlFileShort=APP_DIRNAME.'/metadata'.substr($xmlFile,strlen(META_PATH));

		foreach($xmlArr as $key => $value)
		{
			if ($key == "BIZFORM")
			{
				
				print ">>> process BizForm file '$xmlFile'".NL;
				writeBizFormStrings($xmlArr,$keys,$xmlFileShort);
			}

			elseif($key == "TABS")
			{
				print ">>> process Tab file '$xmlFile'".NL;
				writeTabsStrings($xmlArr,$keys,$xmlFileShort);

			}
			elseif($key == "MENU"){
				$xmlArr=$xmlArr["MENU"]["MENUITEM"];
				print ">>> process Menu file '$xmlFile'".NL;
				writeMenuStrings($xmlArr,$keys,$xmlFile,$xmlFileShort);
			}
			elseif($key == "SELECTION"){
				print ">>> process Selection file '$xmlFile'".NL;
				writeSelectionStrings($xmlArr,$keys,$xmlFileShort);
			}


		}




	}


}

function writeStringsToPo($keys,$language)

{
	$app_language_path= APP_HOME."/".I18n::LANGUAGE_PATH_1;
	$app_language_path_tmp= $app_language_path."/tmp";
	$myFile = $app_language_path_tmp."/"."lang.".$language.".po";

	$fh = fopen($myFile, 'wb') or die("Error!!");


	$content="msgid \"\"\n";
	$content.="msgstr \"\"\n";

	$content.="\"Content-Type: text/plain; charset=utf-8\\n\"\n";
	$content.="\"Content-Transfer-Encoding: 8bit\\n\"\n";

	$nbNewKeys=0;
	foreach ($keys as $key=>$value)
	{
		$commentStr="";

		$isTranslated=I18n::getInstance()->isTranslated($key,$language);
		if ($isTranslated==false)
		{
			$nbNewKeys++;
		}
		$existingTranslation=I18n::getInstance()->translate2($key,$language);

		foreach ($value as $comment){
			$commentStr.="# ".$comment."\n";
		}
		$content.=$commentStr;

		$content.="msgid "."\"".$key."\""."\n";
		$content.="msgstr "."\"".$existingTranslation."\""."\n";
		$content.="\n";


	}
	echo NL.'Found '.$nbNewKeys." new key(s).".NL;
	
	fwrite($fh, $content);
	fclose($fh);

	$file_to_replace = $app_language_path."/".$language."/".I18n::LANGUAGE_PATH_2."/"."lang.".$language.".po";
	$result= @copy($myFile,$file_to_replace);
	print NL."########################################################################################";
	print NL."# wrote Language file '$file_to_replace'                                                ";
	print NL."# Please compile this file to .mo file with poedit ( http://www.poedit.net ).           ";
	print NL."########################################################################################".NL.NL;
	if ($result)
	{
		unlink($myFile);
	}
}



function getStringsFromTpl($tplFiles,&$keys){
	
	// smarty open tag
$ldq = preg_quote('{');

// smarty close tag
$rdq = preg_quote('}');

// smarty command
$cmd = preg_quote('t');

	foreach ($tplFiles as $tplFile)
	{
		$tplFileShort=APP_DIRNAME.'/templates'.substr($tplFile,strlen(SMARTY_TPL_PATH));
		$content = @file_get_contents($tplFile);
	    print ">>> process template file '$tplFile'".NL;
		if (empty($content)) {
			return;
		}
	
		
		preg_match_all(
				"/{$ldq}\s*({$cmd})\s*([^{$rdq}]*){$rdq}([^{$ldq}]*){$ldq}\/\\1{$rdq}/",
		$content,
		$matches
		);
		
		foreach ($matches[3] as $match)
		{
			$keys[$match][]=$tplFileShort;
		}
	}
	return $keys;
}
?>