<?php

defined('OUDY_EXEC') or die ('EXEC Not Defined');

// set execution time limit to infinite
set_time_limit(0);

// disable error loging
ini_set('log_errors', 0);

// set displaying error
ini_set('display_errors', isset($_COOKIE['debug']));

// set default time zone to GMT
date_default_timezone_set('GMT');

if(!defined('OUDYPLAT_PATH')) define('OUDYPLAT_PATH', OUDYPLAT_MASTER);

if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])) $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];

// define Directory Seperator alias
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if(!defined('PROTOCOL')) define('PROTOCOL', empty($_SERVER['HTTPS']) ? 'http' : 'https');
if(!defined('SECURE')) define('SECURE', empty($_SERVER['HTTPS']) ? false : true);

// register autoload directory
spl_autoload_register(function($class) {
	//	check if this class exist in OudyPlat directory
	if(file_exists(OUDYPLAT_PATH.$class.'.php')) {
		// include the file from OudyPlat directory
		include_once OUDYPLAT_PATH.$class.'.php';
	//	check if this class exist in this libraries directory
	} else if(defined('LIBRARIES_PATH') && file_exists(LIBRARIES_PATH.$class.'.php')) {
		// include the file from libraries directory
		include_once LIBRARIES_PATH.$class.'.php';
	} else if(class_exists('Application')) {
		// search in Application libraries
		foreach(Application::$libraries as $directory) {			
			//	check if this class exist in this component directory
			if(file_exists($directory.$class.'.php')) {
				// include the file from this component directory
				include_once $directory.$class.'.php';
			}
		}
	}
});