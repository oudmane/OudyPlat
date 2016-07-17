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
            'columns'=> $class::columns(),
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
        if(gettype($args[0]) == 'array')
            $args = $args[0];
        $args = array_filter($args);
        if(empty($args))
            return false;
        $class = get_class($this);
        $values = array();
        $i = 0;
        foreach(explode(',', $class::key()) as $key)
            $values[':'.$key] = $args[$i++];
        return $this->loadBySQLConditions(
            implode(
                ' AND ',
                array_map(
                    function($key) {
                        return $key.' = :'.$key;
                    },
                    explode(',', $class::key())
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
            $this->setChange($key);
        }
        $this->__construct();
        return !$this->hasErrors();
    }
    public function save($forceInsert = false, $ignore = false) {
        if(!$this->hasChanges())
            return false;
        $class = get_class($this);
        $key = $class::key();
        $query = '';
        $values = array();
        if(count($keys = explode(',', $key)) > 1) {
            $query = SQL::insert(array(
                'columns'=> $class::columns(),
                'table'=> $class::table,
                'key'=> $keys,
                'changes'=> $this->changes,
                'update'=> true
            ));
            $values = SQL::buildValues($this, self::columns());
        } else if($this->$key && !$forceInsert) {
            $query = SQL::update(array(
                'columns'=> $this->changes,
                'table'=> $class::table,
                'conditions'=> $key.' = :'.$key
            ));
            $values = SQL::buildValues($this, $this->changes);
            $values[':'.$key] = $this->$key;
        } else {
            $query = SQL::insert(array(
                'columns'=> $class::columns(),
                'table'=> $class::table,
                'ignore'=> $ignore
            ));
            $values = SQL::buildValues($this, $class::columns());
        }
        $statement = new MySQL($query, $values);
        if(count($keys) == 1 && !$this->$key)
            $this->$key = $statement->lastInsertId();
        return true;
    }
    public function remove() {
        $class = get_class($this);
        $key = $class::key();
        $conditions = '';
        $values = array();
        if(count($keys = explode(',', $key)) > 1) {
            $conditions = implode(
                ' AND ',
                array_map(
                    function($key) {
                        return $key.' = :'.$key;
                    },
                    $keys
                )
            );
            $values = SQL::buildValues($this, $keys);
        } else {
            $conditions = $key.' = :'.$key;
            $values = array(':'.$key=> $this->$key);
        }
        MySQL::delete(array(
            'table'=> $class::table,
            'conditions'=> $conditions
        ), $values);
        return true;
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
    /**
     * 
     * @param string|array $key, primary keys
     * @return \OudyPlat\Object
     */
    public static function getByKey() {
        $class = get_called_class();
        $object = new $class();
        if($object->loadByKey(func_get_args()))
            return $object;
        else
            return null;
    }
    /**
     * 
     * @param string $conditions
     * @param array $values
     * @return \OudyPlat\Object
     */
    public static function getBySQLConditions($conditions, $values = array()) {
        $class = get_called_class();
        $object = new $class();
        if($object->loadBySQLConditions($conditions, $values))
            return $object;
        else
            return null;
    }
    /**
     * 
     * @param array $conditions
     * @param boolean $all
     * @return \OudyPlat\Object
     */
    public static function getByConditions($conditions, $all = true) {
        $class = get_called_class();
        $object = new $class();
        if($object->loadByConditions($conditions, $all))
            return $object;
        else
            return null;
    }
    /**
     * 
     * @param string $conditions
     * @param array $values
     * @return array
     */
    public static function getAllBySQLConditions($conditions, $values = array()) {
        $class = get_called_class();
        return MySQL::select(array(
            'columns'=> $class::columns(),
            'table'=> $class::table,
            'conditions'=> $conditions
        ), $values)->fetchAllClass($class);
    }
    /**
     * 
     * @param array $conditions
     * @param boolean $all
     * @return array
     */
    public static function getAllByConditions($conditions, $all = true) {
        if(empty($conditions))
            return false;
        $class = get_called_class();
        return $class::getAllBySQLConditions(
            implode(
                $all ? ' AND ' : ' OR ',
                array_map(function($key) {
                    return $key.' = :'.$key;
                }, array_keys($conditions))
            ), SQL::buildValues($conditions, array_keys($conditions)));
    }
    /**
     * 
     * @param string $conditions
     * @param array $values
     * @return array
     */
    public static function existWithSQLConditions($conditions, $values = array()) {
        $class = get_called_class();
        return MySQL::select(array(
            'columns'=> $class::key(),
            'table'=> $class::table,
            'conditions'=> $conditions
        ), $values)->fetchAllColumn();
    }
    /**
     * 
     * @param array $conditions
     * @param boolean $all
     * @return array
     */
    public static function existWithConditions($conditions, $all = true) {
        if(empty($conditions))
            return false;
        $class = get_called_class();
        return $class::existWithSQLConditions(
            implode(
                $all ? ' AND ' : ' OR ',
                array_map(function($key) {
                    return $key.' = :'.$key;
                }, array_keys($conditions))
            ), SQL::buildValues($conditions, array_keys($conditions)));
    }
    /**
     * 
     * @param string $conditions
     * @param array $values
     * @return array
     */
    public static function removeBySQLConditions($conditions, $values = array()) {
        $class = get_called_class();
        return MySQL::delete(array(
            'table'=> $class::table,
            'conditions'=> $conditions
        ), $values);
    }
    /**
     * 
     * @param array $conditions
     * @param boolean $all
     * @return array
     */
    public function removeByConditions($conditions, $all = true) {
        if(empty($conditions))
            return false;
        $class = get_called_class();
        return $class::removeBySQLConditions(
            implode(
                $all ? ' AND ' : ' OR ',
                array_map(function($key) {
                    return $key.' = :'.$key;
                }, array_keys($conditions))
            ), SQL::buildValues($conditions, array_keys($conditions)));
    }
    public static function columns($select = true) {
        $class = get_called_class();
        if(defined($class.'::columns') && $class::columns)
            return $class::columns;
        else
            if($columns = implode(',', array_keys(call_user_func('get_object_vars', new $class()))))
                return $columns;
            else
                return '*';
    }
    public static function key() {
        $class = get_called_class();
        if(defined($class.'::key') && $class::key)
            return $class::key;
        else {
            return explode(',', $class::columns())[0];
        }
    }
}