<?php

class DB_filters extends DB_Model
{
    var $table = 'filters';
    
    function get_all()
    {
        return $this->sql->SQL_Select($this->table);
    }
    
    function get_by_id($id)
    {
        $rows = $this->sql->SQL_Select($this->table, '*', array('id'=>$id));
        return isset($rows[0])? $rows[0]: array();
    }
    
    function get_operators()
    {
        return array('==', '!=', '<', '<=', '>', '>=');
    }
    
    function get_actions()
    {
        return array('drop', 'save');
    }
    
    function ignore_profile($snid)
    {
        $data = array();
        $data['field'] = 'owner';
        $data['operator'] = '==';
        $data['value'] = $snid;
        $data['action'] = 'drop';
        $data['priority'] = 10;
        
        return $this->insert($data);
    }
}