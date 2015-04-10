<?php
/**
 * Cookie - Library untuk framework kecik, library ini khusus untuk membantu masalah Cookie 
 *
 * @author 		Dony Wahyu Isp
 * @copyright 	2015 Dony Wahyu Isp
 * @link 		http://github.io/kecik_cookie
 * @license		MIT
 * @version 	1.0.1-alpha
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

	/**
 	 * __construct
 	 * @param Kecik $app
 	 **/
	public function __construct(Kecik $app) {
		$this->app = $app;

		$config = $app->config;
		
		$this->dsn = (!empty($config->get('database.dsn')))?$config->get('database.dsn'):'';
		$this->driver = (!empty($config->get('database.driver')))?strtolower($config->get('database.driver')):'';
		$this->hostname = (!empty($config->get('database.hostname')))?$config->get('database.hostname'):'';
		$this->username = (!empty($config->get('database.username')))?$config->get('database.username'):'';
		$this->password = (!empty($config->get('database.password')))?$config->get('database.password'):'';
		$this->dbname = (!empty($config->get('database.dbname')))?$config->get('database.dbname'):'';
	}

	/**
	 * connect
	 * @return res id
	 **/
	public function connect() {
		

		if (file_exists(dirname( __FILE__ )."/drivers/".$this->driver.".php")) {
			$this->db = include_once("drivers/".$this->driver.".php");

			if (in_array($this->driver, array('sqlite', 'sqlite3')))
				$this->db->connect($this->dbname);
			elseif (in_array($this->driver, $this->dsnuse))
				$this->db->connect($this->dsn, $this->dbname, $this->hostname, $this->username, $this->password);
			else
				$this->db->connect($this->dbname, $this->hostname, $this->username, $this->password);
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
	public function find($condition=array(), $limit=array(), $order_by=array(),$table='') {
		$table = (!empty($this->table))?$this->table:$table;

		return $this->db->find($table, $condition, $limit, $order_by);
	}


	public function __destruct() {
		unset($this->db);
	}
}