<?php

namespace OudyPlat;

class Page extends Object {
    public $id = 0;
    public $component = 'error';
    public $task = '404';
    public $data = null;
    public $title = '';
    public $description;
    public $keywords = array();
    public $canonical = '';
    public $shortlink = '';
    public $metadata = array();
    public $modules = array();
    public $url = null;
    /**
     *
     * @var \OudyPlat\Template
     */
    public $template = null;
    public static $pages = null;
    public function __construct($data = null, $allowedProperties = null, $forceAll = false) {
        parent::__construct($data, $allowedProperties, $forceAll);
        $this->data = new Object();
    }
    public function loadByPageURL($url) {
        $this->url = clone $url;
        if($url->paths) {
            for($i = count($url->paths); $i > 0; $i--) {
                if(isset(self::$pages[$path = '/'.implode('/', $url->paths)]))
                    return $this->__construct(self::$pages[$path]);
                else
                    array_pop($url->paths);
            }
        } else if(isset(self::$pages['/']))
            $this->__construct(self::$pages['/']);
    }
    public function setClass() {
        $classes = func_get_args();
        $position = array_shift($classes);
        echo json_encode($classes);
        $this->template->classes[$position] = $classes;
    }
    public function addClass() {
        $classes = func_get_args();
        $position = array_shift($classes);
        if(!isset($this->template->classes[$position]))
            $this->template->classes[$position] = $classes;
        else
            foreach($classes as $class)
                if(!in_array ($class, $this->template->classes[$position]))
                    array_push($this->template->classes[$position], $class);
    }
    public function removeClass() {
        $classes = func_get_args();
        $position = array_shift($classes);
        if(isset($this->template->classes[$position]))
            foreach($classes as $class)
                if(($index = array_search($class, $this->template->classes[$position])) !== false)
                    array_splice($this->template->classes[$position], $index, 1);
    }
    public function getClasses($position) {
        if(isset($this->template->classes[$position]))
            return implode(' ', $this->template->classes[$position]);
        return '';
    }
}