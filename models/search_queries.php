<?php

class DB_search_queries extends DB_Model
{
    var $table = 'search_queries';
    
    function get_all()
    {
        return $this->sql->SQL_Select($this->table);
    }
    
    function get_by_id($id)
    {
        $rows = $this->sql->SQL_Select($this->table, '*', array('id'=>$id));
        return isset($rows[0])? $rows[0]: array();
    }
    
    function delete($id)
    {
        $this->sql->SQL_Delete('search_results', array('query_id'=>$id), PHP_INT_MAX);
        return parent::delete($id);
    }
}