<?php
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
   
   public function GetProfile($userid)
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
      $profile['ROLELIST'] = '15, 20';
      
      return $profile;
      
      //return $this->GetDBProfile($userid);      
   }
   
   protected function GetDBProfile($userid, $password=null)
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
      $sql = "SELECT T1.RoleId, T1.PositionId, T1.DivisionId, T1.TeamId 
              FROM ob_user AS T0, ob_person AS T1 
              WHERE T0.Login='$userid' AND T0.PersonId=T1.RowId";
      $db = $g_BizSystem->GetDBConnection();
      $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
      $resultSet = $db->Execute($sql);
      if ($resultSet === false) {
         $err = $db->ErrorMsg();
         echo $err;
         exit;
      }
      $sqlArr = $resultSet->FetchRow();
      // process the result
      $profile['USERID'] = $userid;
      $profile['ROLEID'] = $sqlArr[0];
      $profile['ROLE'] = $profile['ROLEID'];
      $profile['POSITIONID'] = $sqlArr[1];
      $profile['DIVISIONID'] = $sqlArr[2];
      $profile['TEAMID'] = $sqlArr[3];
      print_r($profile);
      return $profile;
   }
   
   public function GetAttribute($userid, $attr)
   {
      
   }
}

?>