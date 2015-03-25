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
	private $app;

	private $driver;
	private $hostname;
	private $username;
	private $password;
	private $dbname;

	private $db=NULL;
	private $table='';
	private $data=array();

	/**
 	 * __construct
 	 * @param Kecik $app
 	 **/
	public function __construct(Kecik $app) {
		$this->app = $app;

		$config = $app->config;
		$this->driver = (!empty($config->get('database.driver')))?strtolower($config->get('database.driver')):'';
		$this->hostname = (!empty($config->get('database.hostname')))?$config->get('database.hostname'):'';
		$this->username = (!empty($config->get('database.username')))?$config->get('database.username'):'';
		$this->password = (!empty($config->get('database.password')))?$config->get('database.password'):'';
		$this->dbname = (!empty($config->get('database.dbname')))?$config->get('database.dbname'):'';
	}

	public function connect($dbname='', $driver='mysqli', $hostname='localhost', $username='root', $password='') {
		$this->driver = (!empty($driver) && $driver != 'mysqli')?strtolower($driver):strtolower($this->driver);
		$this->hostname = (!empty($host) && $hostname != 'localhost')?$hostname:$this->hostname;
		$this->username = (!empty($username) && $username != 'root')?$username:$this->username;
		$this->password = (!empty($password))?$password:$this->password;
		$this->dbname = (!empty($dbname))?$dbname:$this->dbname;
		
		if (file_exists(dirname( __FILE__ )."/drivers/".$this->driver.".php")) {
			$this->db = include_once("drivers/".$this->driver.".php");

			if (in_array($this->driver, array('sqlite', 'sqlite3')))
				$this->db->connect($this->dbname);
			else
				$this->db->connect($this->dbname, $this->hostname, $this->username, $this->password);
		}
		
		return $this->db;
	}

	public function exec($query) {
		$this->table = '';
		return $this->db->exec($query);
	}

	public function fetch($res) {
		return $this->db->fetch($res);
	}

	public function affected() {
		if (in_array($this->driver, array('sqlite', 'sqlite3','oci8')) )
			echo 'Fungsi tidak support untuk driver '.$this->driver;
		
		return $this->db->affected();
	}

	public function __get($table) {
        $this->table = $table;
        return $this;
    }

	public function insert($data, $table='') {
		$table = (!empty($this->table))?$this->table:$table;
		
		return $this->db->insert($table, $data);
	}

	public function update($id, $data, $table='') {
		$table = (!empty($this->table))?$this->table:$table;
		
		return $this->db->update($table, $id, $data);
	}

	public function delete($id, $table='') {
		$table = (!empty($this->table))?$this->table:$table;

		return $this->db->delete($table, $id);
	}

	public function find($condition='', $table='') {
		$table = (!empty($this->table))?$this->table:$table;

		return $this->db->find($table, $condition);
	}


	public function __destruct() {
		unset($this->db);
	}
}