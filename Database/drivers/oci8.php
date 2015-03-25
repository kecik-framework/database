<?php
class Kecik_Oci8 {
	private $dbcon=NULL;

	private $_select = '';

	private $username;

	public function __construct() {

	}

	public function connect($dbname, $hostname='localhost', $username='root', $password='') {
		$connection_string = "$hostname/$dbname";
		$this->dbcon = @oci_connect(
		    $username,
		    $password,
		    $connection_string
		);

		if ( !$this->dbcon ) {
		    header('X-Error-Message: Fail Connecting', true, 500);
		    $e = oci_error();
    		trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
		}

		$this->username = $username;
		return $this->dbcon;
	}

	public function exec($sql) {
		
		$stid = oci_parse($this->dbcon, $sql);
		if (!oci_execute($stid)) {
			$e = oci_error();
    		trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
		}

		return $stid;
	}

	public function fetch($stid) {
		$result = array();
		while (($data = oci_fetch_object($stid)) != false) {
			$result[] = $data;
		}

		return $result;
	}

	public function affected() {
        return NULL;
    }

	public function __destruct() {
		//oci_free_statement($stid);
		oci_close($this->dbcon);
	}

	public function insert($table, $data) {
		$table = (!empty($this->table))?$this->table:$table;
		$fields = $values = array();

		while (list($field, $value) = each($data)) {
			$fields[] = $field;
			$values[] = "'".$value."'";
		}

		$fields = implode(',', $fields);
		$values = implode(',', $values);
		$query = "INSERT INTO \"".strtoupper($this->username)."\".\"".strtoupper($table)."\" ($fields) VALUES ($values)";

		return $this->exec($query);
	}

	public function update($table, $id, $data) {
		$fieldsValues = array();
		$and = array();

		while(list($pk, $value) = each($id)) {
			$value = $value;
			if (preg_match('/<|>|!=/', $value))
				$and[] = "$pk$value";
			else
				$and[] = "$pk='$value'";
		}

		$where = '';
		if (count($id) > 0)
			$where = 'WHERE '.implode(' AND ', $and);

		while (list($field, $value) = each($data)) {
			$fieldsValues[] = "$field='".$value."'";
		}

		$fieldsValues = implode(',', $fieldsValues);
		$query = "UPDATE \"".strtoupper($this->username)."\".\"".strtoupper($table)."\" SET $fieldsValues $where";
		return $this->exec($query);
	}

	public function delete($table, $id) {
		$fieldsValues = array();
		$and = array();

		while(list($pk, $value) = each($id)) {
			$value = $value;
			if (preg_match('/<|>|!=/', $value))
				$and[] = "$pk$value";
			else
				$and[] = "$pk='$value'";
		}

		$where = '';
		if (count($id) > 0)
			$where = 'WHERE '.implode(' AND ', $and);

		$query = "DELETE FROM \"".strtoupper($this->username)."\".\"".strtoupper($table)."\" $where";
		return $this->exec($query);
	}

	public function find($table, $condition='') {
		$ret = array();
		$query = "SELECT ";
		$query .= (empty($this->_select))?'* ':$this->_select;
		$query .="FROM \"".strtoupper($this->username)."\".\"".strtoupper($table)."\" ";
		if ($res = $this->exec($query))
			$ret = $this->fetch($res);
		else {
			$e = oci_error();
    		trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
		}
		
		return $ret;
	}
}

return new Kecik_Oci8();