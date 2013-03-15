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
		$qs = $this->select_base . ' where ' . $id_col . ' = ?';
		return $this->query($qs, array($id));
	}
	
	protected function _update($record) {
		$qs = $this->update_base;
		$id = $record[$this->id_col];
		// filter out the id because it's handled separately
		$params = array_diff_key($record, array($this->id_col => 1));
		// can't help myself... going a little functional here
		// takes the array of columns and turns it into:
		// col1 = ?, col2 = ?, ... etc
		$sets = implode(', ', 
						array_map(function($val) { return $val . " = ?"; }), 
								  array_keys($params));
		$where = ' where ' . $this->id_col . ' = ?';
		$qs = $qs . $sets . $where;
		// now we need the id back at the end of the params list
		// at this point you might be thinking, why not just use named params
		// in the statement?
		// shut up.
		return $this->query($qs, array_merge(array_values($params), 
											 array($this->id_col => $id)));
	}
	
	protected function _create($record) {
		$qs = $this->create_base;
		// more functional style... it's addictive really
		$fields = '(' . implode(', ', array_keys($record)) . ') ';
		// does it seem overkill to use a map with an anonymous function just
		// to create a string with the correct number of ?'s? A weaker man
		// might say yes. Readability would be better if eclipse new how to
		// tab properly.
		$values = 'values(' 
			. implode(', ', 
				array_map(
					function() {return "?"; },
					array_values($record)))
			. ')';
		$qs = $qs . $fields . $values;
		return $this->query($qs, array_values($record));
	}
}


Class User extends Model {

	protected $table = 'accounts';
	
	public function checkLogin($username, $password) {
		$qs = $this->select_base . ' where nick = ? and password = PASSWORD(?)'; 
		$result = $this->query($qs, array($username, $password));
		if (count($result) == 0) {
			return false;
		}
		else { return $result; }
	}
	
	public function exists($username) {
		$qs = $this->select_base . ' where nick = ?';
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
		$qs = $this->select_base . ' where time > ' . time() . ' order by time ASC limit 1';
		return $this->query($qs);
	}
}


Class Signup extends Model {
	
	protected $table = 'signups';
	
	public function getCurrentForUser($user, $event) {
		$qs = $this->select_base . ' where user_id = ? AND event_id = ?';
		$res = $this->query($qs, array($user['id'], $event['id']));
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
