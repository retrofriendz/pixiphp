<?php

 // Just an example, refactor this code to generate basic enumerator classes for use in config.enums.php

 $new_type_name="CreateUserResult";
 $starts_at=0;
 $input=array(
  "InvalidEmail"=>"Invalid Email",
  "PasswordNotConfirmed"=>"Password and Confirmation Do Not Match",
  "UsernameAlreadyInUse"=>"That username is already in use.",
  "UsernameNotValid"=>"Username is not valid.",
  "PasswordInsecure"=>"Password was not secure enough.  Try a different one."
 );

 echo '
abstract class '.$new_type_name.' extends Enum {
';
 $i=$starts_at;
 foreach ( $input as $symbol=>$description ) {
  echo ' const '.$symbol.'='.$i.';
';
  $i++;
 }
 echo ' static function name($n) {
  switch(intval($n)) {
';
 foreach ( $input as $symbol=>$description ) {
  echo "   case $new_type_name::$symbol: return '$description';".PHP_EOL;
 }
 echo "   default: return 'Unknown'; break;
  }
 }
}
";
