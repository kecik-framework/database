<?php
class Kecik_Mongo {
	private $dbcon=NULL;

	private $db;
	private $_select = '';

	private $lastSQL = '';
	public function __construct() {

	}

	public function connect($dbname, $hostname='mongodb://localhost:27017', $username='', $password='') {
		$con_string='';
		if (substr(strtolower($hostname), 0, 10) != 'mongodb://') $con_string= 'mongodb://'.$hostname;
		if (strpos($hostname, ':')<=0) $con_string.=':27017';

		if (!empty($username) || !empty($password))
			$con_string="mongodb://$username:$password@$hostname";
		
		$this->dbcon = new MongoClient($con_string);
		
		if ( !$this->dbcon ) {
		    header('X-Error-Message: Fail Connecting', true, 500);
		    die("Failed to connect to MongoDB: ");
		}


		$this->db = $this->dbcon->selectDB($dbname);
		return $this->dbcon;
	}

	public function exec($sql) {
		$this->lastSQL = $sql;
		$res = $this->db->execute($sql);
		if (!$res)
			echo 'Query Error '.$this->db->lastError();

		return $res;
	}

	public function fetch($res) {
		$res = $this->db->execute($this->lastSQL.'.toArray()');
		$this->lastSQL = '';
		$result = array();
		foreach ($res['retval'] as $data) {
			$result[] = (object) $data;
		}
		return $result;
	}

	public function affected() {
        return mysqli_affected_rows($this->dbcon);
    }

	public function __destruct() {
		$this->dbcon->close();
	}

	public function insert($table, $data) {
		return $this->db->$table->insert($data);
	}

	public function update($table, $id, $data) {
		return $this->db->$table->update($id, array('$set'=>$data));
	}

	public function delete($table, $id) {
		return $this->db->$table->remove($id);
	}

	public function find($table, $condition='') {
		$res = $this->exec("db.$table.find()");
		return $this->fetch($res);
	}
}

return new Kecik_Mongo();