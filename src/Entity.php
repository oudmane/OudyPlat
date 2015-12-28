<?php

namespace OudyPlat;

/**
 * 
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class Entity extends Object {
    const columns = '';
    const table = '';
    const key = '';
    const database = '';
    protected $changes = array();
    protected $errors = array();
    public function __construct($data = null, $allowedProperties = null, $forceAll = false) {
        if($data)
            switch(gettype($data)) {
                case 'array':
                case 'object':
                    parent::__construct($data, $allowedProperties, $forceAll);
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
    public function save($forceSave = false) {
        if(empty($this->changes))
            return false;
        $class = get_class($this);
        $key = $class::key;
        if($this->$key && !$forceSave) {
            $query = SQL::update(array(
                'columns'=>     $this->changes,
                'table'=>       $class::table,
                'condition'=>   $key.' = :'.$key
            ));
            $values = SQL::buildValues($this, $this->changes);
            $values[':'.$key] = $this->$key;
        } else {
            $query = SQL::insert(array(
                'columns'=>     $class::columns,
                'table'=>       $class::table
            ));
            $values = SQL::buildValues($this, $class::columns);
        }
        $statement = new MySQL($query, $values);
        if(!$this->$key)
            $this->$key = $statement->lastInsertId();
        return true;
    }
    public function load($key) {
        if(empty($key))
            return false;
        $class = get_class($this);
        return $this->loadBy(array(
            $class::key=> $key
        ));
    }
    public function loadBy($data, $all = true) {
        if(empty($data))
            return false;
        $class = get_class($this);
        $fetch = MySQL::select(
            array(
                'columns'=> $class::columns,
                'table'=> $class::table,
                'condition'=> implode(
					$all ? ' AND ' : ' OR ',
					array_map(
						function($key) {
							return $key.'=:'.$key;
						}, array_keys($data)
					)
				)
            ),
            SQL::buildValues($data, array_keys($data))
        )->fetch();
        if($fetch)
            $this->__construct($fetch);
        return $fetch ? true : false;
    }
    public function bind($data) {
        if($data) foreach($data as $key=>$value) {
			$this->$key = $value;
			$this->change($key);
		}
		$this->__construct();
        return !$this->error();
    }
	public function change($key) {
		if(in_array($key, $this->changes)) return false;
		array_push($this->changes, $key);
		return true;
	}
	public function changed($key = '') {
		return $key ? in_array($key, $this->changes) : $this->changes;
	}
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
    public static function get($conditions, $values = null) {
        $class = get_called_class();
        return MySQL::select(
            array(
                'columns'=> $class::columns,
                'table'=> $class::table,
                'condition'=> $conditions
            ),
            $values
        )->fetchAllClass($class);
    }
    public static function exist($key) {
        $class = get_called_class();
        return MySQL::select(
            array(
                'columns'=> $class::columns,
                'table'=> $class::table,
                'condition'=> $class::key.' = :'.$class::key
            ),
            array(
                ':'.$class::key => $key
            )
        )->fetch() ? true : false;
    }
}