<?php
/**
 * Cookie - Library untuk framework kecik, library ini khusus untuk membantu masalah Database
 *
 * @author 		Dony Wahyu Isp
 * @copyright 	2015 Dony Wahyu Isp
 * @link 		http://github.io/database
 * @license		MIT
 * @version 	1.1.0
 * @package		Kecik\Database
 **/
namespace Kecik;

/**
 * Database
 * @package 	Kecik\Database
 * @author 		Dony Wahyu Isp
 * @since 		1.0.1-alpha
 **/
class Database {
	/**
	 * @var Kecik Class $app
	 **/
	private $app;

	/**
	 * @var string $dsn, $driver, $hostname, $username, $password, $dbname
	 **/
	private $dsn;
	private $driver;
	private $hostname;
	private $username;
	private $password;
	private $dbname;
	//-- End

	/**
	 * @var Driver Class $db
	 **/
	private $db=NULL;
	/**
	 * @var string $table
	 **/
	private $table='';
	/**
	 * @var array $dsnuser ID: Driver yang menggunakan dsn | EN: Driver use dsn
	 **/
	private $dsnuse = ['pdo', 'oci8', 'pgsql', 'mongo'];
	private $failOver = [];
	/**
 	 * __construct
 	 * @param Kecik $app
 	 **/
	public function __construct() {
		$app = Kecik::getInstance();
		$this->app = $app;

		$config = $app->config;
		
		$this->dsn = ($config->get('database.dsn') != '')?$config->get('database.dsn'):'';
		$this->driver = ($config->get('database.driver') != '')?strtolower($config->get('database.driver')):'';
		$this->hostname = ($config->get('database.hostname') != '')?$config->get('database.hostname'):'';
		$this->username = ($config->get('database.username') != '')?$config->get('database.username'):'';
		$this->password = ($config->get('database.password') != '')?$config->get('database.password'):'';
		$this->dbname = ($config->get('database.dbname') != '')?$config->get('database.dbname'):'';
		$this->charset = ($config->get('database.charset') != '')?$config->get('database.charset'):'';

		$this->failOver = ($config->get('database.failover') != '' && is_array($config->get('database.failover')))?$config->get('database.failover'):[];
	}

	/**
	 * connect
	 * @return res id
	 **/
	public function connect() {
		
		if (file_exists(dirname( __FILE__ )."/drivers/".$this->driver.".php")) {
			$this->db = include_once("drivers/".$this->driver.".php");
			$failover = (count($this->failOver) > 0)? TRUE:FALSE;

			$con = FALSE;
			if (!isset($_SESSION['failover']) || empty($_SESSION['failover'])) {
				if (in_array($this->driver, ['sqlite', 'sqlite3']))
					$con = @$this->db->connect($this->dbname, $failover);
				elseif (in_array($this->driver, $this->dsnuse))
					$con = @$this->db->connect($this->dsn, $this->dbname, $this->hostname, $this->username, $this->password, $failover);
				else
					$con = @$this->db->connect($this->dbname, $this->hostname, $this->username, $this->password, $failover, $this->charset);
			}

			if (!$con && count($this->failOver) > 0) {
				$fo = $this->failOver;
				if (isset($_SESSION['failover']) && !empty($_SESSION['failover'])) {
					$fo_id = base64_decode($_SESSION['failover']);
					if (in_array($fo[$fo_id]['driver'], ['sqlite', 'sqlite3']))
						$con = @$this->db->connect($fo[$fo_id]['dbname'], $failover);
					elseif (in_array($fo[$fo_id]['driver'], $this->dsnuse))
						$con = @$this->db->connect($fo[$fo_id]['dsn'], $fo[$fo_id]['dbname'], $fo[$fo_id]['hostname'], $fo[$fo_id]['username'], $fo[$fo_id]['password'], $failover);
					else
						$con = @$this->db->connect($fo[$fo_id]['dbname'], $fo[$fo_id]['hostname'], $fo[$fo_id]['username'], $fo[$fo_id]['password'], $failover);
				} else {
					while(list($id, $fo_config) = each($fo)) {
						if (in_array($fo_config['driver'], ['sqlite', 'sqlite3']))
							$con = @$this->db->connect($fo_config['dbname'], $failover);
						elseif (in_array($fo_config['driver'], $this->dsnuse))
							$con = @$this->db->connect($fo_config['dsn'], $fo_config['dbname'], $fo_config['hostname'], $fo_config['username'], $fo_config['password'], $failover);
						else
							$con = @$this->db->connect($fo_config['dbname'], $fo_config['hostname'], $fo_config['username'], $fo_config['password'], $failover);

						if ($con) {
							$_SESSION['failover'] = base64_encode($id);
							break;
						}
					}

				}

				if (!$con) {
					header('X-Error-Message: Fail Connecting', true, 500);
					die('All Connecting Database Error');
					$this->db = NULL;
				}
				return $this->db;
			} else {
				if (isset($_SESSION['failover']))
					unset($_SESSION['failover']);
			}

			return $this->db;
			
		} else
			throw new \Exception('Database Library: Unknown Driver');
		
		
	}

	/**
	 * exec
	 * @param string $query
	 * @return res
	 **/
	public function exec($query) {
		$this->table = '';
		return $this->db->exec($query);
	}

	/**
	 * fetch
	 * @param res ID: dari exec() | EN: from exec()
	 * @return array object
	 **/
	public function fetch($res) {
		return $this->db->fetch($res);
	}

	/**
	 * affected
	 **/
	public function affected() {
		if (in_array($this->driver, ['sqlite', 'sqlite3','oci8']) )
			echo 'Fungsi tidak support untuk driver '.$this->driver;
		
		return $this->db->affected();
	}

	/**
	 * __get
	 * @param string $table ID: nama table | EN: table name
	 **/
	public function __get($table) {
        $this->table = $table;
        return $this;
    }

    /**
     * insert
     * @param array $data
     * @param string $table
     * @return res
     **/
	public function insert($data, $table='') {
		$table = (!empty($this->table))?$this->table:$table;
		
		return $this->db->insert($table, $data);
	}

	/**
     * update
     * @param array $id primary key
     * @param array $data
     * @param string $table
     * @return res
     **/
	public function update($id, $data, $table='') {
		$table = (!empty($this->table))?$this->table:$table;
		
		return $this->db->update($table, $id, $data);
	}

	/**
     * insert
     * @param array $id primary key
     * @param string $table
     * @return res
     **/
	public function delete($id, $table='') {
		$table = (!empty($this->table))?$this->table:$table;

		return $this->db->delete($table, $id);
	}

	/**
     * insert
     * @param array $condition
     * @param string $table
     * @return res
     **/
	public function find($condition=[], $limit=[], $order_by=[],$table='') {
		$table = (!empty($this->table))?$this->table:$table;

		return $this->db->find($table, $condition, $limit, $order_by);
	}

	public function raw_find($condition=[], $limit=[], $order_by=[],$table='') {
		$table = (!empty($this->table))?$this->table:$table;
		
		return $this->db->raw_find($table, $condition, $limit, $order_by);
	}

	public function fields() {
		$table = (!empty($this->table))?$this->table:$table;

		return $this->db->fields($table);
	}

	public function num_rows() {
		return $this->db->num_rows();
	}

	public function insert_id() {
		return $this->db->insert_id();
	}

	public function set_pk($pk) {
		$this->db->set_pk($pk);
	}

	public function __destruct() {
		unset($this->db);
	}

}
