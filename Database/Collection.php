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
    
    public function __construct($data)
    {
        $this->cursor = 0;
        $this->data = $data;
    }
    
    public function __toString()
    {
        if (!is_object($this->data) && !is_array($this->data)) {
            return $this->data;
        }
    
        return json_encode($this->data, JSON_PRETTY_PRINT);
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