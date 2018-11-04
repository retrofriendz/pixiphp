<?php

 include 'core/Page.php';
 
 $g=getpost();

 $user=User::FindByActivationKey( $g["key"] );

 if ( !false_or_null($user) ) { 
  User::ValidateEmail($user);
?>
Congratulations!
Your account has been activated.
Use the game client to log in with your password!
<?php } else { ?>
Sorry, your activation key was invalid.  Please sign up again.
<?php
}
