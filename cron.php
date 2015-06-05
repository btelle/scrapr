<?php

require_once('loader.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

set_time_limit(60*60*6);
ini_set('max_execution_time', 60*60*6);

$s = new Scraper();
$s->run_scraper();
$s->run_searches();

class Scraper
{
    private $filters;
    private $flickr;
    private $failed_filter;
    
    function __construct()
    {
        $this->flickr = new phpFlickr(FLICKR_API_KEY, FLICKR_AUTH_TOKEN);
        $this->filters = $this->get_filters();
        $this->log_message('info', 'Starting cron scrape');
    }
    
    function __destruct()
    {
        $this->log_message('info', 'Finishing cron scrape');
    }
    
    function run_scraper()
    {
        $p = new DB_Profiles();
        
        $params = array(
            'min_upload_date'=>date('Y-m-d H:i:s', time()-(60*60*6)),
            'max_upload_date'=>date('Y-m-d H:i:s', time()),
            'content_type'=>6,
            'extras'=>'url_l,url_o,date_taken'
        );
        
        foreach($p->get_all() as $profile)
        {
            $this->log_message('info', 'Scraping '.$profile['name']);
            
            $page = 1;
            
            do {
                $params['page'] = $page;
                
                $photos = $this->flickr->people_getPhotos($profile['snid'], $params);
                
                if($photos == NULL)
                {
                    $this->log_message('error', 'Flickr API call failed, returned NULL');
                    break;
                }
                
                if($photos['photos']['total'] > 0)
                {
                    $this->process_page($photos['photos']['photo'], 'scrape', $profile['id']);
                }
                
                $page++;
            }
            while(isset($photos['pages']) && $page <= $photos['pages']);
        }
    }

    function run_searches()
    {
        $s = new DB_Search_queries();
        
        $params = array(
            'min_upload_date'=>date('Y-m-d H:i:s', time()-(60*60*6)),
            'max_upload_date'=>date('Y-m-d H:i:s', time()),
            'content_type'=>6,
            'per_page'=>100,
            'extras'=>'url_l,url_o,date_taken'
        );
        
        foreach($s->get_all() as $query)
        {
            $this->log_message('info', 'Searching for '.$query['search']);
            
            $page = 1;
            $params['text'] = $query['search'];
            
            do {
                $params['page'] = $page;
                
                $photos = $this->flickr->photos_search($params);
                
                if($photos == NULL)
                {
                    $this->log_message('error', 'Flickr API call failed, returned NULL');
                    break;
                }
                
                if($photos['total'] > 0)
                {
                    $this->process_page($photos['photo'], 'search', $query['id']);
                }
                
                $page++;
            }
            while(isset($photos['pages']) && $page <= $photos['pages']);
        }
    }

    function process_page($photos, $src, $src_id)
    {   
        $p = new DB_Photos();
        
        foreach($photos as $photo)
        {
            if($this->filter($photo))
            {
                $this->log_message('info', 'Saving photo '.$photo['id']);
                
                $data = array();
                $data['farm_id'] = $photo['farm'];
                $data['server_id'] = $photo['server'];
                $data['photo_id'] = $photo['id'];
                $data['secret'] = $photo['secret'];
                $data['date_taken'] = $photo['datetaken'];
                $data['owner'] = $photo['owner'];
                $data['large'] = $photo['url_l'];
                $data['original'] = $photo['url_o'];
                try {
                    $p->insert($data);
                    $id = mysql_insert_id();
                    
                    switch($src)
                    {
                        case 'scrape':
                            $sc = array();
                            $sc['photo_id'] = $id;
                            $sc['profile_id'] = $src_id;
                            $sc['retrieved'] = date('Y-m-d H:i:s');
                            $p->insert_scrape_result($sc);
                            break;
                            
                        case 'search':
                            $sr = array();
                            $sr['photo_id'] = $id;
                            $sr['query_id'] = $src_id;
                            $sr['retrieved'] = date('Y-m-d H:i:s');
                            $p->insert_search_result($sr);
                            break;
                    }
                } 
                catch(Exception $e) {
                    if(strstr($e->getMessage(), 'PHOTO_UQ'))
                    {
                        $this->log_message('warning', 'Skipping non-unique image');
                    }
                    else
                    {
                        $this->log_message('error', 'MySQL error: '.$e->getMessage());
                    }
                }
            }
            else
            {
                $this->log_message('info', "Skipping filtered image: ".$photo['id']." [Filter ID: ".$this->failed_filter."]");
            }
        }
    }

    function get_filters()
    {
        $db = new DB_model();
        
        $query = "SELECT * FROM filters ORDER BY priority, field;";
        return $db->run_query($query);
    }

    function filter($photo)
    {
        $ret = TRUE;
        foreach($this->filters as $row)
        {
            $prev = $ret;
            if(isset($photo[$row['field']]))
            {
                switch($row['operator'])
                {
                    case '==':
                        $match = $photo[$row['field']] == $row['value'];
                        break;
                        
                    case '!=':
                        $match = $photo[$row['field']] != $row['value'];
                        break;
                        
                    case '<':
                        $match = $photo[$row['field']] < $row['value'];
                        break;
                        
                    case '>':
                        $match = $photo[$row['field']] > $row['value'];
                        break;
                        
                    case '<=':
                        $match = $photo[$row['field']] <= $row['value'];
                        break;
                        
                    case '>=':
                        $match = $photo[$row['field']] >= $row['value'];
                        break;
                        
                    default:
                        $match = FALSE;
                        break;
                }
                
                if($match)
                {
                    if($row['action'] == 'drop')
                        $ret = FALSE;
                    elseif($row['action'] == 'save')
                        $ret = TRUE;
                }
            }
            
            if($prev == TRUE && $ret == FALSE)      // This filter failed, save it!
                $this->failed_filter = $row['id'];
            
            elseif($prev == FALSE && $ret == TRUE)  // Un-failed!
                $this->failed_filter = NULL;
        }
        
        return $ret;
    }
    
    function log_message($level, $message, $user=1)
    {
        echo '['.strtoupper($level).'] '.$message.'<br />';
        
        $db = new DB_model();
        
        $data = array();
        $data['user_id'] = $user;
        $data['timestamp'] = date('Y-m-d H:i:s');
        $data['log_level'] = $level;
        $data['message'] = $message;
        
        $db->table = 'logs';
        return $db->insert($data);
    }
}

?>