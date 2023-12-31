<?php

function httpCustomErrorHandler($errno, $errstr, $errfile, $errline){

    if(!APP::isInDev()) APP::res(404, array("error" => $errstr));

    APP::res(500, array(
        "error" => array(
            "code" => $errno, 
            "message" => $errstr, 
        ),
        "fileName" => $errfile, 
        "lineNumber" => $errline
    ));
}

function customErrorHandler(){
    $error = error_get_last();
    if ($error !== null && $error['type'] === E_ERROR) {
        
        $temp = explode('Stack trace:', $error['message'])[0];
        $temp = explode(' in ', $temp);
        $errorMessage = str_replace('"', "*", $temp[0]);
        if(!APP::isInDev()) APP::res(404, array("error" => $errorMessage));

        $errorFilePath = 'Unknown';
        $errorLineNo = 'NA';
        if(count($temp) == 2){
            $temp = explode('.php:', $temp[1]);
            $errorFilePath = $temp[0].'.php';
            $errorLineNo = $temp[1];
        }

        APP::res(500, array(
            "error" => array(
                "code" => 100, 
                "message" => $errorMessage, 
            ),
            "fileName" => $errorFilePath, 
            "lineNumber" => (int) $errorLineNo
        ));

    }
}

set_error_handler("httpCustomErrorHandler");
register_shutdown_function('customErrorHandler');

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

?>
