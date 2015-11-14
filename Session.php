<?php

defined('OUDY_EXEC') or die;

/**
 * Description of Session
 *
 * @author Ayoub Oudmane <ayoub at oudmane.me>
 */
class Session extends Object {
	/**
	 * Session id
	 * @var string
	 */
	public $id = 0;
	/**
	 *	Session data
	 * @var array
	 */
	public $data = array();
	/**
	 * Timestamp of last activity
	 * @var int
	 */
	public $lastTime = 0;
	/**
	 *	App Identifer
	 * @var string
	 */
	public $client = '';
	/**
	 * IP address
	 * @var string
	 */
	public $ip = '';
	/**
	 * User id
	 * @var User
	 */
	public $user = 0;
	const columns = 'id,lastTime,data,ip,user,client';
	const table = 'sessions';
	const key = 'id';
	public function __construct($configuration) {
		if(isset($configuration['user']) && $configuration['user']) {
			$this->user = new $configuration['user']();
		}
		if(isset($configuration['client']) && $configuration['client']) {
			$this->client = $configuration['client'];
		}
		if(!isset($configuration['isSocket'])) {
			$this->id = InCookie::get($configuration['cookie']);
			if(empty($this->id)) {
				$this->id = Crypt::genRandomPassword(32);
				InCookie::set($configuration['cookie'], $this->id, 0, '/', $configuration['domain']);
			}
//			if(isset($configuration['id'])) {
//				session_id($configuration['id']);
//			}
//			if(isset($configuration['cookie']) && $configuration['cookie']) {
//				session_name($configuration['cookie']);
//			}
//			if(isset($configuration['domain']) && $configuration['domain']) {
//				session_set_cookie_params(0, '/', $configuration['domain']);
//			}
//			switch($configuration['handler']) {
//				case 'database':
//					session_set_save_handler('Session::open', 'Session::close', 'Session::read', 'Session::write', 'Session::destroy', 'Session::clean');
//					break;
//			}
//			if(session_status() == PHP_SESSION_NONE) {
//				session_start();
//			}
//			$this->data =& $_SESSION;
//			$this->id = session_id();
//		} else {
////			$this->id = $configuration['id'];
		}
		$this->load();
	}
	public function load() {
		$statement = new Database(
			SQL::select('data,lastTime,ip,user', 'sessions', 'id=:id'),
			array(':id'=>$this->id)
		);
		if($fetch = $statement->fetchObject()) {
			$this->lastTime = $fetch->lastTime;
			$this->ip = $fetch->ip;
			$this->data = (array) json_decode($fetch->data);
			if(isset($this->user)) {
				$this->user->load($fetch->user);
			}
		}
	}
	public function save() {
		$this->lastTime = time();
		if(isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];
		$statement = new Database(
			$query = SQL::insertOrUpdate(
				self::columns,
				self::table,
				self::key
			),
			SQL::buildIns($this, self::columns)
		);
	}
	public function setUser($id) {
		$this->user = new User($id);
		$statement = new Database(
			SQL::update('user','sessions','id=:id'),
			array(':id'=>$this->id,':user'=>$this->user->id)
		);
		if($id) $statement = new Database(SQL::insert('time,user','user_logins','id=:id'),array(':time'=>time(),':user'=>$id));
	}
	public function set($key, $value) {
		$this->data[$key] = $value;
	}
	public function get($key, $destroy = false) {
		$value = isset($this->data[$key]) ? $this->data[$key] : null;
		if($destroy) $this->destroy($key);
		return $value;
	}
	public function destroy($key) {
		unset($this->data[$key]);
	}
}