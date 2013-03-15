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
	 * boilerplate every time. $params is for query params, needs to
	 * have keys that match the query string placeholders, in the form of
	 * ':key'
	 */
	public function query($query_string, $params = false) {
		$dbcon = $this->getInstance();
		$statement = $this->_prepareQuery($dbcon, $query_string, $params);
		return $this->executeQuery($statement);
	}
	
	protected function _prepareQuery($dbh, $query_string, $params) {
		$statement = $dbcon->prepare($query_string);
		if ($params) {
			if (!$this->_bindParams($statement, $params)) {
				error_log('call to DbQuery::_bindParams failed with args ' . print_r($params, true));
				return false;
			}
		}
		return $statement;
	}
	
	protected function _bindParams($statement, $params) {
		return array_walk($params, function($val, $key) use($statement){
			$statement->bindValue(':' . $key, $val);			
		});
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
	protected $select_base = '';
	protected $insert_base = '';
	protected $update_base = '';
	protected $id_col = 'id';
	
	function __construct() {
		if ($this->table) {
			$this->select_base = 'select * from ' . $this->table;
			$this->insert_base = 'insert into ' . $this->table;
			$this->update_base = 'update ' . $this->table . ' set ';
		}
	}

	public function getTable() {
		return $this->table;
	}
	
	public function findById($id) {
		$qs = $this->select_base . ' where ' . $this->id_col . ' = :' . $this->id_col;
		return $this->query($qs, array($this->id_col => $id));
	}
	
	protected function _buildUpdateQuery($record) {
		$qs = $this->update_base;
		$id = array($this->id_col => $record[$this->id_col]);
		// filter out the id because it's handled separately
		$params = array_diff_key($record, array($this->id_col => 1));
		// can't help myself... going a little functional here
		// takes the array of columns and turns it into:
		// col1 = :col1, col2 = :col2, ... etc
		$sets = implode(', ', 
						array_map(function($val) { return $val . " = :" . $val; }), 
								  array_keys($params));
		$where = ' where ' . $this->id_col . ' = :' . $this->id_col;
		return $qs . $sets . $where;
	}
	
	protected function _buildCreateQuery($record) {
		$qs = $this->create_base;
		// more functional style... it's addictive really
		$fields = '(' . implode(', ', array_keys($record)) . ') ';
		// and the corresponding values placeholders part
		$values = 'values(' 
			. implode(', ', 
				array_map(
					function($key) {return ":" . $key; },
					array_keys($record)))
			. ')';
		return $qs . $fields . $values;
	}
}


Class User extends Model {

	protected $table = 'accounts';
	
	public function checkLogin($username, $password) {
		$qs = $this->select_base . ' where nick = :nick and password = PASSWORD(:password)'; 
		$result = $this->query($qs, array('nick' => $username, 'password' => $password));
		if (count($result) == 0) {
			return false;
		}
		else { return $result; }
	}
	
	public function exists($username) {
		$qs = $this->select_base . ' where nick = :nick';
		$res = $this->query($qs, array('nick' => $username));
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
		$qs = $this->select_base . ' where time > ' . time() . ' order by time ASC limit 1';
		return $this->query($qs);
	}
}


Class Signup extends Model {
	
	protected $table = 'signups';
	protected $user;
	protected $event; 
	
	function __construct() {
		parent::__construct();
		$this->user = new User();
		$this->event = new Event();
	}
	
	public function getCurrentForUser($user, $event) {
		$qs = $this->select_base . ' where user_id = :user_id AND event_id = :event_id';
		$res = $this->query($qs, array('user_id' => $user['id'], 'event_id' => $event['id']));
		if (count($res) < 1) {
			return false;
		} else {
			return $res;
		}
	}
	
	public function save($signup) {
		$user = array('id' => $signup['user_id']);
		$event = array('id' => $signup['event_id']);
		if ($current = $this->getCurrentForUser($user, $event)) {
			$signup['id'] = $current['id'];
			$this->_update($signup); 
		} else { $this->_create($signup); }
	}
}

class Mode extends Model {

	protected $table = 'modes';
	private $select_event_modes = 'select * from modes 
		inner join event_modes on event_modes.mode_id = modes.id 
		inner join events on events.id = event_modes.event_id 
		where events.id = ?';

	public function getForEvent($event) {
		$res = $this->query($this->select_event_modes, array($event['id']));
	}
	
	public function saveAllForEvent($event, $modes) {
		$this->deleteForEvent($event);
		// it's a closure
		$results = array_map(function($mode) use ($event) { return $this->saveForEvent($event, $mode); }, $modes);
		return (in_array(false, $results));
	}

	public function saveForEvent($event, $mode) {
		$qs = 'insert into event_modes (event_id, mode_id) values (?, ?)';
		return $this->query($qs, array($event['id'], $mode['id']));
	}
	
	public function deleteForEvent($event) {
		$qs = 'delete from event_modes where event_id = ?';
		return $this->query($qs, array($event['id']));
	}
	
	public function isEventMode($event, $mode) {
		$cond = ' and modes.id = ?';
		$res = $this->query($this->select_event_modes . $cond, array($event['id'], $mode['id']));
		return !(empty($res));
	}

}
