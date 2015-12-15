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

    public function load($key) {
        if(empty($key))
            return false;
        
        $class = get_class($this);
        $fetch = MySQL::select(
            array(
                'columns'=> $class::columns,
                'table'=> $class::table,
                'condition'=> $class::key.'=:key'
            ),
            array(':key'=>$key)
        )->fetch();
        if($fetch)
            $this->__construct($fetch);
        return $fetch ? true : false;
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
}