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
}