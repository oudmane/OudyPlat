<?php

namespace OudyPlat;

/**
 * 
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class URL extends Object {
    
    /**
	 * URI
	 * @var string
	 */
	public $path = '';
	/**
	 * URI as array
	 * @var array
	 */
	public $paths = array();
	/**
	 * query string
	 * @var string
	 */
	public $query = '';
	/**
	 * query string as array
	 * @var array
	 */
	public $queries = array();

    /**
     * Initialize URL Object with an URL
     * @param string $url
     */
    public function __construct($url) {
        
        // assing the parse to this object if it's a valid URL
        if($url = parse_url($url))
                parent::__construct($url);
        
        // parse query vars
        if(isset($this->query))
            parse_str($this->query, $this->queries);
        
        // parse paths
        if($this->path)
            $this->paths = array_slice(explode('/', $this->path), 1);
    }
	/**
	 * search if /key/ exist in URI, and return it's next value /key/value
	 * @param string $key
	 * @param boolean $returnNextPath
	 * @return boolean
	 */
	public function inPath($key, $returnNextPath = false) {
        
		// return false if there's no paths
		if(!isset($this->paths) || empty($this->paths))
            return false;
        
		// if the key exist in path
		if(($i = array_search($key, $this->paths)) > -1) {
			// if return next path
			if($returnNextPath) {
				// if has nex path
				if(isset($this->paths[$i+1]))
					// return next path
					return $this->paths[$i+1];
			} else
                return true;
		}
        return false;
	}
	public function inRequest($key) {
		if($this->inPath($key))
			return $this->inPath($key, true);
        
		if(array_key_exists($key, $this->queries))
			return $this->queries[$key];
        
		return false;
	}
	public function getURL($secure = null) {
		$url = '';
        
		if($secure === true)
            $url .= 'https:';
        
		else if($secure === false)
            $url .= 'http:';
        
		return $url .= rtrim(ROOT_URL, '/').$this->path;
	}
}