<?php

namespace OudyPlat;

class Entity extends Object {
    const columns = '';
    const table = '';
    const key = '';
    const types = '';
    protected $changes = array();
    protected $errors = array();
    public function __construct($data = null, $allowedProperties = null, $forceAll = null) {
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
     * @param string $key, primary keys
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
                        return $key.'=:'.$key;
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
                    return $key.'=:'.$key;
                }, array_keys($conditions))
            ), SQL::buildValues($conditions, array_keys($conditions)));
    }
}