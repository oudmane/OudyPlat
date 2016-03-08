<?php

namespace OudyPlat;

class Entity extends Object {
    const columns = '';
    const table = '';
    const key = '';
    const types = '';
    protected $changes = array();
    protected $errors = array();
    /**
     * 
     * @param array|object|string $data
     * @param array|object|string $allowedProperties
     * @param bool $forceAll
     */
    public function __construct($data = null, $allowedProperties = null, $forceAll = false) {
        if($data)
            switch(gettype($data)) {
                case 'array':
                case 'object':
                    parent::__construct($data, $allowedProperties, $forceAll);
                    break;
                default:
                    return $this->loadByKey(func_get_args());
                    break;
            }
        $class = get_class($this);
        if(defined($class.'::types') && $class::types) {
            $types = explode(';', $class::types);
            foreach($types as $type) {
                list($key, $value) = explode(':', $type);
                $this->$key = new $value($this->$key);
            }
        }
    }
    /**
     * 
     * @param string $conditions
     * @param array $values
     * @return boolean
     */
    public function loadBySQLConditions($conditions, $values = array()) {
        $class = get_class($this);
        $fetch = MySQL::select(array(
            'columns'=> $class::columns,
            'table'=> $class::table,
            'conditions'=> $conditions
        ), $values)->fetch();
        if($fetch)
            $this->__construct($fetch);
        return $fetch ? true : false;
    }
    /**
     * 
     * @param string|array $key, primary keys
     */
    public function loadByKey() {
        $args = func_get_args();
        if(empty($args))
            return false;
        if(gettype($args[0]) == 'array')
            $args = $args[0];
        $class = get_class($this);
        $values = array();
        $i = 0;
        foreach(explode(',', $class::key) as $key)
            $values[':'.$key] = $args[$i++];
        return $this->loadBySQLConditions(
            implode(
                ' AND ',
                array_map(
                    function($key) {
                        return $key.' = :'.$key;
                    },
                    explode(',', $class::key)
                )
            ), $values);
    }
    /**
     * 
     * @param array $conditions
     * @param boolean $all
     * @return boolean
     */
    public function loadByConditions($conditions, $all = true) {
        if(empty($conditions))
            return false;
        return $this->loadBySQLConditions(
            implode(
                $all ? ' AND ' : ' OR ',
                array_map(function($key) {
                    return $key.' = :'.$key;
                }, array_keys($conditions))
            ), SQL::buildValues($conditions, array_keys($conditions)));
    }
    /**
     * 
     * @param array|object $data
     * @param array|object|string $allowedProperties
     * @param bool $forceAll
     */
    public function bind($data = null, $allowedProperties = null, $forceAll = false) {
        $data = new Object($data, $allowedProperties, $forceAll);
        foreach($data as $key=>$value) {
            $this->$key = $value;
            $this->change($key);
        }
        $this->__construct();
        return !$this->error();
    }
    public function save($forceInsert = false, $ignore = false) {
        
    }
    /**
     * 
     * @param string $key
     * @param string $error
     */
    public function setError($key, $error) {
        $this->errors[$key] = $error;
    }
    /**
     * 
     * @param string $key
     * @return string
     */
    public function getError($key) {
        return isset($this->errors[$key]) ? $this->errors[$key] : false;
    }
    /**
     * 
     * @return boolean
     */
    public function hasErrors() {
        return count($this->errors) ? true : false;
    }
    /**
     * 
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
    /**
     * 
     * @param string $key
     * @param string $error
     */
    public function unsetError($key, $error = null) {
        if(isset($this->errors[$key]))
            if(is_null($error))
                unset($this->errors[$key]);
            else
                if($this->errors[$key] == $error)
                   unset($this->errors[$key]); 
        
    }
    /**
     * 
     * @param string $key,
     */
    public function unsetErrors() {
        $keys = func_get_args();
        if(empty($keys))
            $this->errors = array();
        else
            foreach($keys as $key)
                $this->unsetError($key);
    }
    /**
     * 
     * @param string $key
     */
    public function setChange($key) {
        if(!in_array($key, $this->changes))
            array_push($this->changes, $key);
    }
    public function unsetChange($key) {
        if(($index = array_search($key, $this->changes)) !== false)
            array_splice($this->changes, $index, 1);
    }
    /**
     * 
     * @param string $key
     * @return boolean
     */
    public function isChanged($key) {
        return in_array($key, $this->changes);
    }
    /**
     * 
     * @return boolean
     */
    public function hasChanges() {
        return count($this->changes) ? true : false;
    }
    /**
     * 
     * @return array
     */
    public function getChanges() {
        return $this->changes;
    }
}