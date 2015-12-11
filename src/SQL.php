<?php

namespace OudyPlat;

class SQL {
    /**
     * Generate SELECT query
     * @param array $query
     * @return string
     */
    public static function select($query) {
        $sql = array();
        
        $sql[] = 'SELECT '.self::toString($query['columns']);
        $sql[] = 'FROM '.$query['table'];
        if(isset($query['condition']))
            $sql[] = 'WHERE '.$query['condition'];
        if(isset($query['join']))
            foreach($query['join'] as $join)
                $sql[] = $join;
        if(isset($query['groupBy']))
            $sql[] = 'GROUP BY '.$query['groupBy'];
        if(isset($query['having']))
            $sql[] = 'HAVING '.$query['having'];
        if(isset($query['orderBy']))
            $sql[] = 'ORDER BY '.$query['orderBy'];
        if(isset($query['limit']))
            $sql[] = 'LIMIT '.$query['limit'];
        
        return implode("\n", $sql);
    }
    public static function update($query) {
        $sql = array();
        $set = array();
        
        $sql[] = 'UPDATE '.$query['table'];
        $sql[] = 'SET';
        foreach(explode(',', self::toString($query['columns'])) as $column)
            $set[] = "\t".$column.' = :'.$column;
        $sql[] = implode(",\n", $set);
        if(isset($query['condition']))
            $sql[] = 'WHERE '.$query['condition'];
        
        return implode("\n", $sql);
    }
    public static function insert($query) {
        $sql = array();
        $columns = explode(',', self::toString($query['columns']));
        $sqlCollumns = array();
        $sqlValues = array();
        
        $sql[] = 'INSERT '.(isset($query['ignore']) ? 'IGNORE ' : '').'INTO '.$query['table'].' (';
        foreach($columns as $column) {
            $sqlCollumns[] = "\t".$column;
            $sqlValues[] = "\t:".$column;
        }
        $sql[] = implode(",\n", $sqlCollumns);
        $sql[] = ') VALUES (';
        $sql[] = implode(",\n", $sqlValues);
        $sql[] = ')';
        
        if(isset($query['update'])) {
            $set = array();
            $sql[] = 'ON DUPLICATE KEY UPDATE';
            foreach($columns as $column)
                if($column != $query['key'])
                    $set[] = "\t".$column.' = :'.$column;
            $sql[] = implode(",\n", $set);
        }
        
        return implode("\n", $sql);
    }
    public function delete($query) {
        $sql = array();
        
        $sql[] = 'DELETE FROM '.$query['table'];
        if(isset($query['condition']))
            $sql[] = 'WHERE '.$query['condition'];
        
        return implode("\n", $sql);
    }
	public static function toString($columns) {
		switch(gettype($columns)) {
			case 'object':
				$columns = get_object_vars($columns);
			case 'array':
			if(array_keys($columns) === range(0, count($columns) - 1)) $columns = array_flip($columns);
				return implode(',', array_keys($columns));
			break;
			default:
				return $columns;
			break;
		}
	}
	public static function buildValues($object, $columns) {
		$return = array();
		if(gettype($columns)=='string') $columns = explode(',', $columns);
		foreach($columns as $column) {
			switch(gettype($object->$column)) {
				case 'array':
					$return[':'.$column] = json_encode($object->$column);
				break;
				case 'object':
					if(isset($object->$column->id)) {
						$return[':'.$column] = $object->$column->id;
					} else {
						$return[':'.$column] = json_encode($object->$column);
					}
				break;
				default:
					$return[':'.$column] = $object->$column;
				break;
			}			
		}
		return $return;
	}
}