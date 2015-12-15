<?php

namespace OudyPlat;

/**
 * 
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class Request {
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
}