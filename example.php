<?php
    
    /**
     * example
     */


  require 'vendor/autoload.php';

  use depakin\admobssv\PublicKey;
  use depakin\admobssv\Signature;

  // google query string
  $query_string = '';

  $Signature = new Signature($query_string);

  var_dump( $Signature->verify() );

    /* -- output
    array(2) {
      'code' =>
      int(0)
      'message' =>
      string(8) "success!"
    }
     */