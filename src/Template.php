<?php

class Template extends \OudyPlat\Object {
    public $name = '';
    public $version = '0.0.0';
    public $style = '';
    public $layout = 'html';
    public static $configuration = null;
    public function __construct($data = null, $allowedProperties = null, $forceAll = false) {
        if(self::$configuration) {
            $this->load(self::$configuration->name);
            parent::__construct(self::$configuration);
        }
        parent::__construct($data, $allowedProperties, $forceAll);
    }
    public function load($name) {
        if($template = include(TEMPLATES_PATH.$name.'/load.php'))
            parent::__construct($template);
    }
}