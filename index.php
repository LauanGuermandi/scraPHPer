<?php

require_once 'functions.php';

$ch                 = curl_init();
$arrLinks           = [];
$arrLinksError      = [];
$pageLinks          = [];

$url                = readline("Insert the initial URL to test: ");
$limit              = readline("Insert the limit of pages to test: ");
$pathJsonLinks      = readline("Insert the path to json links file(Optional): ");

if( file_exists( $pathJsonLinks ) ){
    $jsonLinks      = file_get_contents( $pathJsonLinks );
    $arrLinks       = json_decode( $jsonLinks, true, JSON_UNNESCAPED_SLASHES );

    testingLinks( $arrLinks );

    if( !is_null( $arrLinksError ) ) saveLinksJson( $arrLinksError, "LinksError" );
}else{

    $result         = sendRequest( $url );
    
    for($i=0; $result != false; $i++){
        $arrLinks   = array_merge( getLinks( $result ), $arrLinks );
        $result     = sendRequest( $arrLinks[ $i ] );
    }
    
    testingLinks( $arrLinks );

    if( !is_null( $arrLinksError ) ) saveLinksJson( $arrLinksError, "LinksError" );
}

curl_close( $ch );