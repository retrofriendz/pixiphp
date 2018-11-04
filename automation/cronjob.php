<?php

 if ( PHP_SAPI !== 'cli' ) die;

 include "../core/Page.php";

 global $database;
 $m = new Notification($database);
 $m->ExpireExpired();

 $m = new Message($database);
 $m->RemoveStale();

 $m = new Session($database);
 $m->ExpireExpired();

 $m = new Chat($database);
 $m->RemoveStale();
