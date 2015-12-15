<?php

namespace OudyPlat;

/**
 * 
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class Session extends Object {
    public static $configuration = null;
    public $id = 0;
    public $data = array();
    public $lastTime = 0;
    public $ip = '';
    public function __construct() {
        if(!isset(self::$configuration->isSocket) || !self::$configuration->isSocket) {
            $this->id = Request::getCookie(self::$configuration->cookie);
            if(empty($this->id)) {
				$this->id = Crypt::genRandomPassword(32);
				Request::setCookie(self::$configuration->cookie, $this->id, time()+60*60*24*90, '/', self::$configuration->domain);
			}
        }
        $this->load();
        $this->ip = $_SERVER['REMOTE_ADDR'];
    }
    public function load() {
        $session = MySQL::select(array(
            'columns'=>     'data,lastTime',
            'table'=>       'sessions',
            'condition'=>   'id = :id'
        ), array(
            ':id'=>         $this->id
        ))->fetchObject();
        if($session) {
            $this->data = (array) json_decode($session->data);
            $this->lastTime = $session->lastTime;
        }
    }
    public function save() {
        $this->lastTime = time();
        MySQL::insert(array(
            'columns'=>     'id,data,lastTime,ip',
            'table'=>       'sessions',
            'update'=>      true,
            'key'=>         'id'
        ), SQL::buildValues($this, 'id,data,lastTime,ip'));
    }
    public function __destruct() {
        $this->save();
    }
}