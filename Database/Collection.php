<?php
/**
 * Created by PhpStorm.
 * User: DWIsprananda
 * Date: 9/15/2016
 * Time: 9:58 AM
 */

namespace Kecik;

/**
 * Class DBCollections
 */
class DBCollections implements  \Iterator, \Countable {
    private $cursor = 0;
    
    private $data;
    private $raw_data;
    
    public function __construct($data)
    {
        $this->cursor = 0;
        $this->raw_data = $data;
        $this->data = $data;
    }
    
    public function raw()
    {
        return $this->raw_data;
    }
    
    public function __toString()
    {
        if (!is_object($this->data) && !is_array($this->data)) {
            return $this->data;
        }
        
        return json_encode($this->data);
    }
    
    public function __set( $offset, $value )
    {
        if (!empty($offset)) {
            $this->data->$offset = $value;
        }
    }
    
    public function __get( $offset )
    {
        if (!is_object($this->data-$offset) && !is_array($this->data->$offset)) {
            return $this->data->$offset;
        }
        
        if (!($this->data->$offset instanceof DBCollections)) {
            $this->data->$offset = new DBCollections($this->data->$offset);
        }
        
        return (isset($this->data->$offset))? $this->data->$offset : null;
    }
    
    public function get()
    {
        return $this->data;
    }
    
    public function each(\Closure $callback) {
        
        foreach ( $this as $key => $row ) {
            $callback($key, $row);
        }
        
    }
    
    public function current() {
        return $this->data[$this->cursor];
    }
    
    public function next()
    {
        ++$this->cursor;
    }
    
    public function key()
    {
        return $this->cursor;
    }
    
    public function valid()
    {
        if (is_object($this->data)) {
            return FALSE;
        }
        
        return isset($this->data[$this->cursor]);
    }
    
    public function rewind()
    {
        $this->cursor = 0;
    }
    
    public function count()
    {
        return count($this->data);
    }
    
}