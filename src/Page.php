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
    public $classes = array();
    public $modules = array();
    public $url = null;
    public $template = null;
    public static $pages = null;
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
}