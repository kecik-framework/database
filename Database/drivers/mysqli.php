<?php
class Kecik_MySqli {
	private $dbcon=NULL;

	private $_select = '';

	public function __construct() {

	}

	public function connect($dbname, $hostname='localhost', $username='root', $password='') {
		$this->dbcon = @mysqli_connect(
		    $hostname,
		    $username,
		    $password,
		    $dbname
		);

		if ( mysqli_connect_errno($this->dbcon) ) {
		    header('X-Error-Message: Fail Connecting', true, 500);
		    die("Failed to connect to MySQL: " . mysqli_connect_error());
		}

		return $this->dbcon;
	}

	public function exec($sql) {
		$res = mysqli_query($this->dbcon, $sql);
		if (!$res)
			echo 'Query Error: '.mysqli_error($this->dbcon);

		return $res;
	}

	public function fetch($res) {
		$result = array();
		while( $data = mysqli_fetch_object($res) ) {
			$result[] = $data;
		}

		return $result;
	}

	public function affected() {
        return mysqli_affected_rows($this->dbcon);
    }

	public function __destruct() {
		mysqli_close($this->dbcon);
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
			echo mysqli_error($this->dbcon);
		
		return $ret;
	}
}

return new Kecik_MySqli();