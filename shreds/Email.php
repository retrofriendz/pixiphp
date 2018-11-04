<?php

 abstract class Email {

  static function Send($to_email, $subject, $message) {
   mail( $to_email, $subject, $message . "From: no-reply@".host." X-Mailer: php" );
  }

 };
