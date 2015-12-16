<?php

namespace OudyPlat;

/**
 * 
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class Template extends Object {
    public static $configuration = null;
    public $id = 0;
    public $name = '';
    public $style = '';
    public $layout = 'html';
    public $version = '0.0.0';
    public function __construct($data) {
        if(self::$configuration) {
            $template = self::$configuration->name;
            parent::__construct($template);
        }
        parent::__construct($data);
    }
}