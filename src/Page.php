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
     * @var array
     */
    private $modules = array();
    /**
     * page Classes
     * @var array
     */
    private $classes = array();
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
}