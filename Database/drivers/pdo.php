<?php

/**
 * Driver PDO
 *
 * @author         Dony Wahyu Isp
 * @copyright      2015 Dony Wahyu Isp
 * @link           http://github.io/database
 * @license        MIT
 * @version        1.1.0
 * @package        PDO
 **/
class Kecik_PDO
{
    
    private $dbcon = NULL;
    
    private $_select = '';
    
    private $driver;
    
    private $_fields = '';
    
    private $_num_rows = 0;
    
    private $_pk = '';
    
    private $_joinFields = array();
    
    private $_raw_res = NULL;
    
    private $_query = '';
    
    private $_raw_callback;
    
    public function __construct()
    {
        
    }
    
    public function connect( $dsn, $dbname = '', $hostname = '', $username = 'root', $password = '', $failover = FALSE,
        $charset = 'UTF8'
    ) {
        $driver       = explode(':', $dsn);
        $this->driver = $driver[ 0 ];
        unset( $driver );
        
        try {
            $this->dbcon = new PDO(
                $dsn, $username, $password, array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'' . $charset . '\'' )
            );
        } catch ( PDOException $e ) {
            if ( $failover === FALSE ) {
                header('X-Error-Message: Fail Connecting', TRUE, 500);
                die( "Failed to connect to $dsn: " . $e->getMessage() );
            }
        }
        
        return $this->dbcon;
    }
    
    public function exec( $sql )
    {
        try {
            $this->_query = $sql;
            $res          = $this->dbcon->query($sql);
        } catch ( PDOException $e ) {
            echo "<strong>Query: " . $sql . "</strong><br />";
            echo 'Query Error: ' . $e->getMessage();
        }
        
        return $res;
    }
    
    public function fetch( $res, $callback = NULL )
    {
        //$res->execute();
        if ( $callback == NULL )
            return $res->fetchAll(PDO::FETCH_OBJ);
        else {
            $callback_is = 0;
            if ( is_callable($callback) )
                $callback_is = 1;
            elseif ( is_array($callback) )
                $callback_is = 2;
            
            $result          = array();
            $this->_num_rows = $res->rowCount();
            $rows            = $res->fetchAll(PDO::FETCH_OBJ);
            foreach ( $rows as $data ) {
                if ( $callback_is == 1 ) {
                    while ( list( $field, $value ) = each($data) ) {
                        $data->$field = $callback($data->$field, $data);
                    }
                } elseif ( $callback_is == 2 ) {
                    reset($callback);
                    while ( list( $field, $func ) = each($callback) ) {
                        if ( isset( $data->$field ) )
                            $data->$field = $func($data->$field, $data);
                    }
                }
                
                if ( count($this->_joinFields) > 0 ) {
                    reset($this->_joinFields);
                    while ( list( $field, $join ) = each($this->_joinFields) ) {
                        
                        if ( isset( $data->$field ) ) {
                            $modelJoin = $this->_joinFields[ $field ][ 0 ];
                            $realField = $this->_joinFields[ $field ][ 1 ];
                            
                            if ( !isset( $data->$modelJoin ) ) {
                                $dataJoin             = new \stdClass;
                                $dataJoin->$realField = $data->$field;
                                $data->$modelJoin     = $dataJoin;
                            } else
                                $data->$modelJoin->$realField = $data->$field;
                            
                            unset( $data->$field );
                        }
                    }
                    unset( $dataJoin );
                    
                    $result[] = (object) $data;
                } else
                    $result[] = (object) $data;
            }
            
            return $result;
        }
    }
    
    public function affected()
    {
        return NULL;
    }
    
    public function __destruct()
    {
        unset( $this->dbcon );
    }
    
    public function insert( $table, $data )
    {
        $table  = ( !empty( $this->table ) ) ? $this->table : $table;
        $fields = $values = array();
        
        while ( list( $field, $value ) = each($data) ) {
            $fields[] = "$field";
            if ( is_array($value) && $value[ 1 ] == FALSE )
                $values[] = $value[ 0 ];
            else {
                $value = addslashes($value);
                if ( gettype($value) == 'string' )
                    $value = "'$value'";
                
                $values[] = $value;
            }
        }
        
        $fields = implode(',', $fields);
        $values = implode(',', $values);
        $query  = "INSERT INTO `$table` ($fields) VALUES ($values)";
        
        return (object) array(
            'query'  => $query,
            'result' => $this->exec($query),
            'id'     => $this->inserts_id()
        );
    }
    
    public function update( $table, $id, $data )
    {
        $fieldsValues = array();
        $and          = array();
        
        while ( list( $pk, $value ) = each($id) ) {
            $pk = explode('.', $pk);
            if ( count($pk) > 1 )
                $pk = "`$pk[0]`.`$pk[1]`";
            else
                $pk = "`$pk[0]`";
            
            if ( is_array($value) && $value[ 1 ] == FALSE )
                $value = $value[ 0 ];
            else {
                $value = addslashes($value);
                if ( gettype($value) == 'string' )
                    $value = "'$value'";
            }
            
            if ( preg_match('/<|>|!=/', $value) )
                $and[] = "$pk$value";
            else
                $and[] = "$pk=$value";
        }
        
        $where = '';
        if ( count($id) > 0 )
            $where = 'WHERE ' . implode(' AND ', $and);
        
        while ( list( $field, $value ) = each($data) ) {
            if ( is_array($value) && $value[ 1 ] == FALSE )
                $fieldsValues[] = "$field=" . $value[ 0 ];
            else {
                $value = addslashes($value);
                if ( gettype($value) == 'string' )
                    $value = "'$value'";
                
                $fieldsValues[] = "$field=$value";
            }
        }
        
        $fieldsValues = implode(',', $fieldsValues);
        $query        = "UPDATE $table SET $fieldsValues $where";
        
        return (object) array(
            'query'  => $query,
            'result' => $this->exec($query),
            'id'     => $id
        );
    }
    
    public function delete( $table, $id )
    {
        $fieldsValues = array();
        $and          = array();
        
        while ( list( $pk, $value ) = each($id) ) {
            $pk = explode('.', $pk);
            if ( count($pk) > 1 )
                $pk = "`$pk[0]`.`$pk[1]`";
            else
                $pk = "`$pk[0]`";
            
            if ( is_array($value) && $value[ 1 ] == FALSE )
                $value = $value[ 0 ];
            else {
                $value = addslashes($value);
                if ( gettype($value) == 'string' )
                    $value = "'$value'";
            }
            
            if ( preg_match('/<|>|!=/', $value) )
                $and[] = "$pk$value";
            else
                $and[] = "$pk=$value";
        }
        
        $where = '';
        if ( count($id) > 0 )
            $where = 'WHERE ' . implode(' AND ', $and);
        
        $query = "DELETE FROM $table $where";
        
        return (object) array(
            'query'  => $query,
            'id'     => $id,
            'result' => $this->exec($query)
        );
    }
    
    public function find( $table, $condition = array(), $limit = array(), $order_by = array() )
    {
        $ret      = array();
        $callback = NULL;
        if ( isset( $condition[ 'callback' ] ) ) {
            $callback = $condition[ 'callback' ];
            unset( $condition[ 'callback' ] );
        }
        
        if ( isset( $condition[ 'join' ] ) && count($condition[ 'join' ]) > 0 ) {
            if ( !isset( $condition[ 'select' ] ) ) $condition[ 'select' ] = array( array( "$table.*" ) );
            
            $this->_joinFields = array();
            while ( list( $id, $join ) = each($condition[ 'join' ]) ) {
                $this->_fields = '';
                $fields        = $this->fields($join[ 1 ]);
                
                while ( list( $id, $field ) = each($fields) ) {
                    $this->_joinFields[ "__$join[1]_$field->name" ] = array( $join[ 1 ], $field->name );
                    $condition[ 'select' ][]                        = array( "$join[1].$field->name", 'as' => "__$join[1]_$field->name" );
                }
            }
            
            reset($condition[ 'join' ]);
        }
        
        $query = QueryHelperPDO::find($this->driver, $table, $condition, $limit, $order_by);
        try {
            $res             = $this->exec($query);
            $this->_num_rows = $res->rowCount();
            $this->_fields   = '';
            $nfields         = $res->columnCount();
            $fields          = array();
            for ( $i = 0; $i < $nfields; $i++ ) {
                $field    = $res->getColumnMeta($i);
                $fields[] = (object) array(
                    'name'  => $field[ 'name' ],
                    'type'  => $field[ 'native_type' ],
                    'size'  => $field[ 'len' ],
                    'table' => $field[ 'table' ]
                );
            }
            $this->_fields = $fields;
            $ret           = $this->fetch($res, $callback);
        } catch ( PDOException $e ) {
            echo $e->getMessage();
        }
        
        return $ret;
    }
    
    public function rawFind( $table, $condition = array(), $limit = array(), $order_by = array() )
    {
        $this->_fields   = NULL;
        $this->_num_rows = 0;
        
        if ( isset( $condition[ 'callback' ] ) ) {
            $this->_raw_callback = $condition[ 'callback' ];
            unset( $condition[ 'callback' ] );
        }
        
        if ( isset( $condition[ 'join' ] ) && count($condition[ 'join' ]) > 0 ) {
            if ( !isset( $condition[ 'select' ] ) ) $condition[ 'select' ] = array( array( "$table.*" ) );
            
            $this->_joinFields = array();
            while ( list( $id, $join ) = each($condition[ 'join' ]) ) {
                $this->_fields = '';
                $fields        = $this->fields($join[ 1 ]);
                
                while ( list( $id, $field ) = each($fields) ) {
                    $this->_joinFields[ "__$join[1]_$field->name" ] = array( $join[ 1 ], $field->name );
                    $condition[ 'select' ][]                        = array( "$join[1].$field->name", 'as' => "__$join[1]_$field->name" );
                }
            }
            
            reset($condition[ 'join' ]);
        }
        
        $query = QueryHelperPDO::find($this->driver, $table, $condition, $limit, $order_by);
        try {
            $this->_raw_res  = $this->exec($query);
            $this->_num_rows = $this->_raw_res->rowCount();
            $this->_fields   = '';
            $nfields         = $this->_raw_res->columnCount();
            $fields          = array();
            for ( $i = 0; $i < $nfields; $i++ ) {
                $field    = $this->_raw_res->getColumnMeta($i);
                $fields[] = (object) array(
                    'name'  => $field[ 'name' ],
                    'type'  => $field[ 'native_type' ],
                    'size'  => $field[ 'len' ],
                    'table' => $field[ 'table' ]
                );
            }
            $this->_fields = $fields;
        } catch ( PDOException $e ) {
            echo $e->getMessage();
        }
        
        return $this;
    }
    
    public function each( \Closure $callable )
    {
        $data = array();
        if ( !empty( $this->_raw_res ) && is_callable($callable) ) {
            $res            = $this->_raw_res;
            $this->_raw_res = NULL;
            
            $callback_is = 0;
            if ( is_callable($this->_raw_callback) )
                $callback_is = 1;
            elseif ( is_array($this->_raw_callback) )
                $callback_is = 2;
            
            $callback = $this->_raw_callback;
            
            $rows = $res->fetchAll(PDO::FETCH_OBJ);
            $data = $rows;
            foreach ( $rows as $row ) {
                if ( $callback_is == 1 ) {
                    while ( list( $field, $value ) = each($row) )
                        $row->$field = $callback($row->$field, $row);
                    
                } elseif ( $callback_is == 2 ) {
                    reset($callback);
                    while ( list( $field, $func ) = each($callback) ) {
                        if ( isset( $row->$field ) )
                            $row->$field = $func($row->$field, $row);
                    }
                }
                
                if ( count($this->_joinFields) > 0 ) {
                    
                    reset($this->_joinFields);
    
                    $dataJoin = new stdclass;
                    
                    while ( list( $field, $join ) = each($this->_joinFields) ) {
                        if ( isset( $row->$field ) ) {
                            $modelJoin = $this->_joinFields[ $field ][ 0 ];
                            $realField = $this->_joinFields[ $field ][ 1 ];
                            
                            if ( !isset( $dataJoin->$realField ) ) $dataJoin = new stdclass;
                            
                            $dataJoin->$realField = $row->$field;
                            unset( $row->$field );
                            
                            if ( !empty( $modelJoin ) && $this->_joinFields ) {
                                $row->$modelJoin = $dataJoin;
                            }
                            
                        }
                    }
                }
                
                $callable($row);
            }
        }
        
        return (object) array(
            'query'  => $this->_query,
            'result' => (object) array(
                'num_rows' => $this->_num_rows,
                'fields'   => $this->_fields,
                'data'     => $data
            )
        );
    }
    
    public function fields( $table )
    {
        if ( empty( $this->_fields ) ) {
            $query = QueryHelperPDO::find($this->dbcon, $table, array(), array( 1 ), array());
            if ( $res = $this->exec($query) ) {
                $nfields = $res->columnCount();
                $fields  = array();
                for ( $i = 0; $i < $nfields; $i++ ) {
                    $field    = $res->getColumnMeta($i);
                    $fields[] = (object) array(
                        'name'  => $field[ 'name' ],
                        'type'  => $field[ 'native_type' ],
                        'size'  => $field[ 'len' ],
                        'table' => $field[ 'table' ]
                    );
                }
                $this->_fields = $fields;
            } else
                echo $this->dbcon->error;
        }
        
        return $this->_fields;
    }
    
    public function numRows()
    {
        return $this->_num_rows;
    }
    
    public function insertsId()
    {
        if ( empty( $this->_pk ) )
            return $this->dbcon->lastInsertId();
        else
            return $this->dbcon->lastInsertId($this->_pk);
    }
    
    public function setPK( $pk )
    {
        $this->_pk = $pk;
    }
    
}

class QueryHelperPDO
{
    
    public static function select( $list )
    {
        $select = array();
        
        if ( is_array($list) && count($list) > 0 ) {
            while ( list( $idx, $selectlist ) = each($list) ) {
                while ( list( $id, $fields ) = each($selectlist) ) {
                    
                    
                    if ( is_int($id) ) {
                        if ( count($selectlist) > 1 )
                            $select[] = '' . $fields . '';
                        else
                            $select[] = $fields;
                        
                    } elseif ( is_string($fields) ) {
                        if ( strtoupper($id) != 'AS' )
                            $select[] = strtoupper($id) . "($fields)";
                        else
                            $select[ count($select) - 1 ] .= " AS $fields";
                    }
                }
            }
            
            $ret = implode(', ', $select) . ' ';
        }
        
        return ( !isset( $ret ) ) ? ' * ' : $ret;
    }
    
    public static function where( $list, $group = 'and', $idx_where = 1, $group_prev = '' )
    {
        $ret      = '';
        $where    = array( 'and' => array(), 'or' => array() );
        $opt      = array( 'and', 'or' );
        $optrow   = array();
        $wherestr = '';
        
        if ( is_array($list) && count($list) > 0 ) {
            while ( list( $idx, $wherelist ) = each($list) ) {
                
                //sub operator
                if ( is_string($idx) && in_array($idx, $opt) ) {
                    $where[ $idx ][] .= '(' . self::where($wherelist, $idx, $idx_where + 1, $idx) . ')';
                    $optrow[] = $idx;
                    //logical
                } elseif ( is_array($wherelist) ) {
                    
                    if ( count($wherelist) == 1 && isset( $wherelist[ 0 ] ) ) {
                        $where[ $group ][] = $wherelist[ 0 ];
                    } else {
                        $id_step = 0;
                        while ( list( $cond, $val ) = each($wherelist) ) {
                            // if Count item 1
                            if ( count($wherelist) == 1 ) {
                                
                                for ( $i = 0; $i <= substr_count($cond, '?'); $i++ ) {
                                    $pos = strpos($cond, '?');
                                    $tmp = substr($cond, 0, $pos);
                                    $tmp .= $val[ $i ];
                                    $cond = $tmp . substr($cond, $pos + 1);
                                }
                                
                                
                                $where[ $group ][] = $cond;
                                // if Count item 2
                            } elseif ( count($wherelist) == 2 ) {
                                
                                if ( is_array($val) ) {
                                    while ( list( $condkey, $condval ) = each($val) ) {
                                        for ( $i = 0; $i <= substr_count($cond, '?'); $i++ ) {
                                            $pos = strpos($cond, '?');
                                            $tmp = substr($cond, 0, $pos);
                                            $tmp .= $condval;
                                            $cond = $tmp . substr($cond, $pos + 1);
                                        }
                                    }
                                } else {
                                    if ( $id_step == 0 ) {
                                        $condfield = '' . $val . '';
                                        $id_step++;
                                        continue;
                                    } else {
                                        $cond = $val;
                                    }
                                }
                                
                                $where[ $group ][] = $condfield . $cond;
                                $condfield         = '';
                                // if Count item 3
                            } elseif ( count($wherelist) == 3 ) {
                                if ( is_array($val) && !is_int($cond) ) {
                                    while ( list( $condkey, $condval ) = each($val) ) {
                                        for ( $i = 0; $i <= substr_count($cond, '?'); $i++ ) {
                                            $pos = strpos($cond, '?');
                                            $tmp = substr($cond, 0, $pos);
                                            $tmp .= $condval;
                                            $cond = $tmp . substr($cond, $pos + 1);
                                        }
                                    }
                                } else {
                                    if ( $id_step == 0 ) {
                                        $condfield = '' . $val . '';
                                        $id_step++;
                                        continue;
                                    } elseif ( $id_step == 1 ) {
                                        if ( in_array($val, [ '=', '!=', '<>', '<', '>', '<=', '>=' ]) )
                                            $condopt = $val;
                                        else {
                                            $condopt = ' ' . strtoupper($val) . ' ';
                                        }
                                        $id_step++;
                                        continue;
                                    } else {
                                        if ( is_array($val) ) {
                                            if ( trim($condopt) == 'BETWEEN' )
                                                $val = implode(' AND ', $val);
                                            elseif ( is_array($val) && $val[ 1 ] == FALSE )
                                                $val = $val[ 0 ];
                                            else
                                                $val = '(' . implode(', ', $val) . ')';
                                        } else {
                                            $val = addslashes($val);
                                            if ( gettype($val) == 'string' )
                                                $val = "'$val'";
                                        }
                                        
                                        $cond = $val;
                                    }
                                }
                                
                                $where[ $group ][] = $condfield . $condopt . $cond;
                                $condfield         = '';
                            }
                        }
                        
                    }
                }
            }
            
            if ( $idx_where == 1 && ( count($where[ 'and' ] > 0) || count($where[ 'or' ] > 0) ) )
                $ret = ' WHERE ';
            
            if ( count($optrow) > 0 ) {
                
                while ( list( $id, $opt ) = each($optrow) ) {
                    $wherestr .= implode(' ' . strtoupper($opt) . ' ', $where[ $opt ]);
                    if ( $id == 0 && $idx_where == 2 ) $wherestr .= ' ' . strtoupper($group_prev) . ' ';
                }
                
                $ret .= $wherestr;
            } else
                $ret .= implode(' ' . strtoupper($group) . ' ', $where[ $group ]);//.$wherestr;
        }
        
        return $ret;
    }
    
    public static function group_by( $list )
    {
        $ret      = '';
        $group_by = array();
        
        if ( is_array($list) && count($list) > 0 ) {
            while ( list( $idx, $group_bylist ) = each($list) ) {
                while ( list( $id, $fields ) = each($group_bylist) ) {
                    
                    if ( is_int($id) ) {
                        if ( count($group_bylist) > 1 ) {
                            $fields = explode('.', $fields);
                            if ( count($fields) > 1 )
                                $fields = "$fields[0].$fields[1]";
                            else
                                $fields = "$fields[0]";
                            
                            $group_by[] = $fields;
                        } else
                            $group_by[] = $fields;
                        
                    }
                    
                }
            }
            
            $ret = ' GROUP BY ' . implode(', ', $group_by) . ' ';
        }
        
        return $ret;
    }
    
    public static function join( $table, $list )
    {
        $ret  = '';
        $join = array();
        if ( is_array($list) && count($list) > 0 ) {
            while ( list( $idx, $joinlist ) = each($list) ) {
                if ( count($joinlist) == 2 )
                    $join[] = strtoupper($joinlist[ 0 ]) . ' JOIN ' . $joinlist[ 1 ];
                elseif ( count($joinlist) == 3 ) {
                    if ( is_array($joinlist[ 2 ]) && count($joinlist[ 2 ]) == 2 ) {
                        $on1 = $joinlist[ 2 ][ 0 ];
                        $on2 = $joinlist[ 2 ][ 1 ];
                        
                        if ( strpos($on1, '.') > 1 || strpos($on2, '.') ) {
                            
                            if ( strpos($on1, '.') === FALSE )
                                $on1 = "$joinlist[1].$on1";
                            
                            if ( strpos($on2, '.') === FALSE )
                                $on2 = "$joinlist[1].$on2";
                            
                            $join[] = strtoupper($joinlist[ 0 ]) . " JOIN $joinlist[1] ON $on1 = $on2";
                        } else
                            $join[] = strtoupper(
                                    $joinlist[ 0 ]
                                ) . " JOIN $joinlist[1] ON $joinlist[1].$on1 = $table.$on2";
                    } else {
                        $join[] = strtoupper(
                                $joinlist[ 0 ]
                            ) . " JOIN $joinlist[1] ON $joinlist[1].$joinlist[2] = $table.$joinlist[2]";
                    }
                }
            }
            
            $ret = ' ' . implode(' ', $join);
        }
        
        return $ret;
    }
    
    public static function union( $list )
    {
        $ret = '';
        
        if ( is_array($list) && count($list) > 0 ) {
            $ret = ' UNION ' . implode(', ', $list);
        }
        
        return $ret;
    }
    
    public static function find( $driver, $table, $filter = array(), $lmt = array(), $odr_by = array() )
    {
        $select   = '';
        $from     = "FROM $table";
        $where    = '';
        $group_by = '';
        $limit    = '';
        $order_by = '';
        $union    = '';
        
        if ( is_array($filter) && count($filter) > 0 ) {
            
            while ( list( $syntax, $query ) = each($filter) ) {
                $syntax = strtoupper($syntax);
                
                switch ( $syntax ) {
                    case 'SELECT':
                        $select .= self::select($query);
                        break;
                    
                    case 'WHERE':
                        $where = self::where($query);
                        break;
                    
                    case 'GROUP BY':
                        $group_by = self::group_by($query);
                        break;
                    
                    case 'JOIN':
                        $from .= self::join($table, $query);
                        break;
                    
                    case 'UNION':
                        $union = self::union($query);
                        break;
                    
                    default:
                        # code...
                        break;
                }
            }
            
        }
        
        if ( is_array($lmt) && count($lmt) > 0 ) {
            if ( trim(strtolower($driver)) != 'oci' ) {
                if ( !isset( $lmt[ 1 ] ) )
                    $limit = " LIMIT $lmt[0]";
                else
                    $limit = " LIMIT $lmt[1], $lmt[0]";
            } else {
                if ( !isset( $lmt[ 1 ] ) ) {
                    if ( empty( $where ) ) $where = ' WHERE ';
                    $where .= " ROWNUM <= $lmt[0]";
                    //$limit = " FETCH FIRST $lmt[0] ROWS ONLY";
                } else {
                    $limit
                        = "SELECT * FROM (SELECT a.*, ROWNUM AS my_rnum
		                           FROM (:sql:) a
		                           WHERE ROWNUM <= $lmt[0] + $lmt[1])
		            		  WHERE my_rnum > $lmt[0]";
                }
            }
        }
        
        if ( is_array($odr_by) && count($odr_by) > 0 ) {
            $ord = array( 'asc' => array(), 'desc' => array() );
            while ( list( $sort, $fields ) = each($odr_by) ) {
                if ( strtoupper($sort) == 'ASC' ) {
                    $ord[ 'asc' ][] = implode(', ', $fields) . ' ASC';
                } elseif ( strtoupper($sort) == 'DESC' ) {
                    $ord[ 'desc' ][] = implode(', ', $fields) . ' DESC';
                }
                
            }
            
            if ( count($ord[ 'asc' ]) > 0 || count($ord[ 'desc' ]) ) {
                $order = array();
                if ( count($ord[ 'asc' ]) > 0 )
                    $order[] .= implode(', ', $ord[ 'asc' ]) . ' ';
                if ( count($ord[ 'desc' ]) > 0 )
                    $order[] .= implode(', ', $ord[ 'desc' ]) . ' ';
                $order = implode(', ', $order);
            } else
                $order = '';
            
            $order_by = ' ORDER BY ' . $order;
        }
        
        $select = ( empty( $select ) ) ? '* ' : $select;
        if ( trim(strtolower($driver)) != 'oci' ) {
            $sql = 'SELECT ' . $select . $from . $where . $group_by . $limit . $order_by . $union;
        } else {
            $sql = 'SELECT ' . $select . $from . $where . $group_by . $order_by . $union;
            if ( !empty( $limit ) )
                $sql = str_replace(':sql:', $sql, $limit);
        }
        
        return $sql;
    }
    
}

return new Kecik_PDO();
