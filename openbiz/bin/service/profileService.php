<?php
/**
 * @package PluginService
 */
/**
 * profileService - 
 * class profileService is the plug-in service for getting user profile
 * 
 * @package PluginService
 * @author rocky swen 
 * @copyright Copyright (c) 2003
 * @version $Id: profileService.php,v 1.1 2006/04/07 08:01:29 rockys Exp $
 * @access public 
 */
class profileService
{
   public $role;
   public $roleId;
   public $group;
   public $groupId;
   public $position;
   public $positionId;
   public $division;
   public $divisionId;
   public $org;
   public $orgId;
   
   public function __construct() {}
   
   public function GetProfile($userid=null)
   {
      // with the userid, query for role, group, position, division, org
      if (!$userid) return null;
      
      $profile['USERID'] = $userid;
      // just make up something for test. must be replace in real application
      if ($userid == "admin")
         $profile['ROLE'] = 'admin';
      else if ($userid == "member")
         $profile['ROLE'] = 'member';
      $profile['ROLEID'] = 'RLE_2';
      $profile['GROUP'] = 'Member';
      $profile['GROUPID'] = 'GRP_3';
      $profile['POSITION'] = 'Marketing - France';
      $profile['POSITIONID'] = 'PSN_10';
      $profile['DIVISION'] = 'Marketing - Europe';
      $profile['DIVISIONID'] = 'DVN_5';
      $profile['ORG'] = 'IBM';
      $profile['ORGID'] = 'ORG_101';
      
      return $profile;
   }
   
   protected function GetDBProfile($userid, $password)
   {
      // CASE 1: simple one table query
      // SELECT role, group, pstn, divn, org FROm user_table AS t1 
      // WHERE t1.userid='$userid'
      
      // CASE 2: intersection table user_pstn (user_role, user_divn, user_org ...), need to query multiple times
      // SELECT t1.pstnid, t2.name FROM user_pstn_table AS t1 
      // JOIN pstn_table AS t2 ON t1.pstnid=t2.id 
      // WHERE t1.userid='$userid'
      
      // CASE 3: all hierarchy info contained in one big party table, do query once, then filter on type column
      // SELECT t1.partyid, t2.name, t2.type FROM user_party_table AS t1 
      // JOIN party_table AS t2 ON t1.partyid=t2.id 
      // WHERE t1.userid='$userid'
      
      global $g_BizSystem;
      $db = $g_BizSystem->GetDBConnection();
      $resultSet = $db->Execute($sql);
      $sqlArr = $resultSet->FetchRow();
      // process the result
   }
   
   public function GetAttribute($userid, $attr)
   {
      
   }
}

?>