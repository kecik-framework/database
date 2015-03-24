<?php
class Kecik_PostgreSQL {
	private $dbcon=NULL;

	private $_select = '';

	public function __construct() {

	}

	public function connect($dbname, $hostname='localhost', $username='root', $password='') {
		$hostname = (empty($hostname))?'':"host=$hostname";
		$username = (empty($username))?'':"user=$username";
		$password = (empty($password))?'':"password=$password";
		$dbname   = (empty($dbname))?'':"dbname=$dbname";
		
		$conn_string = "$hostname port=5432 $dbname $user $password options='--client_encoding=UTF8'"
		$this->dbcon = @pg_connect($conn_string);

		if ( !$this->dbcon ) {
		    header('X-Error-Message: Fail Connecting', true, 500);
		    die("Failed to connect to PostgreSQL");
		}

		return $this->dbcon;
	}

	public function exec($sql) {
		$res = pg_query($this->dbcon, $sql);
		if (!$res)
			echo 'Query Error: '.pg_last_error($this->dbcon);

		return $res;
	}

	public function fetch($res) {
		$result = array();
		while( $data = pg_fetch_object($res) ) {
			$result[] = $data;
		}

		return $result;
	}

	public function affected() {
        return pg_affected_rows($this->dbcon);
    }

	public function __destruct() {
		pg_close($this->dbcon);
	}

	public function insert($table, $data) {
		$table = (!empty($this->table))?$this->table:$table;
		$fields = $values = array();

		while (list($field, $value) = each($data)) {
			$fields[] = "`$field`";
			$values[] = "'".mysqli_real_escape_string($this->dbcon, $value)."'";
		}

		$fields = implode(',', $fields);
		$values = implode(',', $values);
		$query = "INSERT INTO `$table` ($fields) VALUES ($values)";

		return $this->exec($query);
	}

	public function update($table, $id, $data) {
		$fieldsValues = array();
		$and = array();

		while(list($pk, $value) = each($id)) {
			$value = mysqli_real_escape_string($this->dbcon, $value);
			if (preg_match('/<|>|!=/', $value))
				$and[] = "`$pk`$value";
			else
				$and[] = "`$pk`='$value'";
		}

		$where = '';
		if (count($id) > 0)
			$where = 'WHERE '.implode(' AND ', $and);

		while (list($field, $value) = each($data)) {
			$fieldsValues[] = "`$field`='".mysqli_real_escape_string($this->dbcon, $value)."'";
		}

		$fieldsValues = implode(',', $fieldsValues);
		$query = "UPDATE `$table` SET $fieldsValues $where";
		return $this->exec($query);
	}

	public function delete($table, $id) {
		$fieldsValues = array();
		$and = array();

		while(list($pk, $value) = each($id)) {
			$value = mysqli_real_escape_string($this->dbcon, $value);
			if (preg_match('/<|>|!=/', $value))
				$and[] = "`$pk`$value";
			else
				$and[] = "`$pk`='$value'";
		}

		$where = '';
		if (count($id) > 0)
			$where = 'WHERE '.implode(' AND ', $and);

		$query = "DELETE FROM `$table` $where";
		return $this->exec($query);
	}

	public function find($table, $condition='') {
		$ret = array();
		$query = "SELECT ";
		$query .= (empty($this->_select))?'* ':$this->_select;
		$query .="FROM $table ";
		if ($res = $this->exec($query))
			$ret = $this->fetch($res);
		else
			echo pg_last_error($this->dbcon);
		
		return $ret;
	}
}

return new Kecik_PostgreSQL;