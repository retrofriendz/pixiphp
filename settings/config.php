<?php

define('pepper','generatesomethinghere');
define('form_salt','entersomethinghere');

define('sitename','-');
define('site','http://127.0.0.1/');
define('site_','http://127.0.0.1');
define('domain','.somewheres.com');
define('host','somewheres.com');

define('AUTH_TIMEOUT',1000*60*5);  // in millis
define('timeout', 60*5);       // number of seconds until cookie expires


/*
 * Without these headers, we diss incoming sockets.
 */

define('MY_APP_ID',     "12345abcde");      //'X-Papi-Application-Id'
define('MY_ADMIN_TOKEN',"3d3d3d");    //'X-Papi-Admin-Token'
