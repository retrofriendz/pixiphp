<?php

 define('USER_ACTIVATION_EXPIRY','+1 day');

abstract class CreateUserResult extends Enum {
 const Success=0;
 const InvalidEmail=1;
 const PasswordNotConfirmed=2;
 const UsernameAlreadyInUse=3;
 const UsernameNotValid=4;
 const PasswordInsecure=5;
 static function name($n) {
  switch(intval($n)) {
   case CreateUserResult::Success: return 'Success';
   case CreateUserResult::InvalidEmail: return 'Invalid Email';
   case CreateUserResult::PasswordNotConfirmed: return 'Password and Confirmation Do Not Match';
   case CreateUserResult::UsernameAlreadyInUse: return 'That username is already in use.';
   case CreateUserResult::UsernameNotValid: return 'Username is not valid.';
   case CreateUserResult::PasswordInsecure: return 'Password was not secure enough.  Try a different one.';
   default: return 'Unknown'; break;
  }
 }
};

 class User extends Model {

  // Called by automation for cron task
  static public function ExpireExpired() {
   global $auth_database;
   $m=new Session($auth_database);
   $m->Delete("emailVerified <> 1 AND activationExpires < ".strtotime('now'));
  }

  static public function GetDisplayName( $user ) {
   if ( !empty($user["nickname"]) ) return $user["nickname"];
   return $user["username"];
  }

  // Called in Session::CreateNew
  static public function LastIP( $user, $ip_addr ) {
   global $auth_database;
   $m=new User($auth_database);
   $m->Set($user['ID'],array("last_ip"=>$ip_addr));
  }

  static public function FindByEmail( $em ) {
   global $auth_database;
   $m=new User($auth_database);
   return $m->First('email',$em);
  }

  static public function FindByActivationKey( $k ) {
   global $auth_database;
   $m=new User($auth_database);
   $user=$m->First('activationKey',$k);
   if ( false_or_null($user) ) return NULL;
   //Turned off because it is not necessary when the crontask is running ExpireExpired():
   //if ( intval($user['activationExpires'] > strtotime('now') )
   // return FALSE;
   return $user;
  }

  static public function FindByUsername( $un ) {
   global $auth_database;
//   var_dump($auth_database);
   $m=new User($auth_database);
   return $m->First('username',$un);
  }

  static public function FindByID( $who, $id ) {
   // whatever logic you want here, made simple for example
   global $auth_database;
   $m=new User($auth_database);
   return $m->Get($id);
  }

  static public function DeleteByID( $id ) {
   global $auth,$is_admin,$is_manager,$auth_database;
   $target=User::Get($id);
   if ( false_or_null($target) ) return FALSE;
   if ( !$is_admin ) return FALSE;
   global $m; $m=new User($auth_database); $m->Delete(array("ID"=>$target["ID"]));
   return TRUE;
  }

  static public function CreateNew( $un, $em, $pw, $co=NULL ) {
   global $auth_database;
   $m=new User($auth_database);
   if ( strlen(trim($pw)) < 8 ) return NULL;
   if ( strlen(trim($un)) < 4 ) return NULL;
   $user=User::FindByUsername($un);
   if ( !false_or_null($user) ) return FALSE;
   $user=User::FindByEmail($em);
   if ( !false_or_null($user) ) return FALSE;

   $activationExpires=strtotime(USER_ACTIVATION_EXPIRY);
   $activationKey=b64k_encode(md5(uniqid($un.$activationExpires,true)));
   $filtered=array(
    'username'=>$un,
    'email'=>$em,
    'password'=>password_hash($pw,PASSWORD_DEFAULT),
    'emailVerified'=>0,
    'su'=>0,
    'admin'=>0,
    'twitter'=>'',
    'nickname'=>'',
    'banner'=>json_encode(array()),
    'steamname'=>'',
    'acl'=>'',
    'medals'=>json_encode(array()),
    'history'=>json_encode(array()),
    'activationKey'=>$activationKey,
    'activationExpires'=>$activationExpires
   );
   Email::Send( $em,
    "Activate Your Account",
    "To activate your account, click here: http://lostastronaut.com/pixigame/activate?key=".$activationKey
   );
   $new_id=$m->Insert($filtered);
   
   return $m->Get($new_id);
  }

  static public function Forgotten($key) {
   global $auth_database;
   $m=new User($auth_database);
   $user=$m->First("forgotKey",$key);
   if ( !false_or_null($user)
     && intval($user['forgotExpires']) > strtotime('now') ) return $user;
   return FALSE;
  }

  static public function PasswordReset($key,$pw) {
   $user=User::Forgotten($key);
   if ( !false_or_null($user) ) {
    global $auth_database;
    $m=new User($auth_database);
    $m->Update(array(
     "forgotKey"=>"",
     "forgotExpires"=>0,
     "password"=>password_hash($pw,PASSWORD_DEFAULT)
    ),array("ID"=>$user['ID']));
    return TRUE;
   }
   return FALSE;
  }

  static public function IncrementHistoryValue($user,$value,$amount=1) {
   $json=json_decode($user["history"],true);
   if ( isset($json[$value]) ) $json[$value]+=$amount;
   else $json[$value]=$amount;
   global $auth_database;
   $m=new User($auth_database);
   $m->Set($user["ID"],array("history"=>json_encode($json)));
  }

  static public function AddMedal($user,$name,$type,$icon,$description) {
   $json=json_decode($user["medals"],true);
   if ( !isset($json["medals"]) || !is_array($json["medals"]) ) $json["medals"]=array();
   $json["medals"][]=array("name"=>$name,"type"=>$type,"icon"=>$icon,"info"=>$description);
   global $auth_database;
   $m=new User($auth_database);
   $m->Set($user["ID"],array("medals"=>json_encode($json)));
  }

  static public function AddBadge($user,$badgecode,$name,$type,$icon,$description) {
   $json=json_decode($user["medals"],true);
   if ( !isset($json["badges"]) || !is_array($json["badges"]) ) $json["badges"]=array();
   $json["badges"][$badgecode]=array("name"=>$name,"type"=>$type,"icon"=>$icon,"info"=>$description);
   global $auth_database;
   $m=new User($auth_database);
   $m->Set($user["ID"],array("medals"=>json_encode($json)));
  }

  static public function ValidateEmail($user) {
   $m=new User($auth_database);
   $m->Update(
    array("emailVerified"=>1,
     "activationKey"=>"",
     "activationExpires"=>0
    ),array("ID"=>$user["ID"])
   );
  }

 };
