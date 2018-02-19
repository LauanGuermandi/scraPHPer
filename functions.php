<?php
/* Functions.php */

/**
 * Function that return a body of request
 * @global int $limit
 * @global int $count
 * 
 * @param string $url
 * 
 * @return string $body
 */
function sendRequest( $url ){
    global $limit;
    global $count;

    $ch = curl_init();

    try{
        if($count <= $limit){
            curl_setopt($ch, CURLOPT_URL, $url); 
            curl_setopt($ch, CURLOPT_HEADER, TRUE);  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
            
            $count++;
            
            if($httpCode == 404 || $httpCode == 301) return false;
            
            return $result;
        }else{
            return false;
        }
    }catch(Exception $e){
        echo $e->getMessage();
    }
}

/**
 * Function that return links contained in the html
 * @param string $body
 * 
 * @return array $arrLinks
 */
function getLinks( $body ){

    $arrLinks = [];

    try{
        $doc = new DOMDocument();
        $errorsLib = libxml_use_internal_errors(true);
        $doc->loadHTML($body);
        libxml_use_internal_errors($errorsLib);

        $a = $doc->getElementsByTagName("a");
        
        foreach($a as $key => $value){
            $aux = $value->getAttribute("href");
            echo substr( $aux, 0, 1 ); 
            $arrLinks[] =  substr( $aux, 0, 1 ) != "/" ? $aux : "";
        }

        return array_unique($arrLinks);
    }catch(Exception $e){
            echo $e->getMessage();
        }
}

/**
 * Function that save links on json file
 * @param array $arrLinks
 * @param string $name
 * 
 * @return void
 */
function saveLinksJson( $arrLinks , $name){
    $arquivo = fopen($name.".json", "w");
    fwrite($arquivo, json_encode($arrLinks, JSON_UNESCAPED_SLASHES));
    fclose($arquivo);
}

/**
 * Function that send a request with NOBODY opt
 * @global curl_instance $ch 
 * 
 * @param string $url
 * 
 * @return int $httpCode
 */
function sendRequestNoBody( $url ){
    global $ch;

    if( $url == "") return;

    try{
        curl_setopt($ch, CURLOPT_URL, $url);   
        curl_setopt($ch, CURLOPT_NOBODY, TRUE); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

        $result = curl_exec($ch);
    }catch(Exception $e){
        $e->getMessage();
    }

    if($result == false ){
        echo "ERROR: " . curl_error($ch);
        die();
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 

    return $httpCode;
}

/**
 * Function that test a array of links
 * @global array $arrErrorLinks
 * 
 * @param array $arrLinks
 * 
 * @return void
 */
function testingLinks( $arrLinks ){
    global $arrErrorLinks;

    $result = null;

    foreach($arrLinks as $value){
        $result =  sendRequestNoBody( $value );
        if($result == 404 | $result == 301){
            $arrErrorLinks[$result][] =  $value;
        }
    }
}
