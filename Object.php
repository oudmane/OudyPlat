<?php

defined('OUDY_EXEC') or die();

class Object {
	/**
	 * Initialate the object
	 * @param array|object $data Data to assing to this Object
	 * @param array|object|string $allowedProperties Filter properties in $data
	 */
	public function __construct($data = null, $allowedProperties = null) {
		// stop if there's nothing to assing to this object
		if(empty($data)) return;
		// convert $data to array if it's an object
		if(gettype($data) == 'array') $data = (object) $data; //get_object_vars();
		if($allowedProperties) {
			// convert the allowed types to arrays if it's a string
			if(gettype($allowedProperties) == 'string') {
				// $allowedProperties = explode(',', $allowedProperties);
				preg_match_all('/\w+(:\w+(\([A-z0-9,]+\))?)?(\.\(.*?\)+)?/', $allowedProperties, $allowedProperties);
				$allowedProperties = $allowedProperties[0];
			}
			// convert the allowed types to arrays if it's an object
			else if(gettype($allowedProperties) == 'object') {
				$allowedProperties = array_keys(get_object_vars($allowedProperties));
			}
			// convert the allowed types to arrays if it's an associative array
			else if(array_keys($allowedProperties) !== range(0, count($allowedProperties) - 1)) {
				$allowedProperties = array_keys($allowedProperties);
			}
		} else {
			$allowedProperties = array_keys(get_object_vars($data));
		}
		// assing properties
		foreach($allowedProperties as $property) {
			if(!preg_match('/^\w+$/', $property)) {
				// get the key
				preg_match('/^\w+/', $property, $key);
				$key = $key[0];
			// if it is a function
			if(preg_match('/^\w+:\w+(\([A-z0-9,]+\))?/', $property, $function)) {
				// get the function
				preg_match_all('/\w+/', $function[0], $functionName);
				$functionName = $functionName[0][1];
				if(preg_match('/^\w+:\w+\([A-z0-9,]+\)/', $function[0]) && $params = preg_replace('/^\w+:\w+\(|\)$/', '', $function[0])) {
					$this->$key = $data->$functionName($params);
				} else $this->$key = $data->$functionName();
			}
			if(preg_match('/\w+(:\w+(\([A-z0-9,]+\))?)?(\.\(.*?\)+)/', $property, $columns)) {
				$this->$key = new Object($data->$key, preg_replace('/^\.\(|\)$/', '', array_pop($columns)));
			}
			} else $this->$property = $data->$property;
		}
	}
	/**
	 * to convert the object to string of JSON
	 * @return string
	 */
	public function __toString() {
		return json_encode($this);
	}
	/**
	 * return the object with specific properities
	 * @param array|object|string $allowedProperties
	 * @return object
	 */
	public function returnObject($allowedProperties) {
		return new Object($this, $allowedProperties);
	}
	/**
	 * return the object as array, with specific properities
	 * @param array|object|string $allowedProperties
	 * @return array
	 */
	public function returnArray($allowedProperties = '') {
		$array = array();
		foreach($this->returnObject($allowedProperties) as $key=>$value)
			$array[] = $value;
		return $array;
	}
	/**
	 * return the object with specific properities
	 * @param array $objects
	 * @param array|object|string $allowedProperties
	 * @return array
	 */
	public static function returnObjects($objects, $allowedProperties) {
		$array = array();
		foreach ($objects as $object) {
			array_push($array, new Object($object, $allowedProperties));
		}
		return $array;
	}
}