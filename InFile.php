<?php

defined('OUDY_EXEC') or die;

/**
 * Description of InFile
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class InFile {
	public static function issr($f) {
		return (isset($_FILES[$f]) && $_FILES[$f]) ? $_FILES[$f] : false;
	}
	public static function iss($f) {
		return isset($_FILES[$f]) ? true : false;
	}
}