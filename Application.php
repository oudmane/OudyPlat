<?php

defined('OUDY_EXEC') or die;

/**
 * Description of Application
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class Application extends Object {
	public $isSocket = false;
	/**
	 * Configuration
	 * @var Configuration
	 */
    public static $configuration = null;
	/**
	 * to store libraries directories
	 * @var array
	 */
	public static $libraries = array();
	/**
	 * Initialize Application
	 * @param Configuration $configuration
	 */
	public function __construct($configuration = null) {
		if($configuration) {
			if(isset($configuration->database))
				Database::$configuration = new Object($configuration->database);
			// initialize session if configured
			if(isset($configuration->session))
				$this->session = new Session($configuration->session);
			// initialize language if configured
			if(isset($configuration->language))
				$this->language = new Language($configuration->language);
			// initialize template if configured
			if(isset($configuration->template))
				$this->template = new Template($configuration->template);
			// save pages json
			if(isset($configuration->pages))
				Page::$pages = (array) json_decode(file_get_contents($configuration->pages));
			// save configuration
			Application::$configuration = ($this->configuration = $configuration);
		}
	}
	/**
	 * Load the component
	 * @param Page $page
	 */
	public function load($page) {
		// attach components vars to this Application
		$this->component =& $page->component;
		$this->task = $page->task;
		$this->page =& $page;
		$this->data = new Object($page->data);
		
		// short vars
		$configuration =& $this->configuration;
		$component =& $this->component;
		$task =& $this->task;
		$data =& $this->data;
		$page =& $this->page;
		$url =& $page->url;
		
		// attach session if is set
		if(isset($this->session)) {
			$session =& $this->session;
			if(isset($session->user)) {
				$user =& $session->user;
			}
		}
		
		// attach language if is set
		if(isset($this->language)) $language =& $this->language;
		
		// attach template if is set
		if(isset($this->template)) $template =& $this->template;
		
		// register component's libraries
		$this->registerLibraries($component);
		// register component's model
		$this->registerModel($component);

		// include System Controller
		if(file_exists(COMPONENTS_PATH.'system'.DS.'controller.php')) {
			include COMPONENTS_PATH.'system'.DS.'controller.php';
		} else if(defined('PARENT_COMPONENTS_PATH') && file_exists(PARENT_COMPONENTS_PATH.'system'.DS.'controller.php')) {
			include PARENT_COMPONENTS_PATH.'system'.DS.'controller.php';
		}
		// Include Component Controller
		if(file_exists(COMPONENTS_PATH.$component.DS.'controller.php')) {
			include COMPONENTS_PATH.$component.DS.'controller.php';
		} else if(defined('PARENT_COMPONENTS_PATH') && file_exists(PARENT_COMPONENTS_PATH.$component.DS.'controller.php')) {
			include PARENT_COMPONENTS_PATH.$component.DS.'controller.php';
		}
		// Include Task Controller
		if(file_exists(COMPONENTS_PATH.$component.DS.'controllers'.DS.$task.'.php')) {
			include COMPONENTS_PATH.$component.DS.'controllers'.DS.$task.'.php';
		} else if(defined('PARENT_COMPONENTS_PATH') && file_exists(PARENT_COMPONENTS_PATH.$component.DS.'controllers'.DS.$task.'.php')) {
			include PARENT_COMPONENTS_PATH.$component.DS.'controllers'.DS.$task.'.php';
		}
		$this->exec = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
		if(isset($this->session)) $this->session->save();
	}
	/**
	 * register a component's libraries
	 * @param string|array $component
	 */
	public function registerLibraries($component) {
		if(!defined('COMPONENTS_PATH')) die('COMPONENTS_PATH not defined');
		// check if it's not already registred
		if(!in_array($component, Application::$libraries)) {
			// check if component's libraries exist in COMPONENTS_PATH
			if(file_exists($libraries = COMPONENTS_PATH.$component.DS.'libraries'.DS)) {
				array_push(Application::$libraries, $libraries);
			// check if component's libraries exist in COMPONENTS_PATH
			} else if(defined('PARENT_COMPONENTS_PATH') && file_exists($libraries = PARENT_COMPONENTS_PATH.$component.DS.'libraries'.DS)) {
				array_push(Application::$libraries, $libraries);
			}
		}
	}
	/**
	 * include a component model
	 * @param string $component
	 */
	public function registerModel($component) {
		if(!defined('COMPONENTS_PATH')) die('COMPONENTS_PATH not defined');
		// check if component's model exist in COMPONENTS_PATH
		if(file_exists($model = COMPONENTS_PATH.$component.DS.'model.php')) {
			include_once $model;
		// check if component's model exist in PARENT_COMPONENTS_PATH
		} else if(defined('PARENT_COMPONENTS_PATH') && file_exists($model = PARENT_COMPONENTS_PATH.$component.DS.'model.php')) {
			include_once $model;
		}
	}
	/**
	 * load the Application using URI
	 * @param string $url
	 */
	public function loadByURL($url = '') {
		// if $url not set, use the current one
		if(empty($url)) $url = $_SERVER['REQUEST_URI'];
		// load page
		$page = new Page($url);
		// load thi Application
		$this->load($page);
	}
	/**
	 * Load the Application using component,task and data vars
	 * @param string $component
	 * @param string $task
	 * @param array|object $data
	 */
	public function loadByComponent($component = 'error', $task = '404', $data = array()) {
		// prepare the Page
		$page = new Page(
			array(
				'component'=>$component,
				'task'=>$task,
				'data'=>$data,
				'url'=> $this->page->url
			)
		);
		// load thi Application
		$this->load($page);
	}
	/**
	 * load Application using URI like /component/task
	 * @param string $url
	 */
	public function loadByComponentURL($url = '') {
		// if $url not set, use the current one
		if(empty($url)) $url = $_SERVER['REQUEST_URI'];
		// parse URL
		$url = new URL($url);
		// check if url has parts
		if($url->paths) {
			// initialize Page
			$page = new Page();
			// set component
			$page->component = $url->paths[0];
			// check task and set
			if(count($url->paths)>=2) $page->task = $url->paths[1];
			// check if data defined
			if(isset($url->queries)) $page->data = new Object($url->queries);
			// append the url
			$page->url = $url;
			// load this Application
			$this->load($page);
		}
	}
	/**
	 * load error component
	 * @param string|int $code
	 * @param array|object $data
	 */
	public function error($code = '404', $data = array()) {
		$this->loadByComponent('error', $code, $data);
	}
	/**
	 * set HTTP Header via a code
	 * @param string|int $header
	 */
	public function setHeader($header) {
		if($this->isSocket) return;
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
			'xml' => 'Content-type: application/xml; charset=utf-8'
		);
		header($http[$header]);
	}
	/**
	 * redirect to a URI
	 * @param string $link
	 * @param string|int $code
	 * @param string $return
	 */
	public function redirect($link, $code = 302, $return = '') {
		if($return && isset($this->session)) $this->session->set('return', $return);
		if($this->isSocket) {
			return $this->loadByURL($link);
		}
		$this->setHeader($code);
		if(!isset($this->base)) $this->base = '';
		if(isset($this->session)) $this->session->save();
		header('Location: '.$this->base.$link);
		die();
	}
	/**
	 * render the Application
	 * @param string $position
	 * @param string $module
	 */
	public function render($position = '', $module = '') {
		foreach(array('page','template','data','configuration','session','language') as $k) {
			$$k =& $this->$k;
		}
		$component =& $this->component;
		$task =& $this->task;
		$user =& $session->user;
		$url =& $page->url;
		
		// set default template and layout if arnot defined in page
		if(empty($page->template)) $page->template = $template->name;
		if(empty($page->layout)) $page->layout = $template->layout;
		switch($position) {
			case 'html':
				ob_start();
				$this->setHeader('html');
				if(!defined('TEMPLATES_PATH')) die('TEMPLATES_PATH not defined');
				$this->setHeader('html');
				include TEMPLATES_PATH.$page->template.DS.$page->layout.'.php';
				$html = preg_replace('/^\s+|\n|\r|\t|\s+$/m', '', ob_get_clean());
				return str_replace(' "', '"', $html);
				ob_clean();
			break;
			case 'view':
				ob_start();
				if(!defined('COMPONENTS_PATH')) die('COMPONENTS_PATH not defined');
				if(file_exists(COMPONENTS_PATH.$component.DS.'views'.DS)) {
					include COMPONENTS_PATH.$component.DS.'views'.DS.$task.'.php';
				} else if(file_exists(COMPONENTS_PATH.$component.DS.'view.php')) {
					include COMPONENTS_PATH.$component.DS.'view.php';
				} else if(defined('PARENT_COMPONENTS_PATH')) {
					if(file_exists(PARENT_COMPONENTS_PATH.$component.DS.'views'.DS)) {
						include PARENT_COMPONENTS_PATH.$component.DS.'views'.DS.$task.'.php';
					} else if(file_exists(PARENT_COMPONENTS_PATH.$component.DS.'view.php')) {
						include PARENT_COMPONENTS_PATH.$component.DS.'view.php';
					}
				}
				$html = preg_replace('/^\s+|\n|\r|\t|\s+$/m', '', ob_get_clean());
				return str_replace(' "', '"', $html);
			break;
			case 'head':
				ob_start();
				if(!defined('COMPONENTS_PATH')) die('COMPONENTS_PATH not defined');
				if(file_exists(COMPONENTS_PATH.$page->component.DS.'head.php')) {
					include COMPONENTS_PATH.$page->component.DS.'head.php';
				} else if(defined('PARENT_COMPONENTS_PATH') && file_exists(PARENT_COMPONENTS_PATH.$page->component.DS.'head.php')) {
					include PARENT_COMPONENTS_PATH.$page->component.DS.'head.php';
				}
				$html = preg_replace('/^\s+|\n|\r|\t|\s+$/m', '', ob_get_clean());
				return str_replace(' "', '"', $html);
			break;
			case 'json':
				if(!defined('COMPONENTS_PATH')) die('COMPONENTS_PATH not defined');
				$this->setHeader('json');
				$return = array();
				if(file_exists(COMPONENTS_PATH.$page->component.DS.'api.php')) {
					include COMPONENTS_PATH.$page->component.DS.'api.php';
				} else if(defined('PARENT_COMPONENTS_PATH') && file_exists(PARENT_COMPONENTS_PATH.$page->component.DS.'api.php')) {
					include PARENT_COMPONENTS_PATH.$page->component.DS.'api.php';
				}
				echo json_encode($return, false);
			break;
			case 'module':
				ob_start();
				if(!defined('MODULES_PATH')) die('MODULES_PATH not defined');
				if($module == 'view') {
					echo $this->render('view');
				} else if(isset($page->modules[$module])) foreach($page->modules[$module] as $m) {
					if(file_exists(MODULES_PATH.$m.'.php')) {
						include MODULES_PATH.$m.'.php';
					} else if(defined('PARENT_MODULES_PATH') && file_exists(PARENT_MODULES_PATH.$m.'.php')) {
						include PARENT_MODULES_PATH.$m.'.php';
					}
				}
				$html = preg_replace('/^\s+|\n|\r|\t|\s+$/m', '', ob_get_clean());
				return str_replace(' "', '"', $html);
			break;
			case 'main':
				$this->setHeader('json');
				$return = new Object();
				$return->html = array();
				$return->class = $page->classes;
				if($page->modules) foreach($page->modules as $position=>$module) {
					$return->html[$position] = $this->render('module', $position);
				}
				$return->url = $page->url->path;
				$return->title = $data->title;
				$return->component = $component;
				$return->task = $task;
				return json_encode($return, false);
			break;
			default:
				die($position.' render not exist');
			break;
		}
	}
	/**
	 * set page title in Data
	 * @param string $title
	 */
	public function setTitle($title) {
		$title = $this->language->r($title);
		$this->data->title = $title;
		$this->setOG('title', $title, true);
	}
	/**
	 * add a sting before Title $title - $oldtitle
	 * @param string $title
	 */
	public function preTitle($title) {
		$title = $this->language->r($title);
		$this->setTitle($title.' - '.$this->data->title);
		$this->setOG('title', $title, true);
	}
	/**
	 * set page Description in Data
	 * @param string $description
	 */
	public function setDescription($description) {
		$description = $this->language->r($description);
		$this->data->description = $description;
		$this->setOG('description', $description, true);
	}
	/**
	 * Set OpenGraph metadata
	 * @param string $property
	 * @param string $content
	 */
	public function setOG($property, $content, $update = false) {
		$this->setMeta('og:'.$property, $content, $update);
	}
	/**
	 * Set Metadata
	 * @param string $property
	 * @param string $content
	 */
	public function setMeta($property, $content, $update = false) {
		if($property && !isset($this->data->meta)) $this->data->meta = array();
		$meta = new Object(
			array(
				'property'=>$property,
				'content'=>$content
			)
		);
		if($update) {
			$found = false;
			for ($i=0; $i < count($this->data->meta); $i++)
				if($this->data->meta[$i]->property == $meta->property) {
					$this->data->meta[$i] = $meta;
					$found = true;
				}
			if(!$found) $this->data->meta[] = $meta;
		} else $this->data->meta[] = $meta; 
	}
	/**
	 * add page Keywords to Data
	 * @param type $keywords
	 */
	public function addKeywords($keywords) {
		// define an array in $data->keywords if it's not defined
		if($keywords && !isset($this->data->keywords))
			$this->data->keywords = array();
		// reverse the order of $keywords
		// to prepend them to $data->keywords
		foreach(array_reverse($keywords) as $key)
			array_unshift($this->data->keywords, $key);
	}
}