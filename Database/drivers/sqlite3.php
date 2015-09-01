<?php
/**
 * Driver SQLite3
 *
 * @author 		Dony Wahyu Isp
 * @copyright 	2015 Dony Wahyu Isp
 * @link 		http://github.io/database
 * @license		MIT
 * @version 	1.0.3
 * @package		SQLite3
 **/
class Kecik_SQLite3 extends SQLite3 {
	private $dbcon=NULL;

	private $_select = '';

	private $_fields = '';

	private $_num_rows = 0;

	private $_pk = '';

	private $_insert_id = null;

	public function __construct() {

	}

	public function connect($dbname, $failover=FALSE) {
		$mode = '0666';
		$this->dbcon = new SQLite3($dbname);

		if ($failover === FALSE) {
			if ( $this->dbcon->lastErrorMsg() ) {
			    header('X-Error-Message: Fail Connecting', true, 500);
			    die("Failed to connect to Sqlite: " . $this->dbcon->lastErrorMsg());
			}
		}
		
		return $this->dbcon;
	}

	public function exec($sql) {
		if (strtolower( substr($sql, 0,6) ) == 'select') {
			$res = $this->dbcon->query($sql);
			if ($res)
				echo 'Query Error: '.$this->dbcon->lastErrorMsg();
		} else {
			$res = $this->dbcon->exec($sql);
			if ($res)
				echo 'Query Error: '.$this->dbcon->lastErrorMsg();
		}

		return $res;
	}

	public function fetch($res) {
		$result = array();
		$this->_num_rows = $res->numRows();
		while ($data = $res->fetchArray())
		    $result[] = (object) $data;

		return $result;
	}

	public function affected() {
        return NULL;
    }

	public function __destruct() {
		sqlite_close($this->dbcon);
	}

	public function insert($table, $data) {
		$table = (!empty($this->table))?$this->table:$table;
		$fields = $values = array();

		while (list($field, $value) = each($data)) {
			$fields[] = "`$field`";
			$values[] = "'".$this->dbcon->escapeString($value)."'";
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
			$value = $this->dbcon->escapeString($value);
			if (preg_match('/<|>|!=/', $value))
				$and[] = "`$pk`$value";
			else
				$and[] = "`$pk`='$value'";
		}

		$where = '';
		if (count($id) > 0)
			$where = 'WHERE '.implode(' AND ', $and);

		while (list($field, $value) = each($data)) {
			$fieldsValues[] = "`$field`='".$this->dbcon->escapeString($value)."'";
		}

		$fieldsValues = implode(',', $fieldsValues);
		$query = "UPDATE `$table` SET $fieldsValues $where";
		return $this->exec($query);
	}

	public function delete($table, $id) {
		$fieldsValues = array();
		$and = array();

		while(list($pk, $value) = each($id)) {
			$value = $this->dbcon->escapeString($value);
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
			$this->dbcon->lastErrorMsg();
		
		return $ret;
	}

	public function fields($table) {
		return array();
	}

	public function num_rows() {
		return $this->_num_rows;
	}

	public function insert_id() {
		return $this->dbcon->lastInsertRowid();
	}

	public function set_pk($pk) {
		$this->_pk = $pk;
	}
}

return new Kecik_Sqlite3;