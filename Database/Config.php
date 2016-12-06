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
    
    private $failOver = [];
    
    public function __construct( $config )
    {
        
        if ( is_array($config) ) {
            $config = (object) $config;
        }
        
        if ( !isset( $config->dsn ) && empty( $config->dsn ) ) {
            $this->driver   = ( isset( $config->driver ) ) ? $config->driver : '';
            $this->hostname = ( isset( $config->hostname ) ) ? $config->hostname : '';
            $this->username = ( isset( $config->username ) ) ? $config->username : '';
            $this->password = ( isset( $config->password ) ) ? $config->password : '';
            $this->dbname   = ( isset( $config->dbname ) ) ? $config->dbname : '';
        } else {
            $this->dsn = $config->dsn;
        }
        
        $this->charset  = ( isset( $config->charset ) ) ? : '';
        $this->failOver = ( !empty( $config->failOver ) ) ? : '';
    }
    
}