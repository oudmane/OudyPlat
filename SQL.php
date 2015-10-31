<?php

defined('OUDY_EXEC') or die;

/**
 * functions to generate SQL queries
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class SQL {
	/**
	 * Generate SELECT query
	 * @param String|Array|Object $columns
	 * @param String $table
	 * @param String $condition
	 * @param String $order
	 * @param Int $limit
	 * @return string
	 */
	public static function select($columns, $table, $condition = null, $order = null, $limit = null) {
		$columns = SQL::toString($columns);
		$query = 'SELECT '.$columns.' FROM '.$table;
		if($condition) $query .= ' WHERE '.$condition;
		if($order) $query .= ' ORDER BY '.$order;
		if($limit) $query .= ' LIMIT '.$limit;
		return $query;
	}
	/**
	 * Generate an UPDATE query
	 * @param String|Array|Object $columns
	 * @param String $table
	 * @param String $condition
	 * @return string
	 */
	public static function update($columns, $table, $condition = null) {
		$columns = SQL::toString($columns);
		$query = 'UPDATE '.$table.' SET ';
		$set = array();
		foreach(explode(',', $columns) as $column) $set[] = $column.'=:'.$column;
		$query .= implode(',', $set);
		if($condition) $query .= ' WHERE '.$condition;
		return $query;
	}
	/**
	 * Generate an INSERT query
	 * @param String|Array|Object $columns
	 * @param String $table
	 * @return string
	 */
	public static function insert($columns, $table) {
		$columns = SQL::toString($columns);
		$query = 'INSERT INTO '.$table.' (';
		$query .= implode(',', explode(',', $columns));
		$query .= ') VALUES(:'.implode(',:', explode(',', $columns)).')';
		return $query;
	}
	/**
	 * Generate an INSERT with IGNORE query
	 * @param String|Array|Object $columns
	 * @param String $table
	 * @return string
	 */
	public static function insertIgnore($columns, $table) {
		$columns = SQL::toString($columns);
		$query = 'INSERT IGNORE INTO '.$table.' (';
		$query .= implode(',', explode(',', $columns));
		$query .= ') VALUES(:'.implode(',:', explode(',', $columns)).')';
		return $query;
	}
	public static function insertOrUpdate($columns, $table, $key) {
		$columns = SQL::toString($columns);
		$columns = explode(',', $columns);
		$query = 'INSERT INTO '.$table.' (';
		$query .= implode(',', $columns);
		$query .= ') VALUES(:'.implode(',:', $columns).')';
		$query .= ' ON DUPLICATE KEY UPDATE ';
		unset($columns[array_search($key, $columns)]);
		foreach($columns as $column) $set[] = $column.'=:'.$column;
		$query .= implode(',', $set);
		return $query;
	}
	public static function delete($table, $condition = '') {
		$query = 'DELETE FROM '.$table;
		if($condition) $query .= ' WHERE '.$condition;
		return $query;
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
	/**
	 * Build Insertion values from Object
	 * @param Object $object
	 * @param String|Array $columns
	 * @return type
	 */
	public static function buildIns($object,$columns) {
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