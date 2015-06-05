<?php

class DB_photos extends DB_Model
{
    var $table = 'photos';
    
    function insert_scrape_result($data)
    {
        return $this->sql->SQL_Insert('scrape_results', $data);
    }
    
    function get_new_scrape_result() 
    {
        $query = "SELECT COUNT(*) AS cnt FROM photos INNER JOIN '.SQL_DB.'.scrape_results ON scrape_results.photo_id=photos.id WHERE viewed IS NULL and deleted IS NULL";
        
        $res = $this->sql->SQL_Exec($query);
        
        return isset($res[0]['cnt'])? $res[0]['cnt']: 0;
    }
    
    function get_new_search_result() 
    {
        $query = "SELECT COUNT(*) AS cnt FROM photos INNER JOIN ".SQL_DB.".search_results ON search_results.photo_id=photos.id WHERE viewed IS NULL and deleted IS NULL";
        
        $res = $this->sql->SQL_Exec($query);
        
        return isset($res[0]['cnt'])? $res[0]['cnt']: 0;
    }
    
    function insert_search_result($data)
    {
        return $this->sql->SQL_Insert('search_results', $data);
    }
    
    function get_follow_photos($profile_id, $start_id, $limit=30)
    {
        $where = array();
        
        if($profile_id > 0)
            $where['profile_id'] = $profile_id;
            
        if($start_id > 0)
            $where[] = "photos.id < '".$start_id."'";
            
        $where[] = 'viewed IS NULL';
        
        return $this->sql->SQL_Select('photos INNER JOIN scrape_results ON photos.id=scrape_results.photo_id', 'photos.*, retrieved, viewed', $where, 'photos.id DESC', $limit);
    }
    
    function get_search_photos($query_id, $start_id, $limit=30)
    {
        $where = array();
        
        if($profile_id > 0)
            $where['query_id'] = $query_id;
            
        if($start_id > 0)
            $where[] = "photos.id < '".$start_id."'";
        
        $where[] = 'viewed IS NULL';
        
        return $this->sql->SQL_Select('photos INNER JOIN search_results ON photos.id=search_results.photo_id', 'photos.*, retrieved, viewed', $where, 'photos.id DESC', $limit);
    }
    
    function mark_viewed($type, $start, $end)
    {
        switch($type)
        {
            case 'follow':
                $table = 'scrape_results'; break;
            case 'search':
                $table = 'search_results'; break;
            default:
                return FALSE;
        }
        
        $query = "UPDATE $table SET viewed=NOW() WHERE photo_id <= '$start' AND photo_id >= '$end';";
        //echo $query;
        return $this->sql->SQL_Exec($query);
    }
    
    function delete_by_profile($snid)
    {
        $update = array('deleted'=>date('Y-m-d H:i:s'));
        return $this->sql->SQL_Update($this->table, $update, array('owner'=>$snid));
    }
    
    function delete($id)
    {
        $this->sql->SQL_Delete('scrape_results', array('photo_id'=>$id));
        $this->sql->SQL_Delete('search_results', array('photo_id'=>$id));
        return parent::delete($id);
    }
}