<?php

namespace OudyPlat;

class Request {
    /**
     *
     * @var \OudyPlat\URL 
     */
    public static $url = null;
    public static function getBody() {
        return file_get_contents('php://input');
    }
    public static function getJSONBody() {
        return json_decode(self::getBody());
    }
    public static function getCookie($name, $destroy = false) {
        if(!isset($_COOKIE[$name]))
            return null;
        
        $cookie = $_COOKIE[$name];
        
        if($destroy)
            self::destroyCookie($name);
        
        return $cookie;
    }
    public static function setCookie($key, $value, $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false) {
        setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function destroyCookie($name) {
        setcookie($name, null, -1);
        unset($_COOKIE[$name]);
    }
    public static function issetParam($param, $from = array('POST', 'GET', 'PATH')) {
        foreach($from as $source) {
            $function = 'isset'.ucfirst(strtolower($source));
            return self::$function($param);
        }
        return false;
    }
    public static function getParam($param, $from = array('POST', 'GET', 'PATH')) {
        foreach($from as $source) {
            $function = 'isset'.ucfirst(strtolower($source));
            if(self::$function($param)) {
                $function = 'get'.ucfirst(strtolower($source));
                return self::$function($param);
            }
        }
        return false;
    }
    public static function issetPost($key) {
        return isset($_POST[$key]);
    }
    public static function getPost($key) {
        if(self::issetPost($key)) {
            return $_POST[$key];
        } else
            return false;
    }
    public static function issetGet($key) {
        return isset($_GET[$key]);
    }
    public static function getGet($key) {
        if(self::issetGet($key)) {
            return $_GET[$key];
        } else
            return false;
    }
    public static function issetFile($key) {
        return isset($_FILES[$key]);
    }
    public static function getFile($key) {
        if(self::issetFile($key)) {
            return $_FILES[$key];
        } else
            return false;
    }
    public static function issetPath($key) {
        if(is_null(self::$url))
            self::$url = new URL($_SERVER['REQUEST_URI']);
        return self::$url->inPath($key);
    }
    public static function getPath($key) {
        if(is_null(self::$url))
            self::$url = new URL($_SERVER['REQUEST_URI']);
        return self::$url->inPath($key, true);
    }
}