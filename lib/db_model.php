<?php

class DB_model
{
    protected $sql;
    
    function __construct()
    {
        global $sql;
        if(!isset($sql) || !is_a($sql, 'SQL'))
            $sql = new SQL(SQL_USER, SQL_PASS, SQL_DB, SQL_HOST);
            
        $this->sql = &$sql;
    }
    
    function update($id, $data)
    {
        return $this->sql->SQL_Update($this->table, $data, array('id'=>$id));
    }
    
    function insert($data)
    {
        return $this->sql->SQL_Insert($this->table, $data);
    }
    
    function delete($id)
    {
        return $this->sql->SQL_Delete($this->table, array('id'=>$id));
    }
    
    function run_query($query)
    {
        return $this->sql->SQL_Exec($query);
    }
}