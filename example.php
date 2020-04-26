<?php


    require_once('publickey.php');
    require_once('signature.php');


    $query_string = 'ad_network=s&ad_unit=5697876904&custom_data=s&reward_amount=1s&reward_item=gold&timestamp=1587884528349&transaction_id=aaaa&signature=aaa&key_id=3335741209';


    $Signature = new Signature($query_string);

    var_dump( $Signature->verify() );