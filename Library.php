<?php

defined('OUDY_EXEC') or die;

/**
 * Description of Library
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class Library extends Object {
	/**
	 * Store Changes here
	 * @var array
	 */
	protected $changes = array();
	/**
	 * Store Errors here
	 * @var array
	 */
	protected $errors = array();
	/**
	 * Initialate the object
	 * @param array|object|string|int $data
	 * @param array|object|string $allowedProperties
	 */
	public function __construct($data = null, $allowedProperties = null) {
		// stop if there's nothing to assing to this object
//		if(empty($data)) return;
		// decide what to do with $data based on it's type
		if($data) switch(gettype($data)) {
			case 'array':
			case 'object':
				parent::__construct($data, $allowedProperties);
			break;
			default:
				return $this->load($data);
			break;
		}
		$class = get_class($this);
		if(defined($class.'::types')) {
			$types = explode(';', $class::types);
			foreach($types as $type) {
				list($key, $value) = explode(':', $type);
				$this->$key = is_array($this->$key) ? $value::get($this->$key) : new $value($this->$key);
			}
		}
	}
	/**
	 * Load the object from Database using $key and return if it's loaded
	 * @param int|string $key
	 * @return boolean
	 */
	public function load($key) {
		// stop if there's no id given to load
		if(empty($key)) return false;
		// get class name
		$class = get_class($this);
		// statement to database
		$statement = new Database(
			// Query
			SQL::select(
				$class::columns,
				$class::table,
				$class::key.'=:key'
			),
			// Values
			array(':key'=>$key)
		);
		// if there's a return
		if($fetch = $statement->fetch()) {
			// assign the row this object
			$this->__construct($fetch);
			return true;
		}
		$this->__construct();
		return false;
	}
	/**
	 * Assign Values to this object
	 * @param array|object $data
	 * @param boolean $all
	 */
	public function bind($data, $all = false) {
		if($data) foreach($data as $key=>$value) {
			$this->$key = $value;
			$this->change($key);
		}
		$this->__construct();
	}
	/**
	 * Save the object in database and return if it's saved
	 * @return boolean
	 */
	public function save($widthUpdate = true) {
		// stop if nothing changed
		if(empty($this->changes)) return false;
		// get class name
		$class = get_class($this);
		// object key
		$key = $class::key;
		// prepare update query if already in database
		if($this->$key && $widthUpdate) {
			$query = SQL::update($this->changes, $class::table, $key.'=:'.$key);
			$values = SQL::buildIns($this, $this->changes);
		// prepare insert query
		} else {
			$query = SQL::insert($class::columns, $class::table);
			$values = SQL::buildIns($this, $class::columns);
		}
		// add object key to values
		$values[':'.$key] = $this->$key;
		// run statement
		$statement = new Database($query, $values);
		// get back key if it's a new object
		if(!$this->$key) $this->$key = $statement->lastid();
		return true;
	}
	/**
	 * Add a key to changed keys
	 * @param string $key
	 * @return boolean
	 */
	public function change($key) {
		if(in_array($key, $this->changes)) return false;
		array_push($this->changes, $key);
		return true;
	}
	/**
	 * Get if this object or a key is changed
	 * @param string $key
	 * @return boolean
	 */
	public function changed($key = '') {
		return $key ? in_array($key, $this->changes) : $this->changes;
	}
	/**
	 * Set or get error
	 * @param string $key
	 * @param string $error
	 * @return boolean|string
	 */
	public function error($key = '', $error = '') {
		if($key) {
			if($error) {
				$this->errors[$key] = $error;
			} else {
				return isset($this->errors[$key]) ? $this->errors[$key] : false;
			}
		} else {
			return count($this->errors) ? $this->errors : false;
		}
	}
	/**
	 * return the object name default or defined language
	 * @param string $language
	 * @return string
	 */
	public function name($language = '') {
		if(!$language) $language = Language::$language;
		$key = $language.'Name';
		return (isset($this->$key) && $this->$key) ? $this->$key : $this->name;
	}
	/**
	 * Load the object from Database using array of conditions and return if it's loaded
	 * @param array $data
	 * @param boolean $all
	 * @return boolean
	 */
	public function loadBy($data, $all = true) {
		// stop if there's no data given to load
		if(empty($data)) return false;
		// get class name
		$class = get_class($this);
		// statement to database
		$statement = new Database(
			// Query
			SQL::select(
				$class::columns,
				$class::table,
				implode(
					$all ? ' AND ' : ' OR ',
					array_map(
						function($key) {
							return $key.'=:'.$key;
						}, array_keys($data)
					)
				)
			),
			// Values
			SQL::buildIns(
				new object($data),
				array_keys($data)
			)
		);
		// if there's a return
		if($fetch = $statement->fetch()) {
			// assign the row this object
			$this->__construct($fetch);
			return true;
		}
		return false;
	}
	public function keywords() {
		$keywords = array();
		if(isset($this->keywords) && $this->keywords) foreach($this->keywords as $k) {
			if(gettype($k) == 'object') {
				array_push($keywords, $k->var);
			} else {
				array_push($keywords, $k);
			}
		}
		return array_unique($keywords);
	}
	/**
	 * get Objects by ids
	 * @param array $ids
	 * @return array|boolean
	 */
	public static function get($ids) {
		// stop if there's no ids defined
		if(empty($ids)) return false;
		// get class name
		$class = get_called_class();
		// set statement
		$statement = new Database(
			SQL::select(
				$class::columns,
				$class::table,
				$class::key.' IN ('.implode(',', $ids).')',
				'FIELD('.$class::key.','.implode(',', $ids).')'
			)
		);
		// return fetch
		return $statement->fetchAllClass($class);
	}
	/**
	 * get Objects by conditions
	 * @param array $data
	 * @param boolean $all
	 * @param string $order
	 * @param string $limit
	 * @return array|boolean
	 */
	public static function getBy($data, $all = true, $order = '', $limit = '') {
		// get class name
		$class = get_called_class();
		// set statement
		$statement = new Database(
			// query
			SQL::select(
				$class::columns,
				$class::table,
				implode($all ? ' AND ' : ' OR ', array_map(
					function($key) {
						return $key.'=:'.$key;
					}, array_keys($data)
				)),
				$order,
				$limit
			),
			// values
			SQL::buildIns(
				new Object($data),
				array_keys($data)
			)
		);
		// return fetch
		return $statement->fetchAllClass($class);
	}
	public function buildErrorStrings($language, $prefix) {
		foreach($this->errors as $key=>$value) {
			$this->errors[$key] = new Object(
				array(
					'code'=> $value,
					'string'=> $language->returnString(strtoupper($prefix.'_'.$key.'_ERROR_'.$value))
				)
			);
		}
	}
	public function __isset($key) {
		switch($key) {
			case 'errors':
				return true;
				break;
			default:
				return isset($this->$key);
				break;
		}
	}
//	public function __get($key) {
//		switch($key) {
//			case 'errors':
//				return $this->$key;
//				break;
//			default:
//				return 'Undefined '.$key;
//				break;
//		}
//	}
//	public function __set($key, $value) {
//		$class = get_class($this);
//		if(defined($class.'::types') && strpos($class::types, $key) !== false) {
//			$types = explode(';', $class::types);
//			foreach($types as $type) {
//				list($tKey, $tValue) = explode(':', $type);
//				if($key == $tKey) $this->$key = new $tValue($value);
//			}
//		} else {
//			echo $key;
//			$this->$key = $value;
//		}
//	}
}