<?php

namespace OudyPlat;

class MongoDB {
    private static function getConnection() {
        if(is_null(self::$connection))
            self::$connection = new \MongoClient('mongodb://'.self::$configuration->host.'/'.self::$configuration->database);
        
        self::$connection;
    }
}