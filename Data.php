<?php

defined('OUDY_EXEC') or die();

/**
 * Description of Data
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class Data {
    /**
	 * Clear array or object
	 * @param array $data
	 * @param string $keys
	 * @param boolean $all
	 * @return array
	 */
	public static function clearArray($data, $keys, $all = false) {
		$keys = explode(',', $keys);
		$return = array();
		if($all) {
			foreach($keys as $key) $return[$key] = isset($data[$key]) ? $data[$key] : null;
		} else {
			foreach($data as $key=>$value) if(in_array($key, $keys)) $return[$key] = $value;
		}
		return $return;
	}
	public static function maskEmail($email, $mask_char = '*', $percent=80) {
		if(empty($email)) return $email;
		list($user, $domain) = preg_split('/@/',$email);
		return substr_replace($user, str_repeat('*', strlen($user)-2), 1, strlen($user)-2).'@'.substr_replace($domain, str_repeat('*', strlen($domain)-2), 1, strlen($domain)-2);
	}
}
