<?php

require_once 'Zend/Translate.php';
require_once 'Zend/Locale.php';

class I18n{

	private $zTrans= array();
	const LANGUAGE_PATH_1="languages";
	const LANGUAGE_PATH_2="LC_MESSAGES";
	const DEFAULT_LANGUAGE=DEFAULT_LANGUAGE;
	private static $singleton =null;
	private $zLocale = null;
	private $m_curLang = null;

	private function __construct()
	{
		$this->zLocale=new Zend_Locale();
	}
	public static function getInstance()
	{
		if (!isset(self::$singleton)) {
			$c = __CLASS__;
			self::$singleton = new $c;
		}

		return self::$singleton;

	}
	public function translate2($str,$lang=null)
	{
		$translation =$str;
		if ($lang==null)
		{
			$lang=$this->getCurrentLanguage();
		}
		else
		{
			if (!isset($this->zTrans[$lang]))
			{
				$this->loadLanguage($lang);
			}
		}

		if (isset($this->zTrans[$lang]) && $str!="")
		{
			$zTransApp=$this->zTrans[$lang]['app'];
			$zTransPHPopenbiz=$this->zTrans[$lang]['phpopenbiz'];
			$isTranslated=false;
			if ($zTransApp != null)
			{
				$translation = $zTransApp->translate($str);
				$isTranslated=$zTransApp->getAdapter()->isTranslated($str);
			}
			if (!$isTranslated && $zTransPHPopenbiz!= null) {
				$translation=$zTransPHPopenbiz->translate($str);
			}

		}

		return $translation;
	}
	public function translate($str)

	{

		return $this->translate2($str);
	}
	public function isTranslated($str,$lang=null)
	{
		if ($lang==null)
		{
			$lang=$this->getCurrentLanguage();
		}
		else
		{
			if (!isset($this->zTrans[$lang]))
			{
				$result=$this->loadLanguage($lang);
				if ($result== false)
				{
					return false;
				}
			}
		}
		$zTransApp=$this->zTrans[$lang]['app'];

		$isTranslated=$zTransApp->getAdapter()->isTranslated($str);
		return $isTranslated;
	}

	private function loadLanguage($lang)
	{

		$app_language_path= APP_HOME."/".I18n::LANGUAGE_PATH_1."/".$lang."/".I18n::LANGUAGE_PATH_2."/"."lang.".$lang.".mo";
		$phpopenbiz_language_path= OPENBIZ_HOME."/".I18n::LANGUAGE_PATH_1."/".$lang."/".I18n::LANGUAGE_PATH_2."/"."lang.".$lang.".mo";
		$noMoAppFile=false;
		if (file_exists($app_language_path))
		{
		   try
   		{
   			$zTransApp=new Zend_Translate('gettext',$app_language_path, $lang);
   			$this->zTrans[$lang]['app']=$zTransApp;
   		}
   		catch(Exception $e)
   		{
   			$noAppMoFile= true;
   		}
		}

		$noPHPopenbizMoFile= false;
      if (file_exists($phpopenbiz_language_path))
      {
   		try
   		{
   			$zTransPHPopenbiz= new Zend_Translate('gettext',$phpopenbiz_language_path, $lang);
   			$this->zTrans[$lang]['phpopenbiz']=$zTransPHPopenbiz;
   		}
   		catch(Exception $e)
   		{
   			$noPHPopenbizMoFile= true;
   		}
      }

		$result = true;
		if ($noPHPopenbizMoFile == true && $noAppMoFile == true)
		{
			$result= false;
		}
		else{
			if ($noPHPopenbizMoFile == true )
			{
				$this->zTrans[$lang]['phpopenbiz']=null;
			}
			if ($noAppMoFile == true )
			{
				$this->zTrans[$lang]['app']=null;
			}
		}
		return $result;
	}

	public function  getBestRegionFromBrowser(){
		$region = $this->zLocale->getRegion();
		if ($region === FALSE)
		{
			return null;
		}
		return $region;
	}


	//locale relies on browser setting

	public function getLocaleForSetlocaleWin()
	{
		$zEnglishLocale=new Zend_Locale("en");
		$current_language=$this->getCurrentLanguage();

		$acceptedLangsByBrowser=$this->zLocale->getBrowser();
		array_multisort($acceptedLangsByBrowser,SORT_DESC, SORT_NUMERIC);
		if ($acceptedLangsByBrowser != null )
		{

			foreach ($acceptedLangsByBrowser as $acceptedLang=>$quality)
			{
				$locale = explode('_', $acceptedLang);
				if ($current_language==$acceptedLang || $current_language==$locale[0])
				{

					$language=$zEnglishLocale->getLanguageTranslation($locale[0]);
					$country=null;
					if (isset($locale[1]))
					{
						$country=$this->get3from2($locale[1]);
					}
					$windowsCode=$language.($country!=null?"_".$country:"");
					return $windowsCode;
				}

			}

		}
		$current_language_winlocale=$zEnglishLocale->getLanguageTranslation($current_language);
		return $current_language_winlocale;
	}

	public function getCurrentLanguageShort()
	{
		$current_language=$this->getCurrentLanguage();
		$parts=explode('_', $current_language);
		$current_language_short=$parts[0];
		return $current_language_short;
	}

	// if browser = es_AR and no es_AR.mo but es.mo
	// load es.mo
	public function getBestAvailableLanguageFromBrowser(){

		$acceptedLangsByBrowser=$this->zLocale->getBrowser();
		array_multisort($acceptedLangsByBrowser,SORT_DESC, SORT_NUMERIC);
		$current_language=I18n::DEFAULT_LANGUAGE;
		if ($acceptedLangsByBrowser != null )
		{
			foreach ($acceptedLangsByBrowser as $acceptedLang=>$quality)
			{

				$isAvailable=$this->loadLanguage($acceptedLang);
				if ($isAvailable === FALSE){
					$parts=explode('_', $acceptedLang);
					$isAvailable=$this->loadLanguage($parts[0]);
				}
				if ($isAvailable)
				{
					$current_language=$acceptedLang;
					break;
				}
			}

		}
		return $current_language;
	}

	public function getCurrentLanguage()
	{
		if ($this->m_curLang != null)
		return $this->m_curLang;

		global $g_BizSystem;
		$sessionContext=$g_BizSystem->GetSessionContext();
		$current_language=$g_BizSystem->GetSessionContext()->GetVar("LANG");

		if ($current_language == "")
		{
			$current_language=I18n::getBestAvailableLanguageFromBrowser();
			$sessionContext->SetVar("LANG",$current_language);
		}

		if (isset($_REQUEST['lang']))
		{
			$requested_lang=$_REQUEST['lang'];
			if (!isset($this->zTrans[$requested_lang]))
			{
				$result=$this->loadLanguage($requested_lang);
				if ($result == false)
				{
					$parts=explode('_', $requested_lang);
					$requested_lang=$parts[0];
					$result=$this->loadLanguage($requested_lang);
				}
				if ($result==true)
				{
					$current_language=$requested_lang;
					$sessionContext->SetVar("LANG",$requested_lang);
				}


			}
		}
		if (!isset($this->zTrans[$current_language]))
		{
			$this->loadLanguage($current_language);
		}

		$this->m_curLang = $current_language;
		return $current_language;
	}

	function get3from2($code2)
	{
		$code2=strtoupper($code2);
		if (isset($this->two2three[$code2])){
			return $this->two2three[$code2];
		}
		return null;
	}

	public $two2three=array("AF"=>"AFG","AL"=>"ALB",
	"DZ"=>"DZA",
	"AS"=>"ASM",
	"AD"=>"AND",
	"AO"=>"AGO",
	"AI"=>"AIA",
	"AQ"=>"ATA",
	"AG"=>"ATG",
	"AR"=>"ARG",
	"AM"=>"ARM",
	"AW"=>"ABW",
	"AU"=>"AUS",
	"AT"=>"AUT",
	"AZ"=>"AZE",
	"BS"=>"BHS",
	"BH"=>"BHR",
	"BD"=>"BGD",
	"BB"=>"BRB",
	"BY"=>"BLR",
	"BE"=>"BEL",
	"BZ"=>"BLZ",
	"BJ"=>"BEN",
	"BM"=>"BMU",
	"BT"=>"BTN",
	"BO"=>"BOL",
	"BA"=>"BIH",
	"BW"=>"BWA",
	"BV"=>"BVT",
	"BR"=>"BRA",
	"IO"=>"IOT",
	"BN"=>"BRN",
	"BG"=>"BGR",
	"BF"=>"BFA",
	"BI"=>"BDI",
	"KH"=>"KHM",
	"CM"=>"CMR",
	"CA"=>"CAN",
	"CV"=>"CPV",
	"KY"=>"CYM",
	"CF"=>"CAF",
	"TD"=>"TCD",
	"CL"=>"CHL",
	"CN"=>"CHN",
	"CX"=>"CXR",
	"CC"=>"CCK",
	"CO"=>"COL",
	"KM"=>"COM",
	"CG"=>"COG",
	"CK"=>"COK",
	"CR"=>"CRI",
	"CI"=>"CIV",
	"HR"=>"HRV",
	"CU"=>"CUB",
	"CY"=>"CYP",
	"CZ"=>"CZE",
	"DK"=>"DNK",
	"DJ"=>"DJI",
	"DM"=>"DMA",
	"DO"=>"DOM",
	"TL"=>"TLS",
	"EC"=>"ECU",
	"EG"=>"EGY",
	"SV"=>"SLV",
	"GQ"=>"GNQ",
	"ER"=>"ERI",
	"EE"=>"EST",
	"ET"=>"ETH",
	"FK"=>"FLK",
	"FO"=>"FRO",
	"FJ"=>"FJI",
	"FI"=>"FIN",
	"FR"=>"FRA",
	"FX"=>"FXX",
	"GF"=>"GUF",
	"PF"=>"PYF",
	"TF"=>"ATF",
	"GA"=>"GAB",
	"GM"=>"GMB",
	"GE"=>"GEO",
	"DE"=>"DEU",
	"GH"=>"GHA",
	"GI"=>"GIB",
	"GR"=>"GRC",
	"GL"=>"GRL",
	"GD"=>"GRD",
	"GP"=>"GLP",
	"GU"=>"GUM",
	"GT"=>"GTM",
	"GN"=>"GIN",
	"GW"=>"GNB",
	"GY"=>"GUY",
	"HT"=>"HTI",
	"HM"=>"HMD",
	"HN"=>"HND",
	"HK"=>"HKG",
	"HU"=>"HUN",
	"IS"=>"ISL",
	"IN"=>"IND",
	"ID"=>"IDN",
	"IR"=>"IRN",
	"IQ"=>"IRQ",
	"IE"=>"IRL",
	"IL"=>"ISR",
	"IT"=>"ITA",
	"JM"=>"JAM",
	"JP"=>"JPN",
	"JO"=>"JOR",
	"KZ"=>"KAZ",
	"KE"=>"KEN",
	"KI"=>"KIR",
	"KP"=>"PRK",
	"KR"=>"KOR",
	"KW"=>"KWT",
	"KG"=>"KGZ",
	"LA"=>"LAO",
	"LV"=>"LVA",
	"LB"=>"LBN",
	"LS"=>"LSO",
	"LR"=>"LBR",
	"LY"=>"LBY",
	"LI"=>"LIE",
	"LT"=>"LTU",
	"LU"=>"LUX",
	"MO"=>"MAC",
	"MK"=>"MKD",
	"MG"=>"MDG",
	"MW"=>"MWI",
	"MY"=>"MYS",
	"MV"=>"MDV",
	"ML"=>"MLI",
	"MT"=>"MLT",
	"MH"=>"MHL",
	"MQ"=>"MTQ",
	"MR"=>"MRT",
	"MU"=>"MUS",
	"YT"=>"MYT",
	"MX"=>"MEX",
	"FM"=>"FSM",
	"MD"=>"MDA",
	"MC"=>"MCO",
	"MN"=>"MNG",
	"MS"=>"MSR",
	"MA"=>"MAR",
	"MZ"=>"MOZ",
	"MM"=>"MMR",
	"NA"=>"NAM",
	"NR"=>"NRU",
	"NP"=>"NPL",
	"NL"=>"NLD",
	"AN"=>"ANT",
	"NC"=>"NCL",
	"NZ"=>"NZL",
	"NI"=>"NIC",
	"NE"=>"NER",
	"NG"=>"NGA",
	"NU"=>"NIU",
	"NF"=>"NFK",
	"MP"=>"MNP",
	"NO"=>"NOR",
	"OM"=>"OMN",
	"PK"=>"PAK",
	"PW"=>"PLW",
	"PA"=>"PAN",
	"PG"=>"PNG",
	"PY"=>"PRY",
	"PE"=>"PER",
	"PH"=>"PHL",
	"PN"=>"PCN",
	"PL"=>"POL",
	"PT"=>"PRT",
	"PR"=>"PRI",
	"QA"=>"QAT",
	"RE"=>"REU",
	"RO"=>"ROU",
	"RU"=>"RUS",
	"RW"=>"RWA",
	"KN"=>"KNA",
	"LC"=>"LCA",
	"VC"=>"VCT",
	"WS"=>"WSM",
	"SM"=>"SMR",
	"ST"=>"STP",
	"SA"=>"SAU",
	"SN"=>"SEN",
	"SC"=>"SYC",
	"SL"=>"SLE",
	"SG"=>"SGP",
	"SK"=>"SVK",
	"SI"=>"SVN",
	"SB"=>"SLB",
	"SO"=>"SOM",
	"ZA"=>"ZAF",
	"ES"=>"ESP",
	"LK"=>"LKA",
	"SH"=>"SHN",
	"PM"=>"SPM",
	"SD"=>"SDN",
	"SR"=>"SUR",
	"SJ"=>"SJM",
	"SZ"=>"SWZ",
	"SE"=>"SWE",
	"CH"=>"CHE",
	"SY"=>"SYR",
	"TW"=>"TWN",
	"TJ"=>"TJK",
	"TZ"=>"TZA",
	" 	"=>"   ",
	"TH"=>"THA",
	"TG"=>"TGO",
	"TK"=>"TKL",
	"TO"=>"TON",
	"TT"=>"TTO",
	"TN"=>"TUN",
	"TR"=>"TUR",
	"TM"=>"TKM",
	"TC"=>"TCA",
	"TV"=>"TUV",
	"UG"=>"UGA",
	"UA"=>"UKR",
	"AE"=>"ARE",
	"GB"=>"GBR",
	"US"=>"USA",
	"UM"=>"UMI",
	"UY"=>"URY",
	"UZ"=>"UZB",
	"VU"=>"VUT",
	"VA"=>"VAT",
	"VE"=>"VEN",
	"VN"=>"VNM",
	"VG"=>"VGB",
	"VI"=>"VIR",
	"WF"=>"WLF",
	"EH"=>"ESH",
	"YE"=>"YEM",
	"YU"=>"YUG",
	"ZR"=>"ZAR",
	"ZM"=>"ZMB",
	"ZW"=>"ZWE");

}
?>