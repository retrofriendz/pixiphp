<?php

function startsWith($haystack, $needle){     $length = strlen($needle);     return (substr($haystack, 0, $length) === $needle);}
function is_ssl() {
return true;
// uncomment the next line and comment out the line above to properly check for ssl, requires a cert
// if ( isset($_SERVER['HTTPS']) ) { if ( 'on' == strtolower($_SERVER['HTTPS']) ) return true; if ( '1' == $_SERVER['HTTPS'] ) return true; } elseif ( isset($_SERVER['SERVER_PORT']) && ( 443 == intval($_SERVER['SERVER_PORT']) ) ) return true; return false;
}
if ( !is_ssl() ) { // The following block is used to restrict access to the insecure version, and bump users to the secure one.
 if (  !startsWith($_SERVER["REMOTE_ADDR"],"127.")
    && !startsWith($_SERVER["REMOTE_ADDR"],"52.")
    && !startsWith($_SERVER["REMOTE_ADDR"],"172.") ) {
  echo 'Access denied to '.$_SERVER['REMOTE_ADDR']; die;
//  header("Location: https://api.mydomain.com"); die;
 }
}

 global $plog_level; $plog_level=1;
 include 'core/Page.php';

 global $TODAY;
 $TODAY = strtotime('now');

 $g=getpost();

 if ( !isset($g["data"]) ) Auth::EndTransmit("Nothing was sent or malformed request.",-1,$g);

 $j=json_decode($g["data"],true);
 if ( is_null($j) ) Auth::EndTransmit("JSON from post 'data' was malformed.",102,$g);

 if ( !isset($j["action"]) ) Auth::EndTransmit("JSON 'action' was not specified.");

 $action = $j["action"];

 new API("index",$action,$j,$g);
