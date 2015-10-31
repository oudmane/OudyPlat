<?php

defined('OUDY_EXEC') or die;

/**
 * Description of Template
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class Template extends Object {
	/**
	 * Template Name
	 * @var string
	 */
	public $name = 'default';
	/**
	 * Template Layout
	 * @var sting
	 */
	public $layout = 'html';
	/**
	 * Template Style
	 * @var string
	 */
	public $style = 'default';
	/**
	 * Template Version
	 * @var string
	 */
	public $version = '0.0.0';
	/**
	 * Template Modules Positions
	 * @var array
	 */
	public $modules = array();
	/**
	 * Template Styles
	 * @var string
	 */
	public $styles = array();
	/**
	 * Initialize Template
	 * @param string|array $template
	 */
	public function __construct($template) {
		// switch on $template type
		switch(gettype($template)) {
			case 'string':
				// if it's a string load it
				$this->load($template);
				break;
			case 'object':
			case 'array':
				// if it's an arrar or object
				// make sure it's an object
				$template = new Object($template);
				// load the defaults of the templates 
				$this->load($template->name);
				// assign this $template
				parent::__construct($template);
				break;
		}
	}
	/**
	 * Initialize Template
	 * @param string $template
	 */
	public function load($template) {
		// get the template.json, parse it, and load it.
		parent::__construct(json_decode(file_get_contents(TEMPLATES_PATH.$template.DS.'template.json')));
	}
}