<?php

namespace OudyPlat;

class Entity extends Object {
    const columns = '';
    const table = '';
    const key = '';
    const database = '';
    const types = '';
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
                    return $this->load();
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
}