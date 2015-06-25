<?php

class DB_settings extends DB_Model
{
    var $table = 'settings';
    
    function get($key)
    {
        $rows = $this->sql->SQL_Select($this->table, '*', array('key'=>$key));
        return isset($rows[0])? $rows[0]['value']: NULL;
    }
    
    function get_all()
    {
        return $this->sql->SQL_Select($this->table, '*');
    }
    
    function set($key, $value)
    {
        if($this->get($key) === NULL)
        {
            $this->insert(array('key'=>$key, 'value'=>$value));
        }
        else
        {
            $this->update($key, array('value'=>$value));
        }
    }
    
    function update($key, $data) 
    {
        return $this->sql->SQL_Update($this->table, $data, array('key'=>$key));
    }
}
