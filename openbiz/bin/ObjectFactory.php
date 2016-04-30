<?PHP

/**
 * ObjectFactory class - fatory class to create metadata based objects (bizview, bizform, bizdataobj...)
 * 
 * @package BizSystem
 * @author rocky swen 
 * @copyright Copyright (c) 2005
 * @access public 
 */
class ObjectFactory
{
   protected $m_ObjsRefMap = array();
   
   public function __construct() { }
   
   public function __destruct()
   {
      //echo "<br>destruct ObjectFactory";
   }
   
   /**
    * ObjectFactory::GetObject() - To get a metadata based object instance. 
    * It returns the instance the internal object map or create a new one and save it in the map.
    * 
    * @param string $objName
    * @return object
    */
   public function GetObject($objName)
   {
      if (array_key_exists($objName, $this->m_ObjsRefMap)) {
         return $this->m_ObjsRefMap[$objName];
      }
      global $g_BizSystem;
      $obj = $this->Construct_obj($objName);
      if ($obj)
         $this->m_ObjsRefMap[$objName] = $obj;
      if (method_exists($obj, "GetSessionVars"))
         $obj->GetSessionVars($g_BizSystem->GetSessionContext());
      return $obj;
   }
   
   /**
    * ObjectFactory::CreateObject() - To create a new metadata based object instance
    * 
    * @param string $objName
    * @param array $xmlArr
    * @return object
    */
   public function CreateObject($objName, &$xmlArr=null)
   {
      global $g_BizSystem;
      $obj = $this->Construct_obj($objName, $xmlArr);
      //$obj->GetSessionVars($g_BizSystem->GetSessionContext());
      return $obj;
   }
   
   /**
    * ObjectFactory::GetAllObjects() - returns the internal object array
    * 
    * @return array
    */
   public function GetAllObjects()
   {
      return $this->m_ObjsRefMap;
   }
   
   protected function Construct_obj($objName, &$xmlArr=null)
   {
      if (!$xmlArr) {
         $xmlFile = BizSystem::GetXmlFileWithPath ($objName);
         if (!$xmlFile) 
            $class = $objName;
         else 
            $xmlArr = BizSystem::GetXmlArray($xmlFile);
      }
      if ($xmlArr) {
         $keys = array_keys($xmlArr);
         $root = $keys[0];
         //$package = $xmlArr[$root]["ATTRIBUTES"]["PACKAGE"];
         $class = $xmlArr[$root]["ATTRIBUTES"]["CLASS"];
         // if class has package name as prefix, change the package to the prefix
         $dotPos = strrpos($class, ".");
         $classPrefix = $dotPos>0 ? substr($class, 0, $dotPos) : null;
         $package = $classPrefix ? $classPrefix : $xmlArr[$root]["ATTRIBUTES"]["PACKAGE"];
         if ($classPrefix) $class = substr($class, $dotPos+1);
      }

      if ($class == "BizObj")  // convert BizObj to BizDataObj, support <1.2 version
         $class = "BizDataObj";
         
      if (!class_exists($class, false)) {
         $classFile= BizSystem::GetLibFileWithPath($class, $package);
         if (!$classFile) {
            trigger_error("Cannot find the class with name as $package.$class", E_USER_ERROR);
            exit();
         }
         include_once($classFile);
      }
      if (class_exists($class, false))
      {
         $obj_ref = new $class($xmlArr);
         if ($obj_ref) {
            return $obj_ref;
         }
      }
      return null;
   }
}

?>