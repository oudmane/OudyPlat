<?php

namespace OudyPlat;

/**
 * Main application class for invoking applications
 */
class Application extends Object {

    /**
     *
     * @var Page
     */
    public $page = null;

    public function __construct() {
        self::check();
    }
    public static function check() {
        if(!defined('ROOT_PATH'))
            die('ROOT_PATH undefined');
        if(!defined('COMPONENTS_PATH'))
            define('COMPONENTS_PATH', ROOT_PATH.'components'.DIRECTORY_SEPARATOR);
    }
}