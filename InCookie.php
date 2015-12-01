<?php

defined('OUDY_EXEC') or die;

/**
 * Description of InCookie
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class InCookie {
	public static function set($key, $value, $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false) {
		setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
	}
	public static function get($key, $destroy = false) {
		$value = isset($_COOKIE[$key]) ? $_COOKIE[$key] : false;
		if($destroy) self::destroy($key);
		return $value;
	}
	public static function destroy($key) {
		setcookie($key, null, -1);
		unset($_COOKIE[$key]);
	}
}