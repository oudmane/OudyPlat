<?php

defined('OUDY_EXEC') or die;

/**
 * Description of Language
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class Language {
	public $defaultLanguage = '';
	/**
	 * Default or Set Language
	 * @var String
	 */
	public static $language = null;
	/**
	 * Loaded component laguages
	 * @var Array
	 */
	private static $components = array();
	/**
	 * Loaded Translates
	 * @var Array
	 */
	private static $strings = array();
	/**
	 * Initialize Language Object
	 * @param string $language
	 * @param array $components
	 */
	public function __construct($language, $components = array()) {
		self::$language = $language;
		if($components) self::addComponent($components);
	}
	/**
	 * add Components language
	 * @param string|array $components
	 */
	public static function addComponent($components) {
		if(gettype($components) == 'array') {
			foreach($components as $component) self::addComponent ($component);
		} else {
			if(!in_array($components, Language::$components)) {
				$component = strtolower($components);
				if(file_exists(COMPONENTS_PATH.$component.DS.'languages'.DS.Language::$language.'.ini')) {
					self::$strings = array_merge(self::$strings,parse_ini_file(COMPONENTS_PATH.$component.DS.'languages'.DS.Language::$language.'.ini'));
				} else if(defined('PARENT_COMPONENTS_PATH') && file_exists(PARENT_COMPONENTS_PATH.$component.DS.'languages'.DS.Language::$language.'.ini')) {
					self::$strings = array_merge(self::$strings,parse_ini_file(PARENT_COMPONENTS_PATH.$component.DS.'languages'.DS.Language::$language.'.ini'));
				}
				array_push(self::$components, $component);
			}
		}
	}
	/**
	 * Change language
	 * @param string $language
	 */
	public function change($language) {
		if($language != self::$language) {
		$components = Language::$components;
			Language::$components = array();
			Language::$strings = array();
			$this->__construct($language, $components);
		}
	}
	/**
	 * Return Language String
	 * @param string $string
	 * @param array $values
	 * @param bool $added
	 * @return string
	 */
	public function returnString($string, $values = array(), $added = false) {
		if(!$added && !isset(self::$strings[strtoupper($string)])) {
			$str = explode('_', strtoupper($string));
			if(count($str)==1) return $string;
			self::addComponent($str[0]);
			return $this->returnString($string, $values, true);	
		} else if($added && !isset(self::$strings[strtoupper($string)])) {
			return $string;
		}
		$string = strtoupper($string);
		$string = self::$strings[$string];
		if($values) foreach($values as $key=>$value) {
			$string = str_replace('{'.$key.'}', $value, $string);
		}
		return $string;
	}
	/**
	 * echo Language String
	 * @param string $string
	 * @param array $data
	 */
	public function printString($string, $data = array()) {
		echo $this->returnString($string, $data);
	}
	/**
	 * shortcut for returnString()
	 * @param string $string
	 * @param array $data
	 * @return string
	 */
	public function r($string, $data = array()) {
		return $this->returnString($string, $data);
	}
	/**
	 * shortcut for printString()
	 * @param string $string
	 * @param array $data
	 */
	public function s($string, $data = array()) {
		echo $this->returnString($string, $data);
	}
	public function direction() {
		return Language::$language == 'ar' ? 'rtl' : 'ltr';
	}
	public function rMustache() {
		$language = $this;
		return function($string, $data = array()) use($language) {
			return $language->returnString($string, $data);
		};
	}
	public function __isset($string) {
		return true;
	}
	public function __get($string) {
		return $this->returnString($string);
	}
}