<?php

namespace OudyPlat;

class Template extends \OudyPlat\Object {
    public $name = '';
    public $version = '0.0.0';
    public $style = '';
    public $layout = 'html';
    public $styles = array();
    public $positions = array();
    public $classes = array();
    private $loaded = false;
    public static $configuration = null;
    public function __construct($data = null, $allowedProperties = null, $forceAll = false) {
        if(self::$configuration && !$this->loaded) {
            $this->load(self::$configuration->name);
            parent::__construct(self::$configuration);
            $this->loaded = true;
        }
        parent::__construct($data, $allowedProperties, $forceAll);
    }
    public function load($name) {
        if($template = include(TEMPLATES_PATH.$name.'/load.php'))
            parent::__construct($template);
    }
    /**
     * 
     * @param \OudyPlat\Page $page
     * @return \OudyPlat\Template
     */
    public function forPage(Page $page) {
        $template = clone $this;
        $template->__construct($page->template);
        return $template;
    }
}