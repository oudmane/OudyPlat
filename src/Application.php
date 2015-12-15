<?php

namespace OudyPlat;

/**
 * Main application class for invoking applications
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class Application extends Object {

    /**
     * 
     * @var Page
     */
    public $page = null;
    /**
     *
     * @var Session
     */
    public $session = null;
    public $exec = 0;

    public function __construct() {
        $this->exec = microtime();
        self::check();
        if(Session::$configuration)
            if(isset(Session::$configuration->class))
                $this->session - new Session::$configuration->class();
            else
                $this->session = new Session();
        $this->exec = microtime() - $this->exec;
    }
    /**
     * load a page
     * @param Page $page
     */
    public function load($page) {
        $data =& $page->data;
        $return = false;
        if(file_exists($controller = COMPONENTS_PATH.'system'.DIRECTORY_SEPARATOR.'controller.php'))
            $return = include($controller);
        else if(defined('PARENT_COMPONENTS_PATH') && file_exists($controller = PARENT_COMPONENTS_PATH.'system'.DIRECTORY_SEPARATOR.'controller.php'))
                $return = include($controller);
        
        if(!$return)
            return false;
        
        if($page) {
            $notyet = false;
            if(file_exists($controller = COMPONENTS_PATH.$page->component.DIRECTORY_SEPARATOR.'controller.php'))
                $return = include($controller);
            else if(defined('PARENT_COMPONENTS_PATH') && file_exists($controller = PARENT_COMPONENTS_PATH.$page->component.DIRECTORY_SEPARATOR.'controller.php'))
                $return = include($controller);
            else
                $notyet = true;
            if(file_exists($controller = COMPONENTS_PATH.$page->component.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$page->task.'.php'))
                $return = include($controller);
            else if(defined('PARENT_COMPONENTS_PATH') && file_exists($controller = PARENT_COMPONENTS_PATH.$page->component.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$page->task.'.php'))
                $return = include($controller);
            else if($notyet)
                return $this->error(2500);
            
            if(!$return)
                return false;
        }
        $this->page = $page;
        return false;
    }
    public function render($position = null, $module = null) {
        if(is_null($this->page))
            $this->error(2503);
        
        $data =& $this->page->data;
        switch($position) {
            case 'api':
                $notyet = false;
                if(file_exists($api = COMPONENTS_PATH.$this->page->component.DIRECTORY_SEPARATOR.'api.php'))
                    include $api;
                else if(defined('PARENT_COMPONENTS_PATH') && file_exists($api = PARENT_COMPONENTS_PATH.$this->page->component.DIRECTORY_SEPARATOR.'api.php'))
                    include $api;
                else
                    $notyet = true;
                if(file_exists($api = COMPONENTS_PATH.$this->page->component.DIRECTORY_SEPARATOR.'apis'.DIRECTORY_SEPARATOR.$this->page->task.'.php'))
                    include $api;
                else if(defined('PARENT_COMPONENTS_PATH') && file_exists($api = PARENT_COMPONENTS_PATH.$this->page->component.DIRECTORY_SEPARATOR.'apis'.DIRECTORY_SEPARATOR.$this->page->task.'.php'))
                    include $api;
                else if($notyet) {
                    $this->error(2502);
                    return $this->render($position);
                }
                $this->setHeader('json');
                echo json_encode($data, JSON_PRETTY_PRINT);
                break;
            case 'html':
                
                break;
            default:
                $this->setHeader('json');
                echo json_encode($this, JSON_PRETTY_PRINT);
                break;
        }
        $this->setHeader('oudyplat');
    }
    public function loadByComponent($component, $task = null, $data = null) {
        $page = new Page(array(
            'component'=> $component,
            'task'=> $task,
            'data'=> $data
        ));
        return $this->load($page);
    }
    public function error($code) {
        return $this->loadByComponent('error', $code);
    }
    public function loadByURI($uri = null) {
        if(is_null($uri))
            $uri = $_SERVER['REQUEST_URI'];
        
        $page = new Page();
        if($page->load($uri))
            return $this->load($page);
        else
            return $this->error(404);
    }
    public function loadByComponentURI($uri = null) {
        if(is_null($uri))
            $uri = $_SERVER['REQUEST_URI'];
        
        $page = new Page();
        $uri = new URL($uri);
        if($uri->paths) {
            $page->component = $uri->paths[0];
            if(count($uri->paths) > 1)
                $page->task = $uri->paths[1];
            return $this->load($page);
        } else
            return $this->error(404);
    }
    public function setHeader($header) {
        $http = array (
            100 => 'HTTP/1.1 100 Continue',
            101 => 'HTTP/1.1 101 Switching Protocols',
            200 => 'HTTP/1.1 200 OK',
            201 => 'HTTP/1.1 201 Created',
            202 => 'HTTP/1.1 202 Accepted',
            203 => 'HTTP/1.1 203 Non-Authoritative Information',
            204 => 'HTTP/1.1 204 No Content',
            205 => 'HTTP/1.1 205 Reset Content',
            206 => 'HTTP/1.1 206 Partial Content',
            300 => 'HTTP/1.1 300 Multiple Choices',
            301 => 'HTTP/1.1 301 Moved Permanently',
            302 => 'HTTP/1.1 302 Found',
            303 => 'HTTP/1.1 303 See Other',
            304 => 'HTTP/1.1 304 Not Modified',
            305 => 'HTTP/1.1 305 Use Proxy',
            307 => 'HTTP/1.1 307 Temporary Redirect',
            400 => 'HTTP/1.1 400 Bad Request',
            401 => 'HTTP/1.1 401 Unauthorized',
            402 => 'HTTP/1.1 402 Payment Required',
            403 => 'HTTP/1.1 403 Forbidden',
            404 => 'HTTP/1.1 404 Not Found',
            405 => 'HTTP/1.1 405 Method Not Allowed',
            406 => 'HTTP/1.1 406 Not Acceptable',
            407 => 'HTTP/1.1 407 Proxy Authentication Required',
            408 => 'HTTP/1.1 408 Request Time-out',
            409 => 'HTTP/1.1 409 Conflict',
            410 => 'HTTP/1.1 410 Gone',
            411 => 'HTTP/1.1 411 Length Required',
            412 => 'HTTP/1.1 412 Precondition Failed',
            413 => 'HTTP/1.1 413 Request Entity Too Large',
            414 => 'HTTP/1.1 414 Request-URI Too Large',
            415 => 'HTTP/1.1 415 Unsupported Media Type',
            416 => 'HTTP/1.1 416 Requested range not satisfiable',
            417 => 'HTTP/1.1 417 Expectation Failed',
            500 => 'HTTP/1.1 500 Internal Server Error',
            501 => 'HTTP/1.1 501 Not Implemented',
            502 => 'HTTP/1.1 502 Bad Gateway',
            503 => 'HTTP/1.1 503 Service Unavailable',
            504 => 'HTTP/1.1 504 Gateway Time-out',
            'html' => 'Content-type: text/html; charset=utf-8',
            'json' => 'Content-type: application/json; charset=utf-8',
            'xml' => 'Content-type: application/xml; charset=utf-8',
            'oudyplat' => 'X-Powered-By: OudyPlat 2.0'
        );
        header($http[$header]);
    }

    public static function check() {
        if(!defined('ROOT_PATH'))
            die('ROOT_PATH undefined');
        if(!defined('COMPONENTS_PATH'))
            define('COMPONENTS_PATH', ROOT_PATH.'components'.DIRECTORY_SEPARATOR);
    }
}