<?php
namespace src\Utility;

class UtilityHelper{
    
    public static function sendJson($status, $message,  $code, $extra=[]){
        http_response_code($code);
        $response = array_merge([
            "status" => $status,
            "message" => $message
        ], $extra);
        echo json_encode($response);
        exit();
    }

    public static function generateToken($token){

        $token = "";
    }

    public static function isActive(){

    }

    public static function sendEmailVerification(){

    }

    public static function hashValue(string $value){
        return password_hash($value, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyValue(string $value, string $hash){
        return password_verify($value,  $hash);
    }
     
    
}