<?php
namespace src\Config;

use PDO;
use PDOException;
use RuntimeException;


class Database{
    private static ?PDO $pdo = null;

    public static function getDatabaseConnection(){
        if(self::$pdo !== null){
            return self::$pdo;
        }

        $database = dirname(__DIR__, 2) . '/' .$_ENV['DB_PATH'];
        $dsn = "sqlite:$database";
        try{
            self::$pdo = new PDO($dsn);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return self::$pdo;
        }catch(PDOException $e){
            throw new RuntimeException('Database connection failed.', 500, $e);
        }
    }
}