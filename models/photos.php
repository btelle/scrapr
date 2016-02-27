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
        $query = "SELECT COUNT(*) AS cnt FROM photos INNER JOIN scrape_results ON scrape_results.photo_id=photos.id WHERE viewed IS NULL and deleted IS NULL";
        
        $res = $this->sql->SQL_Exec($query);
        
        return isset($res[0]['cnt'])? $res[0]['cnt']: 0;
    }
    
    function get_new_search_result() 
    {
        $query = "SELECT COUNT(*) AS cnt FROM photos INNER JOIN search_results ON search_results.photo_id=photos.id WHERE viewed IS NULL and deleted IS NULL";
        
        $res = $this->sql->SQL_Exec($query);
        
        return isset($res[0]['cnt'])? $res[0]['cnt']: 0;
    }
    
    function get_saved_count()
    {
        $query = "SELECT COUNT(*) AS cnt FROM photos WHERE saved_file IS NOT NULL";
        
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
        
        return $this->sql->SQL_Select('photos INNER JOIN scrape_results ON photos.id=scrape_results.photo_id INNER JOIN profiles ON scrape_results.profile_id=profiles.id', 'photos.*, name, retrieved, viewed', $where, 'photos.id DESC', $limit);
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
    
    function get_saved_photos($start_id, $limit=30)
    {
        $where = array();
        
        if($start_id > 0)
            $where[] = "photos.id < '".$start_id."'";
        
        $where[] = 'saved_file IS NOT NULL';
        
        return $this->sql->SQL_Select('photos', '*', $where, 'photos.id DESC', $limit);
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
        $rows = $this->sql->SQL_Select($this->table, '*', array('id'=>$id));
        if(isset($rows[0]))
        {
            $this->sql->SQL_Delete('scrape_results', array('photo_id'=>$id));
            $this->sql->SQL_Delete('search_results', array('photo_id'=>$id));
            
            if($rows[0]['saved_file'] != '')
            {
                @unlink($rows[0]['saved_file']);
            }
            
            return parent::delete($id);
        }
    }
    
    function save_image($id)
    {
        $rows = $this->sql->SQL_Select($this->table, '*', array('id'=>$id));
        
        if(isset($rows[0]['id']))
        {
            $filename = SAVE_DIR.($rows[0]['original'] == ''? basename($rows[0]['large']): basename($rows[0]['original']));
            
            $fh = fopen($filename, 'w');
            fwrite($fh, file_get_contents($rows[0]['original'] == ''? $rows[0]['large']: $rows[0]['original']));
            fclose($fh);
            
            $update = array('saved_file'=>$filename, 'deleted'=>NULL);
            $this->sql->SQL_Update($this->table, $update, array('id'=>$id));
            return TRUE;
        }
        
        return FALSE;
    }
    
    function get_saved_image($id)
    {
        $rows = $this->sql->SQL_Select($this->table, '*', array('id'=>$id));
        
        if(isset($rows[0]))
            return $rows[0];
        
        return FALSE;
    }
    
    function gc($offset=0)
    {
        $ts = time()-$offset;
        
        $query = "DELETE FROM scrape_results WHERE UNIX_TIMESTAMP(viewed) < $ts;";
        $this->sql->SQL_Exec($query);
        
        $query = "DELETE FROM search_results WHERE UNIX_TIMESTAMP(viewed) < $ts;";
        $this->sql->SQL_Exec($query);
        
        $query = "DELETE FROM p USING photos AS p WHERE NOT EXISTS(SELECT * FROM search_results WHERE photo_id=p.id) AND NOT EXISTS(SELECT * FROM scrape_results WHERE photo_id=p.id) AND saved_file IS NULL;";
        $this->sql->SQL_Exec($query);
    }

    function save_image_by_url($url)
    {
        $filename = SAVE_DIR.(basename($url));
            
        $fh = fopen($filename, 'w');
        fwrite($fh, file_get_contents($url));
        fclose($fh);

        return filesize($filename) > 0;
    }
}