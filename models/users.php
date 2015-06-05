<?php

class DB_users extends DB_Model
{
    var $table = 'users';
    
    function get_user($username)
    {
        return $this->sql->SQL_Select($this->table, '*', array('username'=>$username));
    }
    
    function get_user_by_key($key)
    {
        return $this->sql->SQL_Select($this->table, 'id', array('api_key'=>$key));
    }
    
    function hash_password($pass, $secret)
    {
        return hash('sha256', $secret.$pass);
    }
    
    function generate_api_key()
    {
        return sha1(rand(0, 100).time());
    }
}