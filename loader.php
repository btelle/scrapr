<?php
error_reporting(E_ALL^E_WARNING); 
ini_set("display_errors", 1); 

date_default_timezone_set('America/Los_Angeles');
define('LIB_DIR', 'lib/');
define('MODELS_DIR', 'models/');

if(!file_exists(LIB_DIR) || !file_exists(MODELS_DIR))
    die('Please specify the location of you lib and models directories in loader.php');

require_once(LIB_DIR.'credentials.php');

if(!defined('FLICKR_API_KEY') || !defined('SQL_HOST'))
    die('Please move lib/credentials.default.php to lib/credentials.php and enter your Flickr and SQL credentials');
    
if(!file_exists(LIB_DIR.'phpflickr'))
    die('Please install PHPFlickr to lib/');

require_once(LIB_DIR.'phpflickr/phpFlickr.php');
require_once(LIB_DIR.'DatabaseErrorException.class.php');
require_once(LIB_DIR.'mysql.class.php');
require_once(LIB_DIR.'db_model.php');
require_once(MODELS_DIR.'users.php');
require_once(MODELS_DIR.'photos.php');
require_once(MODELS_DIR.'profiles.php');
require_once(MODELS_DIR.'logs.php');
require_once(MODELS_DIR.'filters.php');
require_once(MODELS_DIR.'search_queries.php');

try {
    $sql = new SQL(SQL_USER, SQL_PASS, SQL_DB, SQL_HOST);
    unset($sql);
} catch(DatabaseErrorException $e) {
    die("MySQL Connection failed: ".$e->getMessage());
}