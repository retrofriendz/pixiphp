<?php

  define('CHAT_STALENESS',"2 minutes ago");

  class Chat extends Model {

   static function Received($user,$chat_id) {
    global $database;
    $m=new Chat($database);
    if ( intval($m["Destination"]) === intval($user["ID"]) )
     $m->Delete(array("ID"=>$chat_id));
   }

   static function To($user,$recipient,$content) {
    $message=b64k_encode($content);
    global $database;
    $m=new Chat($database);
    $m->Insert(array(
     "Originator"=>$user["ID"],
     "Destination"=>$recipient["ID"],
     "Created"=>strtotime('now'),
     "Content"=>$message,
     "Type"=>0
     )
    );
   }

   static function Say($user,$game,$content) {
    $message=b64k_encode($content);
    global $database;
    $m=new Chat($database);
    $m->Insert(array(
     "Originator"=>$user["ID"],
     "Destination"=>$game["ID"],
     "Created"=>strtotime('now'),
     "Content"=>$message,
     "Type"=>1
     )
    );
   }

   // Call this periodically in a cron task
   static function RemoveStale() {
    global $database;
    $m=new Chat($database);
    $m->Delete("Created < ".strtotime(CHAT_STALENESS));
   }

  };
