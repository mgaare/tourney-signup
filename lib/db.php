<?php

Class DbConnection
{
	private static $instance = null;
	private static $dsn = '';
	private static $username = '';
	private static $password = '';
	
	public static function instance() {
		if ($this->instance) {
			return $this->instance;
		}
		return $this->instance = new PDO($this->dsn, $this->username, $this->password);
	}
	
}

Class DbQuery {
	protected $dbcon = null;
	
	protected function getInstance() {
		if ($this->dbcon) {
			return $this->dbcon;
		}
		return $this->dbcon = DbConnection::instance();
	}
	
	/**
	 * handy little query abstraction so I don't have to type all this
	 * boilerplate every time. $params is for query params, an array in
	 * the same order as the ?'s in the statement
	 */
	public function query($query_string, $params = false) {
		$dbcon = $this->getInstance();
		$statement = $dbcon->prepare($query_string);
		if ($params) {
			// just to make sure this is a numeric-indexed array with keys starting at 0
			$params = array_values($params);
			// bindValue wants things to start at 1 and needs vars passed by reference
			foreach ($params as $index => &$value) {
				$statement->bindValue($index + 1, $value);
			}
		}
		return $this->executeQuery($statement);
	}
	
	public function executeQuery($statement) {
		if (!$statement->execute()) {
			return false;
		}
		return $statement->fetchAll();
	}
}

Class Model extends DbQuery {

	protected $table = null;
	protected $qs_base = '';
	protected $id_col = 'id';
	
	function __construct() {
		if ($this->table) {
			$this->qs_base = 'select * from ' . $this->table;
		}
	}

	public function getTable() {
		return $this->table;
	}
	
	public function findById($id) {
		$qs = $this->qs_base . ' where ' . $id_col . ' = ?';
		return $this->query($qs, array($id));
	}
}


Class User extends Model {

	protected $table = 'accounts';
	
	public function checkLogin($username, $password) {
		$qs = $this->qs_base . ' where nick = ? and password = PASSWORD(?)'; 
		$result = $this->query($qs, array($username, $password));
		if (count($result) == 0) {
			return false;
		}
		else { return $result; }
	}
	
	public function userExists($username) {
		$qs = $this->qs_base . ' where nick = ?';
		$res = $this->query($qs, array($username));
		return (count($res) > 0);
	}
	
	public function loginUser($user) {
		$_SESSION['player_logged_in'] = true;
		$_SESSION['player'] = $user;
	}
	
	public function isLoggedIn() {
		return (isset($_SESSION['player_logged_in']) 
			&& $_SESSION['player_logged_in']);
	}
	
}


Class Event extends Model {
	
	protected $table = 'events';
	
	public function getCurrent() {
		$qs = $this->qs_base . ' where time > ' . time() . ' order by time ASC limit 1';
		return $this->query($qs);
	}
	
}
