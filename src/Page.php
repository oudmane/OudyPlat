<?php

namespace OudyPlat;

/**
 * Page class to load and handle page properties
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class Page extends Object {
    /**
     * page id
     * @var int
     */
    public $id = 0;
    /**
     * page component name
     * @var string
     */
    public $component = '';
    /**
     * page task name
     * @var string
     */
    public $task;
    /**
     * page data
     * @var Object
     */
    public $data;
    /**
     * page title
     * @var string
     */
    public $title = '';
    /**
     * page description
     * @var string
     */
    public $description = '';
    /**
     * page keywords
     * @var array
     */
    public $keywords = array();
    /**
     * page canonical URL
     * @var string
     */
    public $canonical = '';
    /**
     * page shorlink URL
     * @var string
     */
    public $shortlink = '';
    /**
     * page metadata properties
     * @var array
     */
    public $metadata = array();
    /**
     * page microdata properties
     * @var array
     */
    public $microdata = array();
    /**
     * page opengraph properties
     * @var array
     */
    public $opengraph = array();
    /**
     * page Modules
     * @var Object
     */
    public $modules = null;
    /**
     * page Classes
     * @var Object
     */
    public $classes = null;
    public $uri = '';
    /**
     * to store defined pages
     * @var object
     */
    public static $pages = null;
    
    /**
     * Initialize Page
     * @param type string|URL|Page|
     */
    public function __construct($page = null) {
        $this->data = new Object();
        $this->classes = new Object();
        $this->modules = new Object();
        // switch on $page type
        switch (gettype($page)) {
            
            // if it's a string
            case 'string':
            // if it's an URL
            case 'URL':
                $this->load($page);
                break;
            default:
                parent::__construct($page);
                break;
        }
    }
    public function load($page) {
        if(gettype($page) == 'string')
            // convert it to URL
            $page = new URL($page);
        // check if URL path exist in defined pages
        if(isset(self::$pages[$page->path]))
            // load it
            $this->__construct(self::$pages[$page->path]);

        // check if the first element in URL path exist in defined pages
        else if(isset(self::$pages['/'.$page->paths[0]]))
            // load it
            $this->__construct(self::$pages['/'.$page->paths[0]]);
        
        else
            return false;
        
        return true;
    }
    public function setClass() {
        $args = func_get_args();
        if(!isset($this->classes->$args[0]))
            $this->classes->$args[0] = new Object();
        if(!isset($this->classes->$args[0]->set))
            $this->classes->$args[0]->set = array();
        for($i=1; $i<count($args); $i++)
            array_push($this->classes->$args[0]->set, $args[$i]);
    }
    public function addClass() {
        $args = func_get_args();
        if(!isset($this->classes->$args[0]))
            $this->classes->$args[0] = new Object();
        if(!isset($this->classes->$args[0]->add))
            $this->classes->$args[0]->add = array();
        for($i=1; $i<count($args); $i++)
            array_push($this->classes->$args[0]->add, $args[$i]);
    }
    public function removeClass() {
        $args = func_get_args();
        if(!isset($this->classes->$args[0]))
            $this->classes->$args[0] = new Object();
        if(!isset($this->classes->$args[0]->remove))
            $this->classes->$args[0]->remove = array();
        for($i=1; $i<count($args); $i++)
            array_push($this->classes->$args[0]->remove, $args[$i]);
    }
    public function addModule($position, $module) {
        if(!isset($this->modules->$position))
            $this->modules->$position = array();
        $this->modules->{$position}[] = $module;
    }
    public function removeModule($position, $module) {
        if(isset($this->modules->$position)) {
            if(($i = array_search($module, $this->modules->{$position})) !== false)
                unset($this->modules->{$position}[$i]);
            if(!$this->modules->{$position})
                unset($this->modules->{$position});
        }
    }
    public function preTitle($title) {
        $this->title = $title.' - '.$this->title;
    }
    public function setDescription($description) {
        $this->description = $description;
        $this->setOG('description', $description);
    }
    /**
     * Set Metadata
     * @param string $property
     * @param string $content
     */
    public function setMeta($property, $content, $update = false) {
        $meta = new Object(
            array(
                'property'=>$property,
                'content'=>$content
            )
        );
        if($update) {
            $found = false;
            for ($i=0; $i < count($this->metadata); $i++)
                if($this->metadata[$i]->property == $meta->property) {
                    $this->metadata[$i] = $meta;
                    $found = true;
                }
            if(!$found)
                $this->metadata[] = $meta;
        } else
            $this->metadata[] = $meta; 
    }
    /**
     * Set OpenGraph metadata
     * @param string $property
     * @param string $content
     */
    public function setOG($property, $content, $update = false) {
        $this->setMeta('og:'.$property, $content, $update);
    }
}