<?PHP
/**
 * Configuration class - Configuration management class that has help methods to get data from config.xml
 *
 * @package BizSystem
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access public
 */
class Configuration
{
   private $m_XmlArr;
   private $m_DatabaseInfo;

   /**
    * Configuration::__construct() - Initialize this object. Read Config.xml into a internal array.
    */
   public function __construct()
   {
      $xmlFile = BizSystem::GetXmlFileWithPath ("Config");
      $this->m_XmlArr = &BizSystem::GetXmlArray($xmlFile);
   }

   /**
    * Configuration::GetDatabaseInfo() - returns the <DataSource> defined in Config.xml as an array.
    * Returned array is a 2D map.
    * (DBName1 => ["Name"], ["Driver"], ["Server"], ["DBName"], ["User"], {"Password"])
    * (DBName2 => ["Name"], ["Driver"], ["Server"], ["DBName"], ["User"], {"Password"])
    * (...)
    * If DBName is given, returns the record only related to the given DBName, otherwise returns all records
    *
    * @param string $DBName
    * @return array
    */
   public function GetDatabaseInfo($DBName=null)
   {
      if ($DBName && $this->m_DatabaseInfo[$DBName])
         return $this->m_DatabaseInfo[$DBName];

      if (!$this->m_XmlArr["CONFIG"]["DATASOURCE"]) {
         $errmsg = BizSystem::GetMessage("ERROR", "SYS_ERROR_NODBINFO");
         trigger_error($errmsg, E_USER_ERROR);
      }

      $breakflag = false;
      foreach($this->m_XmlArr["CONFIG"]["DATASOURCE"]["DATABASE"] as $db) {
         if ($this->m_XmlArr["CONFIG"]["DATASOURCE"]["DATABASE"]["ATTRIBUTES"]) {
            $db = $this->m_XmlArr["CONFIG"]["DATASOURCE"]["DATABASE"];
            $breakflag = true;
         }
         $tmp["Name"] = $db["ATTRIBUTES"]["NAME"];
         $tmp["Driver"] = $db["ATTRIBUTES"]["DRIVER"];
         $tmp["Server"] = $db["ATTRIBUTES"]["SERVER"];
         $tmp["DBName"] = $db["ATTRIBUTES"]["DBNAME"];
         $tmp["User"] = $db["ATTRIBUTES"]["USER"];
         $tmp["Password"] = $db["ATTRIBUTES"]["PASSWORD"];
         $this->m_DatabaseInfo[$tmp["Name"]] = $tmp;
         if ($breakflag) break;
      }

      if ($DBName && $this->m_DatabaseInfo[$DBName])
         return $this->m_DatabaseInfo[$DBName];
      if ($DBName && !isset($this->m_DatabaseInfo[$DBName]))
      {
         $errmsg = BizSystem::GetMessage("ERROR", "BDO_ERROR_INVALID_DBNAME", array($DBName));
         trigger_error($errmsg, E_USER_ERROR);
      }

      if (!$DBName)
         return $tmp;
   }
}

?>