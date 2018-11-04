<?php

  class Message extends Model {

   static function Received($user,$msg_id) {
    global $database;
    $m=new Message($database);
    if ( intval($m["r_User"]) === intval($user["ID"]) )
     $m->Delete(array("ID"=>$msg_id));
   }

   static function Send($reference_id,$type,$recipient,$expiry,$content,$json=array()) {
    $message=b64k_encode($content);
    global $database;
    $m=new Chat($database);
    $m->Insert(array(
     "Reference"=>$reference_id, // Originator (Game,etc)
     "Type"=>$type,
     "Created"=>strtotime('now'),
     "Expiry"=>$expiry,
     "r_User"=>$recipient["ID"],
     "Content"=>$message,
     "JSON"=>json_encode($json)
     )
    );
   }

   // Call this periodically in a cron task
   static function RemoveStale() {
    global $database;
    $m=new Message($database);
    $m->Delete("Expiry <> 0 AND Expiry < ".strtotime('now'));
   }

  };
