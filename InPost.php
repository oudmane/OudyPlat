<?php

defined('OUDY_EXEC') or die;

/**
 * Description of InPost
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class InPost {
	/**
	 * Return if $key exist in $_POST
	 * @param String $key
	 * @return Boolean
	 */
	public static function exist($key) {
		return isset($_POST[$key]) ? true : false;
	}
	/**
	 * return the value of $key in $_POST
	 * @param String $key
	 * @return String
	 */
	public static function get($keys) {
		if(is_array($keys)) {
			$return = array();
			foreach($keys as $key) {
				if($value = self::get($key)) $return[$key] = $value;
			}
		}
		return (isset($_POST[$keys]) && $_POST[$keys]) ? $_POST[$keys] : false;
	}
	public static function issr($key) {
		return (isset($_POST[$key]) && $_POST[$key]) ? $_POST[$key] : false;
	}
	public static function iss($key) {
		return isset($_POST[$key]) ? true : false;
	}
	public static function issrt($key, $destroy = false) {
		return self::issr($key);
		$token = Form::token($key);
		if($token && InPost::issr($token->key) == $token->value) {
			return (isset($_POST[$key]) && $_POST[$key]) ? $_POST[$key] : false;
		}
		return false;
	}
}