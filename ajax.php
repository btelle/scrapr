<?php

require_once('loader.php');
error_reporting(E_ERROR); 

switch($_REQUEST['mode']) {
    case 'login':
        $u = new DB_Users();
        $user = $u->get_user($_POST['username']);
        
        if(count($user) > 0 && $user[0]['log_in'] == 1)
        {
            $user = $user[0];
            if($u->hash_password($_POST['password'], $user['secret']) === $user['passhash'])
            {
                $p = new DB_photos();
                $data = array('message'=>'Correct log in');
                $data['api_key'] = $user['api_key'];
                $data['new_follow_count'] = $p->get_new_scrape_result();
                $data['new_search_count'] = $p->get_new_search_result();
                json_response($data);
            }
            else
            {
                json_response(array('error'=>array('message'=>'Incorrect password', 'field'=>'password')), 403);
            }
        }
        else
        {
            json_response(array('error'=>array('message'=>'Invalid or unauthorized user', 'field'=>'username')), 403);
        }
        break;
        
    case 'first_run':
        $u = new DB_users();
        $users = $u->get_num_users();
        
        if($users > 1)
        {
            json_response(array('error'=>array('message'=>'There are already 2 users on the system')), 500);
        }
        elseif($_POST['username'] == 'system')
        {
            json_response(array('error'=>array('message'=>'Username is reserved')), 500);
        }
        else
        {
            $data = array();
            $data['username'] = $_POST['username'];
            $data['secret'] = $u->generate_secret();
            $data['api_key'] = $u->generate_api_key();
            $data['log_in'] = 1;
            $data['passhash'] = $u->hash_password($_POST['password'], $data['secret']);
            
            if($u->insert($data))
            {
                json_response(array('message'=>'Created user'));
            }
            else
            {
                json_response(array('error'=>array('message'=>'User creation failed')), 500);
            }
        }
        break;
        
    case 'confirm_key':
        if(confirm_api_key($_GET['api_key']))
        {
            $p = new DB_photos();
            
            $data = array('message'=>'OK');
            $data['new_follow_count'] = $p->get_new_scrape_result();
            $data['new_search_count'] = $p->get_new_search_result();
            json_response($data);
        }
        else
        {
            json_response(array('error'=>array('message'=>'Not Logged In')), 401);
        }
        
        break;
        
    case 'num_users':
        $u = new DB_users();
        $users = $u->get_num_users();
        
        json_response(array('num_users'=>$users));
        break;
    
    case 'all_profiles':
        if(confirm_api_key($_GET['api_key']))
        {
            $p = new DB_Profiles();
            $profiles = $p->get_all();
            
            json_response(array('profiles'=>$profiles));
        }
        else
        {
            json_response(array('error'=>array('message'=>'Not Logged In')), 401);
        }
        
        break;
        
    case 'all_queries':
        if(confirm_api_key($_GET['api_key']))
        {
            $s = new DB_Search_queries();
            $queries = $s->get_all();
            
            json_response(array('queries'=>$queries));
        }
        else
        {
            json_response(array('error'=>array('message'=>'Not Logged In')), 401);
        }
        
        break;
        
    case 'all_filters':
        if(confirm_api_key($_GET['api_key']))
        {
            $f = new DB_Filters();
            $filters = $f->get_all();
            
            json_response(array('filters'=>$filters));
        }
        else
        {
            json_response(array('error'=>array('message'=>'Not Logged In')), 401);
        }
        
        break;
        
    case 'profile':
        if(confirm_api_key($_GET['api_key']))
        {
            $p = new DB_Profiles();
            $profile = $p->get_by_id($_GET['id']);
            
            if(isset($profile['id']))
            {
                json_response(array('profile'=>$profile));
            }
            else
            {
                json_response(array('error'=>array('message'=>'Profile Not Found')), 404);
            }
        }
        elseif(confirm_api_key($_POST['api_key']))
        {
            $p = new DB_Profiles();
            
            $data = array();
            $data['id'] = $_POST['id'];
            $data['snid'] = $_POST['snid'];
            $data['name'] = $_POST['name'];
            
            if($data['id'] > 0)
            {
                $id = $data['id'];
                unset($data['id']);
                
                if($p->update($id, $data))
                {
                    json_response(array('message'=>'OK'));
                }
            }
            else
            {
                 unset($data['id']);
                
                if($p->insert($data))
                {
                    json_response(array('message'=>'OK'));
                }
            }
        }
        else
        {
            json_response(array('error'=>array('message'=>'Not Logged In')), 401);
        }
        break;
        
    case 'query':
        if(confirm_api_key($_GET['api_key']))
        {
            $s = new DB_Search_queries();
            $query = $s->get_by_id($_GET['id']);
            
            if(isset($query['id']))
            {
                json_response(array('query'=>$query));
            }
            else
            {
                json_response(array('error'=>array('message'=>'Query Not Found')), 404);
            }
        }
        elseif(confirm_api_key($_POST['api_key']))
        {
            $s = new DB_Search_queries();
            
            $data = array();
            $data['id'] = $_POST['id'];
            $data['search'] = $_POST['search'];
            
            if($data['id'] > 0)
            {
                $id = $data['id'];
                unset($data['id']);
                
                if($s->update($id, $data))
                {
                    json_response(array('message'=>'OK'));
                }
            }
            else
            {
                 unset($data['id']);
                
                if($s->insert($data))
                {
                    json_response(array('message'=>'OK'));
                }
            }
        }
        else
        {
            json_response(array('error'=>array('message'=>'Not Logged In')), 401);
        }
        break;
        
    case 'filter':
        if(confirm_api_key($_GET['api_key']))
        {
            $f = new DB_Filters();
            $filter = $f->get_by_id($_GET['id']);
            
            if(isset($filter['id']))
            {
                json_response(array('filter'=>$filter, 'operators'=>$f->get_operators(), 'actions'=>$f->get_actions()));
            }
            else
            {
                json_response(array('error'=>array('message'=>'Filter Not Found')), 404);
            }
        }
        elseif(confirm_api_key($_POST['api_key']))
        {
            $f = new DB_Filters();
            
            $data = array();
            $data['id'] = $_POST['id'];
            $data['field'] = $_POST['field'];
            $data['operator'] = $_POST['operator'];
            $data['value'] = $_POST['value'];
            $data['action'] = $_POST['action'];
            $data['priority'] = $_POST['priority'];
            
            if($data['id'] > 0)
            {
                $id = $data['id'];
                unset($data['id']);
                
                if($f->update($id, $data))
                {
                    json_response(array('message'=>'OK'));
                }
            }
            else
            {
                 unset($data['id']);
                
                if($f->insert($data))
                {
                    json_response(array('message'=>'OK'));
                }
            }
        }
        else
        {
            json_response(array('error'=>array('message'=>'Not Logged In')), 401);
        }
        break;
        
    case 'delete_profile':
        if(confirm_api_key($_POST['api_key']))
        {
            $p = new DB_Profiles();
            
            if($p->delete($_POST['id']))
            {
                json_response(array('message'=>'OK'));
            }
            else
            {
                json_response(array('error'=>array('message'=>'Could not delete')), 500);
            }
        }
        else
        {
            json_response(array('error'=>array('message'=>'Not Logged In')), 401);
        }
        break;
        
    case 'delete_query':
        if(confirm_api_key($_POST['api_key']))
        {
            $s = new DB_Search_queries();
            
            if($s->delete($_POST['id']))
            {
                json_response(array('message'=>'OK'));
            }
            else
            {
                json_response(array('error'=>array('message'=>'Could not delete')), 500);
            }
        }
        else
        {
            json_response(array('error'=>array('message'=>'Not Logged In')), 401);
        }
        break;
        
    case 'delete_filter':
        if(confirm_api_key($_POST['api_key']))
        {
            $f = new DB_Filters();
            
            if($f->delete($_POST['id']))
            {
                json_response(array('message'=>'OK'));
            }
            else
            {
                json_response(array('error'=>array('message'=>'Could not delete')), 500);
            }
        }
        else
        {
            json_response(array('error'=>array('message'=>'Not Logged In')), 401);
        }
        break;
        
    case 'logs':
        if(confirm_api_key($_GET['api_key']))
        {
            $l = new DB_logs();
            
            $rows = $l->filter($_GET['user_id'], $_GET['timestamp'], $_GET['log_level'], $_GET['message'], $_GET['last_id']);
            json_response(array('logs'=>$rows));
        }
        elseif(confirm_api_key($_POST['api_key']))
        {
            // Insert a log entry
            $u = new DB_Users();
            $l = new DB_logs();
            $users = $u->get_user_by_key($_POST['api_key']);
            
            $data = array();
            $data['user_id'] = $users[0]['id'];
            $data['log_level'] = $_POST['log_level'];
            $data['message'] = $_POST['message'];
            $data['timestamp'] = date('Y-m-d H:i:s');
            
            if($l->insert($data))
            {
                json_response(array('message'=>'OK'));
            }
            else
            {
                json_response(array('error'=>array('message'=>'Failed to insert')), 500);
            }
        } 
        else 
        {
            json_response(array('error'=>array('message'=>'Not Logged In')), 401);
        }
        break;
        
    case 'follow_photos':
        if(confirm_api_key($_GET['api_key']))
        {
            $p = new DB_Photos();
            if($_GET['start_id'] != '' && $_GET['last_id'] != '')
                $p->mark_viewed('follow', $_GET['start_id'], $_GET['last_id']);
            
            $rows = $p->get_follow_photos($_GET['follow_id'], $_GET['last_id']);
            
            json_response(array('photos'=>$rows));
        }
        else
        {
            json_response(array('error'=>array('message'=>'Not Logged In')), 401);
        }
        break;
        
    case 'search_photos':
        if(confirm_api_key($_GET['api_key']))
        {
            $p = new DB_Photos();
            
            if($_GET['start_id'] != '' && $_GET['last_id'] != '')
                $p->mark_viewed('search', $_GET['start_id'], $_GET['last_id']);
            
            $rows = $p->get_search_photos($_GET['query_id'], $_GET['last_id']);
            
            json_response(array('photos'=>$rows));
        }
        else
        {
            json_response(array('error'=>array('message'=>'Not Logged In')), 401);
        }
        break;
        
    case 'delete_by_profile':
        if(confirm_api_key($_POST['api_key']))
        {
            $p = new DB_Photos();
            
            if($p->delete_by_profile($_POST['snid']))
                json_response(array('message'=>'OK'));
        }
        else
        {
            json_response(array('error'=>array('message'=>'Not Logged In')), 401);
        }
        break;
        
    case 'ignore_profile':
        if(confirm_api_key($_POST['api_key']))
        {
            $f = new DB_Filters();
            $p = new DB_Photos();
            
            if($f->ignore_profile($_POST['snid']) && $p->delete_by_profile($_POST['snid']))
                json_response(array('message'=>'OK'));
        }
        else
        {
            json_response(array('error'=>array('message'=>'Not Logged In')), 401);
        }
        break;
}

function json_response($resp, $code=200)
{
    switch($code)
    {
        case 500: header('HTTP/1.1 500 Internal Server Error'); break;
        case 403: header('HTTP/1.1 403 Forbidden'); break;
        case 401: header('HTTP/1.1 401 Unauthorized'); break;
        case 404: header('HTTP/1.1 404 File Not Found'); break;
    }
    
    header('Content-Type: application/json');
    
    echo json_encode($resp);
}

function confirm_api_key($key)
{
    $u = new DB_Users();
    $users = $u->get_user_by_key($key);
    
    return count($users) == 1 && isset($users[0]['id']);
}