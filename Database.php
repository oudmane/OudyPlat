<?php

defined('OUDY_EXEC') or die;

/**
 * Description of Database
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class Database {
	/**
	 * to store the connection with the database
	 * @var type
	 */
	public static $connection = null;
	/**
	 * Database Configuration
	 * @var type
	 */
	public static $configuration = null;
	/**
	 * to connect and get the connection
	 */
	public static function getConnection() {
		// check if already connected
		if(is_null(self::$connection)) {
			// get configuration
			$configuration = self::$configuration;
			// connect using PDO
			self::$connection = new PDO(
				$configuration->driver.':dbname='.$configuration->database.';host='.$configuration->host,
				$configuration->username,
				$configuration->password,
				array(
					PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
					PDO::ATTR_PERSISTENT => TRUE
				)
			);
			self::$connection->setAttribute(
				PDO::ATTR_ERRMODE,
				PDO::ERRMODE_EXCEPTION
			);
		}
		return self::$connection;
	}
	/**
	 * send a query to database
	 * @param string $query
	 * @param array $values
	 */
	public function __construct($query = null, $values = null) {
		$this->database = Database::getConnection();
		if(empty($query)) return;
		$this->setQuery($query);
		$this->execute($values);
	}
	/**
	 * set the query
	 * @param string $query
	 */
	public function setQuery($query) {
		$this->statement = $this->database->prepare($query);
	}
	/**
	 * execute the query
	 * @param array $values
	 */
	public function execute($values = array()) {
		if(empty($values)) {
			$this->statement->execute();
		} else {
			$this->statement->execute($values);
		}
	}
	/**
	 * return one row as array of array(row)
	 * @return array
	 */
	public function fetch() {
		return $this->statement->fetch(PDO::FETCH_ASSOC);
	}
	/**
	 * return all rows in array of of array(row)
	 * @return array
	 */
	public function fetchAll() {
		return $this->statement->fetchAll(PDO::FETCH_ASSOC);
	}
	/**
	 * return one column of all rows in one array
	 * @param int $column
	 * @return array
	 */
	public function fetchAllColumn($column = 0) {
		return $this->statement->fetchAll(PDO::FETCH_COLUMN, $column);
	}
	/**
	 * return array of objects with rows values
	 * @param string $class
	 * @return array
	 */
	public function fetchAllClass($class) {
		return $this->statement->fetchAll(PDO::FETCH_CLASS, $class);
	}
	/**
	 * return one column
	 * @return string
	 */
	public function fetchColumn() {
		return $this->statement->fetchColumn();
	}
	/**
	 * return row as Object
	 * @return object
	 */
	public function fetchObject() {
		return $this->statement->fetchObject();
	}
	/**
	 * return array of objects with rows values
	 * @return array
	 */
	public function fetchAllObject() {
		return $this->statement->fetchAll(PDO::FETCH_OBJ);
	}
	/**
	 * return row count
	 * @return int
	 */
	public function rowCount() {
		return $this->statement->rowCount();
	}
	/**
	 * return last id
	 * @return int
	 */
	public function lastid() {
		return $this->database->lastInsertId();
	}
	/**
	 * return found rows count
	 * @return int
	 */
	public function foundRows() {
		return $this->database->query('SELECT FOUND_ROWS();')->fetch(PDO::FETCH_COLUMN);
	}
}