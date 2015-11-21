<?php

defined('OUDY_EXEC') or die;

/**
 * Description of InGet
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class InGet {
	/**
	 * Return if $key exist in $_GET
	 * @param string $key
	 * @return boolean
	 */
	public static function exist($key) {
		return isset($_GET[$key]) ? true : false;
	}
	/**
	 * return the value of $key in $_GET
	 * @param string|array $keys
	 * @return string|array
	 */
	public static function get($keys) {
		if(is_array($keys)) {
			$return = array();
			foreach($keys as $key)
				if($value = self::get($key))
					$return[$key] = $value;
			return $return;
		}
		return (isset($_GET[$keys]) && $_GET[$keys]) ? $_GET[$keys] : false;
	}
}
