<?php

namespace Config;

use PDO;
use PDOException;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

class Database{
    private static $pdo = null;

    public function dbConnection($pdo){
        if(self::$pdo !== null){
            return self::$pdo;
        }     
        $database = $_ENV[DB_NAME];
        $dsn = 'sqlite:' . $database;

        try{
            self::$pdo = new PDO($dsn);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            return self::$pdo;

        }catch(PDOException $e){
            http_response_code(500);
            header('Content_Type: application/json');

            die(json_encode([
                'status' => 'error',
                'message' => 'Database connection failed'
            ]));
        }
   }
}