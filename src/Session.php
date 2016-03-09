<?php

namespace OudyPlat;

class Session {
    public $id = '';
    public $data = array();
    public $ip = '';
    public static $configuration = null;
    public function __construct() {
        if(!is_null(self::$configuration)) {
            $configuration = new Object(array(
                'cookie'=> 'session',
                'expiration'=> strtotime('6 months', 0),
                'path'=> '/',
                'domain'=> '',
                'handler'=> false
            ));
            $configuration->__construct(self::$configuration);
            self::$configuration = $configuration;
            $this->id = Request::getCookie($configuration->cookie);
            if(!$this->id)
                Request::setCookie($configuration->cookie, $this->id = Crypt::genRandomPassword(32), time()+$configuration->expiration, $configuration->path, $configuration->domain);
            $this->ip = $_SERVER['REMOTE_ADDR'];
            if(!$configuration->handler) {
                session_id($this->id);
                session_name($configuration->cookie);
                if(session_status() == PHP_SESSION_NONE)
                    session_start();
                $this->data =& $_SESSION;
            }
        }
    }
    public function set($key, $value) {
        $this->data[$key] = $value;
    }
    public function get($key, $remove = false) {
        if(!isset($this->data[$key]))
            return null;
        $value = $this->data[$key];
        if($remove)
            $this->remove($key);
        return $value;
    }
    public function remove($key) {
        unset($this->data[$key]);
    }
    public static function getHandler() {
        if(is_null(Session::$configuration))
            return false;
        else if(isset(Session::$configuration->handler))
            return Session::$configuration->handler;
        return false;
    }
}