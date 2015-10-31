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
	 * @param array $template
	 */
	public function __construct($template) {
		parent::__construct(json_decode(file_get_contents(TEMPLATES_PATH.$template['name'].DS.'template.json')));
		parent::__construct($template);
	}
}