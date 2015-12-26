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
    /**
     *
     * @var Language
     */
    public $language = null;
    public $exec = 0;

    public function __construct() {
        $this->exec = microtime();
        self::check();
        if(Session::$configuration)
            if(isset(Session::$configuration->class))
                $this->session = new Session::$configuration->class();
            else
                $this->session = new Session();
        if(Language::$language)
            $this->language = new Language(Language::$language);
        $this->exec = microtime() - $this->exec;
    }
    /**
     * load a page
     * @param Page $page
     */
    public function load($page) {
        $data =& $page->data;
        $language =& $this->language;
        $return = null;
        if(file_exists($controller = COMPONENTS_PATH.'system'.DIRECTORY_SEPARATOR.'controller.php'))
            $return = include($controller);
        else if(defined('PARENT_COMPONENTS_PATH') && file_exists($controller = PARENT_COMPONENTS_PATH.'system'.DIRECTORY_SEPARATOR.'controller.php'))
                $return = include($controller);
        
        if($return !== null)
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
        $language =& $this->language;
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
                $page =& $this->page;
                $template = new Template();
                if(isset($page->classes))
                	$template->mergeClasses($page->classes);
                $this->setHeader('html');
				if(file_exists($html = ROOT_PATH.'templates/'.$template->name.'/layouts/html.php'))
                	include $html;
                break;
            case 'module':
                $page =& $this->page;
                $template = new Template();
                if(isset($page->classes))
                    $template->mergeClasses($page->classes);
                if(isset($page->modules->$module))
                    foreach($page->modules->$module as $m)
                        if(file_exists($module = MODULES_PATH.$m.'.php'))
                            include $module;
                        else if(defined('PARENT_COMPONENTS_PATH') && file_exists($module = PARENT_COMPONENTS_PATH.$m.'.php'))
                            include $module;
                break;
            case 'view':
                $notyet = false;
                if(file_exists($view = COMPONENTS_PATH.$this->page->component.DIRECTORY_SEPARATOR.'view.php'))
                    include $view;
                else if(defined('PARENT_COMPONENTS_PATH') && file_exists($view = PARENT_COMPONENTS_PATH.$this->page->component.DIRECTORY_SEPARATOR.'view.php'))
                    include $view;
                else
                    $notyet = true;
                if(file_exists($view = COMPONENTS_PATH.$this->page->component.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$this->page->task.'.php'))
                    include $view;
                else if(defined('PARENT_COMPONENTS_PATH') && file_exists($view = PARENT_COMPONENTS_PATH.$this->page->component.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$this->page->task.'.php'))
                    include $view;
                break;
            default:
                $this->setHeader('json');
                echo json_encode($this, JSON_PRETTY_PRINT);
                break;
        }
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
    public function sanitaze() {
        ob_start(function($input) {
            if(trim($input) === "") return $input;
            // Remove extra white-space(s) between HTML attribute(s)
            $input = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s', function($matches) {
                return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
            }, str_replace("\r", "", $input));
            // Minify inline CSS declaration(s)
            if(strpos($input, ' style=') !== false) {
                $input = preg_replace_callback('#<([^<]+?)\s+style=([\'"])(.*?)\2(?=[\/\s>])#s', function($matches) {
                    return '<' . $matches[1] . ' style=' . $matches[2] . minify_css($matches[3]) . $matches[2];
                }, $input);
            }
            return preg_replace(
                array(
                    // t = text
                    // o = tag open
                    // c = tag close
                    // Keep important white-space(s) after self-closing HTML tag(s)
                    '#<(img|input)(>| .*?>)#s',
                    // Remove a line break and two or more white-space(s) between tag(s)
                    '#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
                    '#(<!--.*?-->)|(?<!\>)\s+(<\/.*?>)|(<[^\/]*?>)\s+(?!\<)#s', // t+c || o+t
                    '#(<!--.*?-->)|(<[^\/]*?>)\s+(<[^\/]*?>)|(<\/.*?>)\s+(<\/.*?>)#s', // o+o || c+c
                    '#(<!--.*?-->)|(<\/.*?>)\s+(\s)(?!\<)|(?<!\>)\s+(\s)(<[^\/]*?\/?>)|(<[^\/]*?\/?>)\s+(\s)(?!\<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
                    '#(<!--.*?-->)|(<[^\/]*?>)\s+(<\/.*?>)#s', // empty tag
                    '#<(img|input)(>| .*?>)<\/\1>#s', // reset previous fix
                    '#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...
                    '#(?<=\>)(&nbsp;)(?=\<)#', // --ibid
                    // Remove HTML comment(s) except IE comment(s)
                    '#\s*<!--(?!\[if\s).*?-->\s*|(?<!\>)\n+(?=\<[^!])#s'
                ),
                array(
                    '<$1$2</$1>',
                    '$1$2$3',
                    '$1$2$3',
                    '$1$2$3$4$5',
                    '$1$2$3$4$5$6$7',
                    '$1$2$3',
                    '<$1$2',
                    '$1 ',
                    '$1',
                    ""
                ),
            $input);
        });
        function minify_css($input) {
            if(trim($input) === "") return $input;
            return preg_replace(
                array(
                    // Remove comment(s)
                    '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
                    // Remove unused white-space(s)
                    '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
                    // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                    '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
                    // Replace `:0 0 0 0` with `:0`
                    '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
                    // Replace `background-position:0` with `background-position:0 0`
                    '#(background-position):0(?=[;\}])#si',
                    // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
                    '#(?<=[\s:,\-])0+\.(\d+)#s',
                    // Minify string value
                    '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
                    '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
                    // Minify HEX color code
                    '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
                    // Replace `(border|outline):none` with `(border|outline):0`
                    '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
                    // Remove empty selector(s)
                    '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
                ),
                array(
                    '$1',
                    '$1$2$3$4$5$6$7',
                    '$1',
                    ':0',
                    '$1:0 0',
                    '.$1',
                    '$1$3',
                    '$1$2$4$5',
                    '$1$2$3',
                    '$1:0',
                    '$1$2'
                ),
            $input);
        }
        function minify_js($input) {
            if(trim($input) === "") return $input;
            return preg_replace(
                array(
                    // Remove comment(s)
                    '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
                    // Remove white-space(s) outside the string and regex
                    '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
                    // Remove the last semicolon
                    '#;+\}#',
                    // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
                    '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
                    // --ibid. From `foo['bar']` to `foo.bar`
                    '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
                ),
                array(
                    '$1',
                    '$1$2',
                    '}',
                    '$1$3',
                    '$1.$3'
                ),
            $input);
        }
    }
    public static function check() {
        if(!defined('ROOT_PATH'))
            die('ROOT_PATH undefined');
        if(!defined('COMPONENTS_PATH'))
            define('COMPONENTS_PATH', ROOT_PATH.'components'.DIRECTORY_SEPARATOR);
        if(!defined('TEMPLATES_PATH'))
            define('TEMPLATES_PATH', ROOT_PATH.'templates'.DIRECTORY_SEPARATOR);
        if(!defined('MODULES_PATH'))
            define('MODULES_PATH', ROOT_PATH.'modules'.DIRECTORY_SEPARATOR);
    }
}