<?php
/**
 * Created by PhpStorm.
 * User: DWIsprananda
 * Date: 9/15/2016
 * Time: 9:58 AM
 */

/**
 * Class DBConfig
 * @package Kecik
 */
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