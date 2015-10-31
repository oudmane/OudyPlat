<?php

defined('OUDY_EXEC') or die;

/**
 * Description of Page
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class Page extends Object {
	/**
	 * Identifer for the page
	 * @var int
	 */
	public $id = 0;
	/**
	 * Component name
	 * @var string
	 */
	public $component = 'error';
	/**
	 * Task
	 * @var string
	 */
	public $task = '404';
	/**
	 * pre data for the page
	 * @var object|array
	 */
	public $data = null;
	/**
	 * page Template
	 * @var string
	 */
	public $template = null;
	/**
	 * page Layout
	 * @var string
	 */
	public $layout = null;
	/**
	 * Page Classes
	 * @var array
	 */
	public $classes = array();
	/**
	 * Page Modules
	 * @var array
	 */
	public $modules = array();
	/**
	 * Page Active positions
	 * @var array
	 */
	public $positions = array();
	/**
	 * URL
	 * @var URL
	 */
	public $url = null;
	/**
	 * To store definded pages
	 * @var array
	 */
	public static $pages = null;
	/**
	 * Initialize Page
	 * @param string|array|object $page Page to load
	 */
	const types = 'url:URL';
	public function __construct($page = array()) {
		if($page) {
			if(gettype($page) == 'string') {
				$this->url = new URL($page);
				if(isset(self::$pages[$this->url->path])) {
					parent::__construct(self::$pages[$this->url->path]);
				} else if(isset(self::$pages['/'.$this->url->paths[0]])) {
					parent::__construct(self::$pages['/'.$this->url->paths[0]]);
				}
			} else {
				parent::__construct($page);
			}
		}
	}
	/**
	 * add Module to a position
	 * @param string $position
	 * @param string $module
	 */
	public function setModule($position, $module) {
		if(!isset($this->modules[$position])) $this->modules[$position] = array();
		if(!in_array($module, $this->modules[$position])) $this->modules[$position][] = $module;
	}
	/**
	 * remove Module from a position
	 * @param string $position
	 * @param string $module
	 */
	public function unsetModule($position, $module) {
		if(isset($this->modules[$position])) {
			$i = array_search($module, $this->modules[$position]);
			if($i!==false) unset($this->modules[$position][$i]);
			if(!$this->modules[$position]) unset($this->modules[$position]);
		}
	}
	/**
	 * return filled positions
	 * @param string $positions
	 * @return boolean
	 */
	public function count($positions) {
		$exist = 0;
		foreach(explode(' + ', $positions) as $position) if(isset($this->modules[$position])) $exist++;
		return $exist;
	}
	/**
	 * Set CSS Class to a position
	 * @param string $position
	 * @param string $class
	 */
	public function setClass($position, $class) {
		$this->classes[$position] = $class;
	}
	/**
	 * get Class of a position
	 * @param string $position
	 * @return string
	 */
	public function getClass($position, $default = '') {
		if(isset($this->classes[$position])) {
			return $this->classes[$position];
		} else {
			return $default;
		}
	}
}