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
    public $classes = array();
    public function __construct($data = null) {
        if(self::$configuration)
            $this->load(self::$configuration->name);
        
        if($data)
            parent::__construct($data);
    }
    public function load($name) {
        if(file_exists($template = TEMPLATES_PATH.$name.DIRECTORY_SEPARATOR.'template.json'))
            parent::__construct(json_decode(file_get_contents($template)));
        else
            die($name.' template not installed');
    }
    public function mergeClasses($classes) {
        foreach ($classes as $position => $classes) {
            if(isset($classes->set))
                $this->classes->$position = $classes->set;
            if(isset($classes->add))
                $this->classes->$position = array_unique(array_merge($this->classes->$position, $classes->add));
        }
    }
    public function getClass($position) {
        if(isset($this->classes->$position))
            if(in_array('uk-hidden', $this->classes->$position))
                return 'uk-hidden';
            else
                return implode(' ', $this->classes->$position);
        else
            return '';
    }
}