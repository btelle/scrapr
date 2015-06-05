<?php

/**
 * SQL
 * 
 * SQL Interface for MySQL Database Engine
 * 
 * DatabaseErrorExceptions are used for errors:
 * 	+ Fatal errors are thrown at E_USER_ERROR
 * 	+ Syntax errors are thrown at E_USER_WARNING
 * 	+ Parameter errors are thrown at E_USER_NOTICE
 * 
 * @throws DatabaseErrorException
 * @author Brandon Telle
 * @version 11.10.28
 */

class SQL
{
	/* Private variables */
	private $errno, $errmes, $query, $dbh; 
	private $memcached, $memcached_host, $memcached_port, $memcached_length, $memcached_result;
	
	/**
	 * Constructor.
	 * 
	 * Initializes variables.
	 */
	function __construct($user='root', $pass='', $db='', $host='localhost')
	{
		$this->errno = 0;
		$this->errmes = "";
		$this->query = "";
		$this->memcached = FALSE;
		
		$this->SQL_Connect($user, $pass, $db, $host);
	}
	
	/**
	 * Destructor
	 * 
	 * Closes SQL connection.
	 */
	function __destruct() {
		@mysql_close($this->dbh);
	}
	
	/**
	 * SQL_Connect
	 * 
	 * Connects to specified SQL database
	 * 
	 * @param $username username to connect with
	 * @param $password password for user
	 * @param $database database to use
	 * @param $host database host - default localhost
	 * 
	 * @throws DatabaseErrorException 
	 * 
	 * @return true on success
	 */
	function SQL_Connect($username, $password, $database, $host="localhost")
	{
		$this->dbh = @mysql_connect($host, $username, $password) or list($this->errno, $this->errmes) = array(mysql_errno(), mysql_error());
		if(!$this->errno)
		{
			@mysql_select_db($database, $this->dbh) or list($this->errno, $this->errmes) = array(mysql_errno(), mysql_error());
			
			if($this->errno)
				throw new DatabaseErrorException($this->errmes, $this->errno, E_ERROR);
		}
		else 
			throw new DatabaseErrorException($this->errmes, $this->errno, E_ERROR);
		
		return ($this->errno == 0);
	}
	
	/**
	 * SQL_Update
	 * 
	 * Updates each field in $fields to the value specified.
	 * 
	 * @param $table table to update
	 * @param $fields associative array of fields and their new values
	 * @param $where associative array of WHERE clause values
	 * @param $limit number of records matching the WHERE to update
	 * 
	 * @throws DatabaseErrorException
	 * 
	 * @return true on success
	 */
	function SQL_Update($table, $fields, $where, $limit=0)
	{
		if(count($fields))
		{
			$query = "UPDATE ".$table." SET ".$this->SQL_Parse_Array($fields, ", ")." WHERE ".
						$this->SQL_Parse_Array($where).(($limit<=0)?";":" LIMIT $limit;");
			
			$this->query = $query;
			$this->SQL_Query();
			
			if($this->errno)
				throw new DatabaseErrorException($this->errmes, $this->errno, E_USER_WARNING);
		}
		else
		{
			throw new DatabaseErrorException('You must update something ($fields may not be empty)', -1, E_USER_NOTICE);
		}
		return ($this->errno == 0);
	}
	
	/**
	 * SQL_Select
	 * 
	 * Selects rows from database
	 * 
	 * @param $table table to select from
	 * @param $fields array of fields to select - default *
	 * @param $where associative array of WHERE clause values
	 * @param $order ORDER BY field and direction
	 * @param $limit number of values to return
	 * @param $offset offset value
	 * 
	 * @throws DatabaseErrorException
	 * 
	 * @return array of rows found - may be empty
	 */
	function SQL_Select($table, $fields='*', $where=array(), $order="", $limit=0, $offset=0)
	{
		if(is_string($fields))
			$fields = array($fields);
		
		if(count($fields))
		{
			$query = "SELECT ".implode(", ", $fields)." FROM $table";
			if(count($where)) $query .= " WHERE ".$this->SQL_Parse_Array($where);
			if($order!="") $query .= " ORDER BY $order";
			if($limit)
			{
				if($offset)
					$query .= " LIMIT $offset, $limit";
				else
					$query .= " LIMIT $limit";
			}
			$query .= ";";
			
			$this->query = $query;
			$retArr = $this->SQL_Query();
			
			if($this->errno)
				throw new DatabaseErrorException($this->errmes, $this->errno, E_USER_WARNING);
		}
		else
		{
			throw new DatabaseErrorException('You must select something ($fields may not be empty)', -1, E_USER_NOTICE);
			$retArr = array();
		}
		return $retArr;
	}
	
	/**
	 * SQL_Delete
	 * 
	 * Deletes rows from the database
	 * 
	 * @param $table table to delete from
	 * @param $where associative array of WHERE clause values 
	 * @param $limit number of rows to delete - default 1
	 * 
	 * @throws DatabaseErrorException
	 * 
	 * @return true on success
	 */
	function SQL_Delete($table, $where, $limit=1)
	{
		if(!empty($where))
		{
			$query = "DELETE FROM ".$table." WHERE ".$this->SQL_Parse_Array($where)." LIMIT ".(($limit>0)?$limit:1).";";
			
			$this->query = $query;
			$this->SQL_Query();
			
			if($this->errno)
				throw new DatabaseErrorException($this->errmes, $this->errno, E_USER_WARNING);
		}
		else
		{
			throw new DatabaseErrorException('Cannot delete without $where values', -1, E_USER_NOTICE);
			$this->errno = -1;
		}
		return ($this->errno == 0);
	}
	
	/**
	 * SQL_Insert
	 * 
	 * Inserts a row into the database
	 * 
	 * @param $table table to insert into
	 * @param $valuesArr associative array of fields and values
	 * 
	 * @throws DatabaseErrorException
	 * 
	 * @return inserted row's ID (if none exists, true) on success and false on failure 
	 */
	function SQL_Insert($table, $values)
	{
		if(!empty($values))
		{
			$query = "INSERT INTO ".$table."(";
			$vals = "VALUES("; 
			foreach($values as $attr => $value)
			{
				$query .= $attr.", ";
				$vals .= "'".$this->SQL_Escape($value)."', ";
			}
			$query = substr($query, 0, strrpos($query, ",")).") ".substr($vals, 0, strrpos($vals, ",")).");";
			
			$this->query = $query;
			$this->SQL_Query();
			
			if($this->errno)
			{
				throw new DatabaseErrorException($this->errmes, $this->errno, E_USER_WARNING);
				$id = FALSE;
			}
			else
			{
				$id = mysql_insert_id();
				$id = ($id == "")? TRUE: $id;
			}
		}
		else
		{
			throw new DatabaseErrorException('You must insert something ($values may not be empty)', -1, E_USER_NOTICE);
			$id = FALSE;
		} 
		
		return $id;
	}
	
	/**
	 * SQL_Count
	 * 
	 * Gets row count for specified table.
	 *  
	 * @param $table table to COUNT
	 * @param $values array of values to COUNT on
	 * @param $where associative array of where values
	 * 
	 * @throws DatabaseErrorException
	 * 
	 * @return number of rows found
	 */
	function SQL_Count($table, $values='*', $where=array())
	{
		if(is_string($values))
			$values = array($values);
		
		$ret = $this->SQL_Select($table, 'COUNT('.implode($values, ", ").') AS count', $where);
		
		if($ret)
			$ret = $ret[0]['count'];
			
		else
			$ret = 0;
		
		return $ret;
	}
	
	/**
	 * SQL_Exec
	 * 
	 * Executes an SQL query. Be careful with this function, 
	 * all user-generated inputs should be sanitized with SQL_Escape 
	 * before creating the query.
	 * 
	 * @param $query query to be executed
	 * 
	 * @throws DatabaseErrorException
	 * 
	 * @return if rows are returned by the query, an array of them, otherwise true on success
	 */
	function SQL_Exec($query)
	{
		$this->query = $query;
		$ret = $this->SQL_Query();
		
		if($this->errno)
			throw new DatabaseErrorException($this->errmes, $this->errno, E_USER_WARNING);
		
		return (empty($ret)? ($this->errno == 0): $ret);
	}
	
	/**
	 * SQL_Query
	 * 
	 * Utility funtion that executes the queries generated by 
	 * other functions in this class. Uses memcached to reduce
	 * SQL server load. Configure memcached in /lib/general.inc.php
	 *  
	 * @param $query query to be executed - defaults to $this->query
	 * @return results of the query
	 */
	private function SQL_Query($query=NULL)
	{
		if($query == NULL)
			$query = $this->query;
		
		$run_query = FALSE;
		$this->memcached_result = FALSE;
			
		if($this->memcached)
		{		
			$memcache = new Memcache;
			$memcache->connect($this->memcached_host, $this->memcached_port) 
				or trigger_error("Could not connect to memcached server", E_USER_ERROR);
				
			$key = md5($query);
			$get_result = $memcache->get($key);
			
			if ($get_result !== false)
			{
				$this->memcached_result = TRUE;
				$retArr = $get_result;
			}
			else 
			{
				$run_query = TRUE;
			}
		}
		else 
		{
			$run_query = TRUE;
		}
		if($run_query)
		{
			$ret = @mysql_query($query, $this->dbh);
			
			$count = @mysql_num_rows($ret) or $count = 0;
			$retArr = array();
			
			for($i=0; $i<$count; $i++)
				$retArr[$i] = @mysql_fetch_assoc($ret);
			
			@mysql_free_result($ret);
			
			if($this->memcached)
				$memcache->set($key, $retArr, TRUE, $this->memcached_length);

			$this->errno = mysql_errno();
			$this->errmes = mysql_error();
		}
		return $retArr;
	}
	
	/**
	 * SQL_Parse_Array
	 * 
	 * Parses an associative array into query format
	 * 
	 * @param $array array of values to parse
	 * @param $delim delimiter between fields - default " AND "
	 * @return query string of array values
	 */
	private function SQL_Parse_Array($array, $delim = " AND ")
	{
		$query = "";
		if(count($array))
		{
			foreach($array as $attr => $value)
			{
                if(is_string($attr))
                {
                    $query .= "`".$attr."` = "."'".$this->SQL_Escape($value)."'".$delim;
                }
                else
                {
                    $query .= $value.$delim;
                }
			}
			$query = substr($query, 0, strrpos($query, $delim));
		}
		return $query;
	}
	
	/**
	 * SQL_Escape
	 * 
	 * Makes a value database-safe. Use SQL_Unescape to undo.
	 * 
	 * @param $val value to escape
	 * @return sanitized value
	 */
	function SQL_Escape($val)
	{
		return mysql_real_escape_string($val, $this->dbh);
	}
	
	/**
	 * SQL_Unescape
	 * 
	 * Desanitizes a database value
	 * 
	 * @param $val value to unescape
	 * @return unescaped value
	 */
	function SQL_Unescape($val)
	{
		return stripslashes($val);
	}
	
	/**
	 * SQL_Error
	 * 
	 * Get current SQL error values
	 * 
	 *  @return array of error number and error message
	 */
	function SQL_Error()
	{
		return array("errno"=>$this->errno, "errmes"=>$this->errmes, "query"=>$this->query);
	}
	
	/**
	 * SQL_Error_Number
	 * 
	 * Gets current SQL error number
	 * 
	 * @return error number
	 */
	function SQL_Error_Number()
	{
		return $this->errno;
	}
	
	/**
	 * SQL_Error_Message
	 * 
	 * Gets the current SQL error message
	 * 
	 * @return error message
	 */
	function SQL_Error_Message()
	{
		return $this->errmes;
	}
	
	/**
	 * SQL_Get_Query
	 * 
	 * Get the last run query
	 * 
	 * @return query
	 */
	function SQL_Get_Query()
	{
		return $this->query;
	}
	
	/**
	 * SQL_Date
	 * 
	 * Attempts to translate date to SQL DATETIME format
	 * 
	 * @param $date date to translate - defaults to current Unix timestamp
	 * 
	 * @return date in the format Y-m-d H:m:s
	 */
	function SQL_Date($date=NULL)
	{
		if(!isset($date))
			$date = time();
			
		if(!is_numeric($date))
			$date = strtotime($date);
			
		return date('Y-m-d H:i:s', $date);
	}
	
	/**
	 * SQL_Use_Memcached
	 * 
	 * Enable or disable memcached caching
	 * 
	 * @param $host host of memcached server
	 * @param $port port to connect on
	 * @param $length length of time to cache results, default 20 seconds
	 */
	function SQL_Use_Memcached($host='localhost', $port=11211, $length=20)
	{
		$this->memcached = TRUE;
		$this->memcached_host = $host;
		$this->memcached_port = $port;
		$this->memcached_length = $length;
	}
	
	/**
	 * SQL_Was_Memcached
	 * 
	 * Returns whether the last query result was obtained through memcached or not
	 * 
	 * @return true is memcached was used
	 */
	function SQL_Was_Memcached()
	{
		return $this->memcached_result;
	}
}
/* End of SQL class */
?>