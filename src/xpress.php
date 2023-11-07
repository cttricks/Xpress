<?php

include_once __DIR__ .'/errorHandler.php';

class APP {

    private static $contentType = '';
    private static $isInDev = false;
    private static $request;
    private static $responseCode=0;
    private static $response;

    private function __construct(){}

    public static function debugger($status=false){
        self::$isInDev = $status;
    }

    public static function isInDev(){
        return self::$isInDev;
    }

    public static function use($endpoint='',$routerLocation=''){
        if(empty($endpoint)) return;
        if(empty($routerLocation)) return;

        $currentURI = explode('?', $_SERVER['REQUEST_URI'])[0];

        //Check if the rout is similar
        if(substr($currentURI, 0, strlen($endpoint)) !== $endpoint) return;

        //remove the rout part from the URI
        $currentURI = substr($currentURI, strlen($endpoint), (strlen($endpoint)+strlen($currentURI)));
        $_SERVER['REQUEST_URI'] = (empty($currentURI))?'/':$currentURI;

        $routerLocation = str_replace('.php', '', $routerLocation);
        if(!is_file($routerLocation.'.php')){
            self::res(404, array(
                "error" => 'Route '. $routerLocation . ' does not exsit at given location'
            ));
        }
        
        include_once $routerLocation.'.php';
        self::end();
    }

    private static function validateEndpointAndMethod(){
        //Get the name of calling function
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $backTraceCount = count($backtrace);
        if($backTraceCount<2) return false;
        if(!isset($backtrace[1]['function'])) return false;
        $method = strtoupper($backtrace[1]['function']);
        $endpoint = $backtrace[1]['args'][0];
        
        //Validate endpoint
        if(!$endpoint || $_SERVER['REQUEST_METHOD'] !== $method) return false;

        //On exact match
        if($endpoint == explode('?', $_SERVER['REQUEST_URI'])[0]){
            self::setRequestData();
            return true;
        }

        //Else, loop through endpoint chunks & find exact match with inline params
        $endpointArray = explode('/', $endpoint);
        $reqEndpointArray = explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]);
        if(count($endpointArray) !== count($reqEndpointArray)) return false;

        array_shift($endpointArray);
        array_shift($reqEndpointArray);

        self::setRequestData();

        $finalStatus = true;
        $count=0;
        foreach($endpointArray as $item){
            if(!$finalStatus) break;
            if(mb_substr($item, 0, 1) == ':'){
                self::$request[str_replace(':','', $item)] = $reqEndpointArray[$count];
                $count++;
                continue;
            }
            if($item !== $reqEndpointArray[$count]) $finalStatus = false;
            $count++;
        }
        return $finalStatus;
        
    }

    private static function setRequestData(){
        $finalData = array();
        if($_SERVER['REQUEST_METHOD'] == 'GET'){
            $finalData = $_GET;
        }else if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
                $json = file_get_contents('php://input');
                $finalData = @json_decode($json, true);
                $finalData = ($finalData)?$finalData:array();
            } else {
                $finalData = $_POST;
            }
        }

        //Set request data
        self::$request = $finalData;
    }

    //Handel methods
    public static function get($endpoint='', callable $function){
        if(self::validateEndpointAndMethod()) self::executeReq($function);
    }

    public static function post($endpoint='', callable $function){
        if(self::validateEndpointAndMethod()) self::executeReq($function);
    }

    public static function put($endpoint='', callable $function){
        if(self::validateEndpointAndMethod()) self::executeReq($function);
    }

    public static function delete($endpoint='', callable $function){
        if(self::validateEndpointAndMethod()) self::executeReq($function);
    }

    //Execute request
    private static function executeReq(callable $function){
        try{
            $function(self::$request, new self());
        }catch(Exception $e){
            self::$responseCode = 500;
            self::$response = array('error' => 'Failed to execute, '. $e->getMessage());
        }
        self::end();
    }

    //Response Compose
    public static function status($code=200){
        self::$responseCode = $code;
        return new self();
    }
    
    public static function send($data=''){
        if(self::$responseCode == 0) self::$responseCode = 200;
        if(gettype($data) == 'array'){
            self::$contentType = "application/json";
            $data = json_encode($data);
        }
        self::$response = $data;
        self::end();
    }

    public static function json($data=array()){
        if(self::$responseCode == 0) self::$responseCode = 200;
        self::$contentType = "application/json";
        if(gettype($data) !== 'array') $data = array($data);
        self::$response = json_encode($data);
        self::end();
    }

    public static function redirect($endpoint='',$path='',$status=302,$includeParams=false){
        $currentEndpoint = explode('?', $_SERVER['REQUEST_URI']);
        if($endpoint != $currentEndpoint[0]) return;

        if(!empty($path)){
            $path = ($includeParams && count($currentEndpoint)==2)?$path.'?'.$currentEndpoint[1]:$path;
            header('Location: '.$path, true, $status);
            exit;
        }

        self::status(404)->send('No target found for redirect! Please check your codes');
    }

    public static function end(){
        if(self::$responseCode == 0) self::$responseCode = 404;
        if(!self::$response){
            self::$response = json_encode(array('error' => "endpoint not avaialbe"));
            self::$contentType = "application/json";
        }

        http_response_code(self::$responseCode);
        if(!empty(self::$contentType)) header("Content-Type: ". self::$contentType);
        die(self::$response);
    }

    public static function res($code, $data){
        self::$responseCode = $code;
        self::$response = $data;

        if(gettype(self::$response) == 'array'){
            self::$contentType = 'application/json';
            self::$response = json_encode(self::$response);
        }else{
            self::$contentType = 'text/plain';
        }
        self::end();
    }

    public static function req($key='', $defaulValue=null){
        return (strlen($key)>0 && isset(self::$request[$key]))?self::$request[$key]:$defaulValue;
    }

    public static function Authorization($defaulValue=null){
        $tokenInfo = array(
            'type' => 'No Auth',
            'value' => $defaulValue
        );

        $authorizationHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : $defaulValue;
        if (preg_match('/Basic\s+(.+)/i', $authorizationHeader, $matches)) {
            $tokenInfo['type'] = 'Basic';
            $tokenInfo['value'] = $matches[1];
        } elseif (preg_match('/Bearer\s+(.+)/i', $authorizationHeader, $matches)) {
            $tokenInfo['type'] = 'Bearer';
            $tokenInfo['value'] = $matches[1];
        }
        return $tokenInfo;
    }
}
?>
