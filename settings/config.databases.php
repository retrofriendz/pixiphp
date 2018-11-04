<?php

// Set the site's Auth DB here

define('AUTH_DB_DSN', 'mysql:dbname=pixigamedb;host=localhost;port=3306');
define('AUTH_DB_USER','<yourdbuser>');
define('AUTH_DB_PASS','<yourdbpassword>');

// Add your application database below,
// and in core/Auth.php:
// and in core/automation.php
// modify to connect to it after connecting to auth db

