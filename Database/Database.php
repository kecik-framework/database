<?php
/**
 * Cookie - Library untuk framework kecik, library ini khusus untuk membantu masalah Database
 *
 * @author         Dony Wahyu Isp
 * @copyright      2015 Dony Wahyu Isp
 * @link           http://github.io/database
 * @license        MIT
 * @version        1.1.0
 * @package        Kecik\Database
 **/
namespace Kecik;

class DBConfig
{
    public $dsn;
    public $driver;
    public $hostname;
    public $username;
    public $password;
    public $dbname;
    public $charset;
    
    private $failOver = array();
    
    /*public function __construct( $driver, $hostname = NULL, $username = NULL, $password = NULL, $dbname = NULL,
        $charset = NULL, $failOver = NULL
    ) {
        if ( !is_null($hostname) && !is_null($username) && !is_null($password) && !is_null($dbname) ) {
            $this->driver   = $driver;
            $this->hostname = $hostname;
            $this->username = $username;
            $this->password = $password;
            $this->dbname   = $dbname;
        } else {
            $this->dsn = $driver;
        }
        
        $this->chartset = $charset;
        $this->failOver = ( !is_null($failOver) ) ? $failOver : $this->failOver;
    }*/
}

/**
 * Database
 * @package       Kecik\Database
 * @author        Dony Wahyu Isp
 * @since         1.0.1-alpha
 **/
class Database
{
    /**
     * @var Kecik Class $app
     **/
    private $app;
    
    /**
     * @var string $dsn , $driver, $hostname, $username, $password, $dbname
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
    private $db = NULL;
    /**
     * @var string $table
     **/
    private $table = '';
    /**
     * @var array $dsnuser ID: Driver yang menggunakan dsn | EN: Driver use dsn
     **/
    private $dsnuse   = array( 'pdo', 'oci8', 'pgsql', 'mongo' );
    private $failOver = array();
    
    /**
     * Database constructor.
     */
    public function __construct( $config = NULL )
    {
        $app       = Kecik::getInstance();
        $this->app = $app;
        
        if ( is_callable($config) ) {
            $config = $config(new DBConfig());
            
            $this->dsn      = ( !empty( $config->dsn ) ) ? $config->dsn : '';
            $this->driver   = ( !empty( $config->driver ) ) ? strtolower($config->driver) : '';
            $this->hostname = ( !empty( $config->hostname ) ) ? $config->hostname : '';
            $this->username = ( !empty( $config->username ) ) ? $config->username : '';
            $this->password = ( !empty( $config->password ) ) ? $config->password : '';
            $this->dbname   = ( !empty( $config->dbname ) ) ? $config->dbname : '';
            $this->charset  = ( !empty( $config->charset ) ) ? $config->charset : '';
            
            $this->failOver = ( !empty( $config->failover ) && is_array(
                    $config->failover
                ) ) ? $config->failover : array();
            
        } else {
            
            $config = $app->config;
            
            $this->dsn      = ( $config->get('database.dsn') != '' ) ? $config->get('database.dsn') : '';
            $this->driver   = ( $config->get('database.driver') != '' ) ? strtolower(
                $config->get('database.driver')
            ) : '';
            $this->hostname = ( $config->get('database.hostname') != '' ) ? $config->get('database.hostname') : '';
            $this->username = ( $config->get('database.username') != '' ) ? $config->get('database.username') : '';
            $this->password = ( $config->get('database.password') != '' ) ? $config->get('database.password') : '';
            $this->dbname   = ( $config->get('database.dbname') != '' ) ? $config->get('database.dbname') : '';
            $this->charset  = ( $config->get('database.charset') != '' ) ? $config->get('database.charset') : '';
            
            $this->failOver = ( $config->get('database.failover') != '' && is_array(
                    $config->get('database.failover')
                ) ) ? $config->get('database.failover') : array();
            
        }
    }
    
    /**
     * @return Driver|mixed|null
     * @throws \Exception
     */
    public function connect()
    {
        
        if ( file_exists(dirname(__FILE__) . "/drivers/" . $this->driver . ".php") ) {
            $this->db = include_once( "drivers/" . $this->driver . ".php" );
            $failover = ( count($this->failOver) > 0 ) ? TRUE : FALSE;
            $con      = FALSE;
            
            if ( !isset( $_SESSION[ 'failover' ] ) || empty( $_SESSION[ 'failover' ] ) ) {
                
                if ( in_array($this->driver, array( 'sqlite', 'sqlite3' )) ) {
                    $con = @$this->db->connect($this->dbname, $failover);
                } elseif ( in_array($this->driver, $this->dsnuse) ) {
                    $con = @$this->db->connect(
                        $this->dsn, $this->dbname, $this->hostname, $this->username, $this->password, $failover
                    );
                } else {
                    $con = @$this->db->connect(
                        $this->dbname, $this->hostname, $this->username, $this->password, $failover, $this->charset
                    );
                }
                
            }
            
            if ( !$con && count($this->failOver) > 0 ) {
                $fo = $this->failOver;
                
                if ( isset( $_SESSION[ 'failover' ] ) && !empty( $_SESSION[ 'failover' ] ) ) {
                    $fo_id = base64_decode($_SESSION[ 'failover' ]);
                    
                    if ( in_array($fo[ $fo_id ][ 'driver' ], array( 'sqlite', 'sqlite3' )) ) {
                        $con = @$this->db->connect($fo[ $fo_id ][ 'dbname' ], $failover);
                    } elseif ( in_array($fo[ $fo_id ][ 'driver' ], $this->dsnuse) ) {
                        $con = @$this->db->connect(
                            $fo[ $fo_id ][ 'dsn' ], $fo[ $fo_id ][ 'dbname' ], $fo[ $fo_id ][ 'hostname' ],
                            $fo[ $fo_id ][ 'username' ], $fo[ $fo_id ][ 'password' ], $failover
                        );
                    } else {
                        $con = @$this->db->connect(
                            $fo[ $fo_id ][ 'dbname' ], $fo[ $fo_id ][ 'hostname' ], $fo[ $fo_id ][ 'username' ],
                            $fo[ $fo_id ][ 'password' ], $failover
                        );
                    }
                    
                } else {
                    
                    while ( list( $id, $fo_config ) = each($fo) ) {
                        
                        if ( in_array($fo_config[ 'driver' ], array( 'sqlite', 'sqlite3' )) ) {
                            $con = @$this->db->connect($fo_config[ 'dbname' ], $failover);
                        } elseif ( in_array($fo_config[ 'driver' ], $this->dsnuse) ) {
                            $con = @$this->db->connect(
                                $fo_config[ 'dsn' ], $fo_config[ 'dbname' ], $fo_config[ 'hostname' ],
                                $fo_config[ 'username' ], $fo_config[ 'password' ], $failover
                            );
                        } else {
                            $con = @$this->db->connect(
                                $fo_config[ 'dbname' ], $fo_config[ 'hostname' ], $fo_config[ 'username' ],
                                $fo_config[ 'password' ], $failover
                            );
                        }
                        
                        if ( $con ) {
                            $_SESSION[ 'failover' ] = base64_encode($id);
                            break;
                        }
                        
                    }
                    
                }
                
                if ( !$con ) {
                    header('X-Error-Message: Fail Connecting', TRUE, 500);
                    die( 'All Connecting Database Error' );
                    $this->db = NULL;
                }
                
                return $this->db;
            } else {
                
                if ( isset( $_SESSION[ 'failover' ] ) ) {
                    unset( $_SESSION[ 'failover' ] );
                }
                
            }
            
            return $this->db;
        } else {
            throw new \Exception('Database Library: Unknown Driver');
        }
        
    }
    
    /**
     * exec
     *
     * @param string $query
     *
     * @return res
     **/
    public function exec( $query )
    {
        $this->table = '';
        
        return $this->db->exec($query);
    }
    
    /**
     * fetch
     *
     * @param res ID: dari exec() | EN: from exec()
     *
     * @return array object
     **/
    public function fetch( $res )
    {
        return $this->db->fetch($res);
    }
    
    /**
     * affected
     **/
    public function affected()
    {
        if ( in_array($this->driver, array( 'sqlite', 'sqlite3', 'oci8' )) ) {
            echo 'Fungsi tidak support untuk driver ' . $this->driver;
        }
        
        return $this->db->affected();
    }
    
    /**
     * @param $table
     *
     * @return $this
     */
    public function __get( $table )
    {
        $this->table = $table;
        
        return $this;
    }
    
    /**
     * insert
     *
     * @param array  $data
     * @param string $table
     *
     * @return res
     **/
    public function insert( $data, $table = '' )
    {
        $table = ( !empty( $this->table ) ) ? $this->table : $table;
        
        return $this->db->insert($table, $data);
    }
    
    /**
     * update
     *
     * @param array  $id primary key
     * @param array  $data
     * @param string $table
     *
     * @return res
     **/
    public function update( $id, $data, $table = '' )
    {
        $table = ( !empty( $this->table ) ) ? $this->table : $table;
        
        return $this->db->update($table, $id, $data);
    }
    
    /**
     * insert
     *
     * @param array  $id primary key
     * @param string $table
     *
     * @return res
     **/
    public function delete( $id, $table = '' )
    {
        $table = ( !empty( $this->table ) ) ? $this->table : $table;
        
        return $this->db->delete($table, $id);
    }
    
    /**
     * @param array  $condition
     * @param array  $limit
     * @param array  $order_by
     * @param string $table
     *
     * @return mixed
     */
    public function find( $condition = array(), $limit = array(), $order_by = array(), $table = '' )
    {
        $table = ( !empty( $this->table ) ) ? $this->table : $table;
        
        return $this->db->find($table, $condition, $limit, $order_by);
    }
    
    /**
     * @param array  $condition
     * @param array  $limit
     * @param array  $order_by
     * @param string $table
     *
     * @return mixed
     */
    public function raw_find( $condition = array(), $limit = array(), $order_by = array(), $table = '' )
    {
        $table = ( !empty( $this->table ) ) ? $this->table : $table;
        
        return $this->db->raw_find($table, $condition, $limit, $order_by);
    }
    
    /**
     * @param $table
     *
     * @return mixed
     */
    public function fields( $table )
    {
        $table = ( !empty( $this->table ) ) ? $this->table : $table;
        
        return $this->db->fields($table);
    }
    
    /**
     * @return mixed
     */
    public function num_rows()
    {
        return $this->db->num_rows();
    }
    
    /**
     * @return mixed
     */
    public function insert_id()
    {
        return $this->db->insert_id();
    }
    
    /**
     * @param $pk
     */
    public function set_pk( $pk )
    {
        $this->db->set_pk($pk);
    }
    
    /**
     *
     */
    public function __destruct()
    {
        unset( $this->db );
    }
    
}
