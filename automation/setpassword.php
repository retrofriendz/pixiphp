<?php


if (PHP_SAPI === 'cli') {

 include "../core/Page.php";

 $user = array();
 $user['ID'] = 1;
 $new_password = "guestguest";

 var_dump( Auth::SetPassword($user,$new_password) );

}
