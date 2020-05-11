<?php
    
    /**
     * example
     */

    require_once('publickey.php');
    require_once('signature.php');

    use depakin\PublicKey;
    use depakin\Signature;


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