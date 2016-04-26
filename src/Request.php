<?php

namespace OudyPlat;

class Request {
    public $get = array();
    public $post = array();
    public $files = array();
    /**
     *
     * @var \OudyPlat\URL 
     */
    public $url = array();
    public function __construct() {
        
    }
    public function load($param, $values) {
        $this->$param = $values;
    }
//    public static function getBody() {
//        return file_get_contents('php://input');
//    }
//    public static function getJSONBody() {
//        return json_decode(self::getBody());
//    }
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
            return $this->$function($param);
        }
        return false;
    }
    public static function getParam($param, $from = array('POST', 'GET', 'PATH')) {
        foreach($from as $source) {
            $function = 'isset'.ucfirst(strtolower($source));
            if(self::$function($param)) {
                $function = 'get'.ucfirst(strtolower($source));
                return $this->$function($param);
            }
        }
        return false;
    }
    public function issetPost($key) {
        return isset($this->post[$key]);
    }
    public function getPost($key) {
        if($this->issetPost($key)) {
            return $this->post[$key];
        } else
            return false;
    }
    public function issetGet($key) {
        return isset($this->get[$key]);
    }
    public function getGet($key) {
        if($this->issetGet($key)) {
            return $this->get[$key];
        } else
            return false;
    }
    public function issetFile($key) {
        return isset($this->files[$key]);
    }
    public function getFile($key) {
        if($this->issetFile($key)) {
            return $this->files[$key];
        } else
            return false;
    }
    public function issetPath($key) {
        if(!is_null($this->url))
            return $this->url->inPath($key);
        else
            return false;
    }
    public function getPath($key) {
        if(!is_null($this->url))
            return $this->url->inPath($key, true);
        else
            return false;
    }
}