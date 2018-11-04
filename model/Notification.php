<?php

 define('NOTIFICATION_EXPIRY',"+5 minutes");

 class Notification extends Model {

   static function Received($user,$note_id) {
    global $database;
    $m=new Notification($database);
    if ( intval($m["r_User"]) === intval($user["ID"]) )
     $m->Delete(array("ID"=>$note_id));
   }

  static function Write( $user_id, $content, $expiry=-1 ) {
   global $database;
   $now=strtotime('now');
   $m=new Notification($database);
   $m->Insert(array(
    "Sent"=>$now,
    "Expiry"=>strtotime(NOTIFICATION_EXPIRY),
    "r_User"=>$user_id,
    "Content"=>json_encode($content)
   ));
  }
  
  static function byUser( $user ) {
   global $database;
   $m=new Notification($database);
   $m->ExpireExpired();
   return $m->By("r_User",$user["ID"]);
  }
 
  static function ExpireExpired() {
   global $database;
   $m=new Notification($database);
   $now=strtotime('now');
   return $m->Delete("Expiry <> 0 AND Expiry < $now");
  }

  static function Expire( $message ) {
   global $database;
   $m=new Notification($database);
   $m->Delete(array('ID'=>$message["ID"]));
  }

 };
