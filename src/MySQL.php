<?php

namespace OudyPlat;

/**
 * 
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class MySQL {
    /**
     *
     * @var \PDO
     */
    private static $connection = null;
    /**
     * Configuration
     * @var \OudyPlat\Object
     */
    private static $configuration = null;
    /**
     *
     * @var \PDOStatement
     */
    private $statement = null;
    /**
     * initialize a statement for MySQL
     * @param string $query
     * @param array $values
     */
    public function __construct($query = null, $values = null) {
        if($query) {
            $this->prepare($query);
            $this->execute($values);
        }
    }
    /**
     * 
     * @param string $query
     * @return MySQL
     */
    public function prepare($query) {
        return
            $this->statement = self::getConnection()->prepare($query);
    }
    public function execute($values = array()) {
        if(empty($values))
            $this->statement->execute();
        else
            $this->statement->execute($values);
        return $this;
    }
    /**
     * fetch the next row
     * @return array
     */
    public function fetch() {
        return $this->statement->fetch(\PDO::FETCH_ASSOC);
    }
    /**
     * fetch all rows
     * @return array
     */
    public function fetchAll() {
        return $this->statement->fetchAll(\PDO::FETCH_ASSOC);
    }
    /**
     * fetch the column from next row
     * @param int $column
     * @return mixed
     */
    public function fetchColumn($column = 0) {
        return $this->statement->fetchColumn($column);
    }
    /**
     * return array containing only the column
     * @param int $column
     * @return array
     */
    public function fetchAllColumn($column = 0) {
        return $this->statement->fetchAll(\PDO::FETCH_COLUMN, $column);
    }
    /**
     * fetch nex row as $class
     * @param mixed $class
     * @return $class
     */
    public function fetchClass($class) {
        $this->statement->setFetchMode(\PDO::FETCH_CLASS, $class);
        return $this->statement->fetch(\PDO::FETCH_CLASS);
    }
    /**
     * fetch all rows as $class
     * @param mixed $class
     * @return array
     */
    public function fetchAllClass($class) {
        return $this->statement->fetchAll(\PDO::FETCH_CLASS, $class);
    }
    /**
     * fetch next row as Object
     * @return \OudyPlat\Object
     */
    public function fetchObject() {
        return $this->fetchClass('\OudyPlat\Object');
    }
    /**
     * fetch all rows as Object
     * @return array
     */
    public function fetchAllObject() {
        return $this->fetchAllClass('\OudyPlat\Object');
    }
    /**
     * rows count
     * @return int
     */
    public function rowCount() {
        return $this->statement->rowCount();
    }
    /**
     * last Inserted Id
     * @return mixed
     */
    public function lastInsertId() {
        return self::getConnection()
                ->lastInsertId();
    }
    /**
     * found rows count
     * @return int
     */
    public function foundRows() {
        return self::getConnection()
                ->query('SELECT FOUND_ROWS();')
                ->fetch(\PDO::FETCH_COLUMN);
    }
    /**
     * configure Database Connection
     * @param string $driver
     * @param string $host
     * @param string $database
     * @param string $username
     * @param string $password
     */
    public static function configure($host, $database, $username, $password = null) {
        self::$configuration = new Object(array(
            'host'=> $host,
            'database'=> $database,
            'username'=> $username,
            'password'=> $password
        ));
    }
    /**
     * get the Connection with MySQL
     * @return \PDO
     */
    private static function getConnection() {
        if(is_null(self::$connection)) {
            self::$connection = new \PDO(
                'mysql:dbname='.self::$configuration->database.';host='.self::$configuration->host,
                self::$configuration->username,
                self::$configuration->password,
                array(
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                    \PDO::ATTR_PERSISTENT => FALSE
                )
            );
            self::$connection->setAttribute(
                \PDO::ATTR_ERRMODE,
                \PDO::ERRMODE_EXCEPTION
            );
        }
        
        return self::$connection;
    }
    /**
     * 
     * @param array|string $query
     * @param array $values
     * @return \OudyPlat\MySQL
     */
    public static function select($query, $values = array()) {
        if(gettype($query) != 'string')
            $query = SQL::select($query);
        return new MySQL($query, $values);
    }
    public static function update($query, $values = array()) {
        if(gettype($query) != 'string')
            $query = SQL::update($query);
        return new MySQL($query, $values);
    }
    public static function insert($query, $values = array()) {
        if(gettype($query) != 'string')
            $query = SQL::insert($query);
        return new MySQL($query, $values);
    }
    public static function delete($query, $values = array()) {
        if(gettype($query) != 'string')
            $query = SQL::delete($query);
        return new MySQL($query, $values);
    }
}