<?php
class Kecik_MsSQL {
	private $dbcon=NULL;

	private $_select = '';

	public function __construct() {

	}

	public function connect($dbname, $hostname='localhost', $username='root', $password='', $failover=FALSE) {
		
		$this->dbcon = sqlsrv_connect(
		    $hostname,
		    $username,
		    $password
		);
		
		if ($failover === FALSE) {
			if ( !$this->dbcon ) {
			    header('X-Error-Message: Fail Connecting', true, 500);
			    die("Failed to connect to MsSQL ");
			}
		}
		
		mssql_select_db($dname, $this->dbcon);

		return $this->dbcon;
	}

	public function exec($sql) {
		$res = mssql_query($sql, $this->dbcon);
		if (!$res)
			echo 'Query Error';

		return $res;
	}

	public function fetch($res) {
		$result = array();
		while( $data = mssql_fetch_object($res) ) {
			$result[] = $data;
		}

		return $result;
	}

	public function affected() {
        return mssql_rows_affected($this->dbcon);
    }

	public function __destruct() {
		mssql_close($this->dbcon);
	}

	public function insert($table, $data) {
		$table = (!empty($this->table))?$this->table:$table;
		$fields = $values = array();

		while (list($field, $value) = each($data)) {
			$fields[] = "`$field`";
			$values[] = "'".$value."'";
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
			$value = $value;
			if (preg_match('/<|>|!=/', $value))
				$and[] = "`$pk`$value";
			else
				$and[] = "`$pk`='$value'";
		}

		$where = '';
		if (count($id) > 0)
			$where = 'WHERE '.implode(' AND ', $and);

		while (list($field, $value) = each($data)) {
			$fieldsValues[] = "`$field`='".$value."'";
		}

		$fieldsValues = implode(',', $fieldsValues);
		$query = "UPDATE `$table` SET $fieldsValues $where";
		return $this->exec($query);
	}

	public function delete($table, $id) {
		$fieldsValues = array();
		$and = array();

		while(list($pk, $value) = each($id)) {
			$value = $value;
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
			echo 'Query Error';
		
		return $ret;
	}
}

return new Kecik_MsSQL();