<?php

Class DbConnection
{
	private static $instance = null;
	private static $dsn = '';
	private static $username = '';
	private static $password = '';
	
	public static function instance() {
		if (self::$instance) {
			return self::$instance;
		}
		return self::$instance = new PDO(self::$dsn, self::$username, self::$password);
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
	
	public function debugQuery($query_string, $params = false) {
		$dbcon = $this->getInstance();
		$statement = $this->_prepareQuery($dbcon, $query_string, $params);
		$statement->debugDumpParams();
		return $statement;
	}
	
	protected function _prepareQuery($dbh, $query_string, $params) {
		$statement = $dbh->prepare($query_string);
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
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}
}

Class Model extends DbQuery {

	protected $table;
	protected $select_base = '';
	protected $insert_base = '';
	protected $update_base = '';
	protected $id_col = 'id';
	
	public function __construct() {
		if ($this->table) {
			$this->select_base = 'select * from ' . $this->table;
			$this->insert_base = 'insert into ' . $this->table;
			$this->update_base = 'update ' . $this->table . ' set ';
		}
		ModelStore::addInstance($this);
	}

	public function getTable() {
		return $this->table;
	}
	
	public function findById($id) {
		$qs = $this->select_base . ' where ' . $this->id_col . ' = :' . $this->id_col;
		return first($this->query($qs, array($this->id_col => $id)));
	}
	
	/**
	 * does a select * from table, and then adds where conditions from params,
	 * which needs to have array keys that match column names
	 */ 
	//which needs
	public function findSimple($params = false) {
		$qs = $this->select_base;
		if ($params) {
			$wheres = implode(' and ', 
				map(function($col) {
						return "{$col} = :{$col}";
					}, array_keys($params)));
			$qs .= " where {$wheres}";
		}
		return $this->query($qs, $params);
	}
	
	public function update($record) {
		$qs = $this->_buildUpdateQuery($record);
		return $this->query($qs, $record);
	}
	
	protected function _buildUpdateQuery($record, $qs_base = false) {
		if ($qs_base) {
			$qs = $qs_base;
		} else {
			$qs = $this->update_base;
		}
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
	
	public function create($record) {
		$qs = $this->_buildCreateQuery($record);
		return $this->query($qs, $record);
	}
	
	protected function _buildCreateQuery($record, $qs_base = false) {
		if ($qs_base) {
			$qs = $qs_base;
		} else {
			$qs = $this->insert_base;
		}
		// more functional style... it's addictive really
		$fields = ' (' . implode(', ', array_keys($record)) . ') ';
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

Class ModelStore {

	private static $models = array();
	
	public static function addInstance($model) {
		self::$models[get_class($model)] = $model;
	}
	
	public static function isInstance($model_class) {
		return (isset(self::$models[$model_class]));
	}
	
	public static function getInstance($model_class) {
		if (ModelStore::isInstance($model_class)) {
			return self::$models[$model_class];
		} else {
			//self::$models[$model_class] = true;
			return self::$models[$model_class] = new $model_class();
		}		
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
		else { return first($result); }
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
	
	public function getLoggedIn() {
		return $_SESSION['player'];	
	}
}


Class Event extends Model {
	
	protected $table = 'events';
	
	public function getCurrent() {
		$qs = $this->select_base . ' where time > ' . time() - (60*60*24)
			. ' order by time ASC limit 1';
		$res = $this->query($qs);
		return first($res);
	}
}


Class Signup extends Model {
	
	protected $table = 'signups';
	public $user;
	public $event;
	public $mode;
	
	function __construct() {
		parent::__construct();
		$this->user = ModelStore::getInstance('User');
		$this->event = ModelStore::getInstance('Event');
		$this->mode = ModelStore::getInstance('Mode');
	}
	
	public function getForUser($user, $event) {
		$qs = $this->select_base . ' where user_id = :user_id '
			. 'AND event_id = :event_id';
		$res = $this->query($qs, 
			array('user_id' => $user[$this->user->id_col],
				'event_id' => $event[$this->event->id_col]));
		if (count($res) < 1) {
			return false;
		} else {
			return $res;
		}
	}
	
	public function deleteForUser($user, $event) {
		$qs = "delete from {$this->table} where user_id = :user_id "
			. "AND event_id = :event_id";
		return $this->query($qs, 
			array('user_id' => $user[$this->user->id_col],
				  'event_id' => $event[$this->event->id_col]));	
	}
	
	public function getForEventAndMode($event, $mode) {
		$qs = "{$this->select_base}  where event_id = :event_id "
			. "and mode_id = :mode_id";
		return $this->query($qs,
			array('event_id' => $event[$this->event->id_col],
				  'mode_id' => $event[$this->mode->id_col]));
	}
}

class Mode extends Model {
	
	protected $table = 'modes';
	public $event;
	private $select_event_modes = 'select modes.id as id, modes.name as name,
		modes.team_mode as team_mode
		from modes inner join event_modes on event_modes.mode_id = modes.id 
		where event_modes.event_id = :id';

	function __construct() {
		parent::__construct();
		$this->event = ModelStore::getInstance('Event');
	}

	public function getForEvent($event) {
		$res = $this->query($this->select_event_modes, 
							array('id' => $event[$this->event->id_col]));
		return $res;
	}
	
	public function saveAllForEvent($event, $modes) {
		$this->deleteForEvent($event);
		// it's a closure
		$results = array_map(
			function($mode) use ($event) {
				 return $this->saveForEvent($event, $mode); },
			$modes);
		return (in_array(false, $results));
	}

	public function saveForEvent($event, $mode) {
		$qs = 'insert into event_modes (event_id, mode_id) ' 
			. 'values (:event_id, :mode_id)';
		return $this->query($qs, 
			array($event[$this->event->id_col], $mode[$this->id_col]));
	}
	
	public function deleteForEvent($event) {
		$qs = 'delete from event_modes where event_id = :event_id';
		return $this->query($qs, array('event_id' => $event[$this->event->id_col]));
	}
	
	public function isEventMode($event, $mode) {
		$cond = ' and modes.id = ?';
		$res = $this->query($this->select_event_modes . $cond, array($event['id'], $mode['id']));
		return !(empty($res));
	}

}

class Map extends Model {

	public $mode;
	protected $table = 'maps';
	
	function __construct() {
		parent::__construct();
		$this->mode = ModelStore::getInstance('Mode');		
	}
	
	public function getForMode($mode) {
		$qs = "select maps.id as id, maps.name as name, "
			. "mode_maps.qualification as qualification, "
			. "mode_maps.all_v_all as all_v_all from maps "
			. "inner join mode_maps on mode_maps.map_id = maps.id " 
			. "where mode_maps.mode_id = :mode_id";
		$params = array('mode_id' => $mode['id']);
		return $this->query($qs, $params);
	}
	
	public function getAllModes($map) {
		$qs = "select * from mode_maps where map_id = :map_id";
		$params = array('map_id' => $map['id']);
		return $this->query($qs, $params);
	}
	
	public function saveForMode($map, $mode) {
		$qs_base = 'insert into mode_maps ';
		$params = array('mode_id' => $mode[$this->mode->id_col],
						'map_id' => $map[$this->id_col],
						'qualification' => $map['qualification'],
						'all_v_all' => $map['all_v_all']);
		return $this->create($params);
	}
	
	public function saveAllForMode($maps, $mode) {
		$this->deleteForMode($mode);
		// it's a closure
		$results = array_map(
			function($map) use ($mode) {
				 return $this->saveForMode($map, $mode); },
			$modes);
		return (in_array(false, $results));
	}
	
	public function deleteForMode($mode) {
		$qs = 'delete from mode_maps where mode_id = :mode_id';
		return $this->query($qs, array('mode_id' => $mode[$this->mode->id_col]));
	}
}

class Vote extends Model {
	
	protected $table = 'votes';
	public $event;
	public $mode;
	public $map;
	public $user;
	
	function __construct() {
		parent::__construct();
		$this->event = ModelStore::getInstance('Event');
		$this->mode = ModelStore::getInstance('Mode');
		$this->map = ModelStore::getInstance('Map');
		$this->user = ModelStore::getInstance('User');
	}
	
	public function deleteForUserEvent($user, $event) {
		$qs = "delete from {$this->table} where user_id = :user_id "
			. "AND event_id = :event_id";
		return $this->query($qs, 
			array('user_id' => $user[$this->user->id_col],
				  'event_id' => $event[$this->event->id_col]));	
	}
	
	public function getCountsForEventMode($event, $mode) {
		$qs = "select map_id, count(qualification), count(all_v_all) "
			. "from {$this->table} where event_id = :event_id "
			. "and mode_id = :mode_id";
		return $this->query($qs,
			array('event_id' => $event[$this->event->id_col],
				  'mode_id' => $mode[$this->mode->id_col])); 
	}
	
}
	