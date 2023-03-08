<?php

include('DB_config.php');

class DB
{
	var $_dbConn = 0;
	var $_queryResource = 0;

	function connect_db_with_selforder()
	{
		global $_DB;

		if($this->connect_db("easysales.asia", "easysale_zack", "zack123@", "easysale_selforder"))
			return true;
	}
    
    function connect_with_given_connection($user,$passowrd,$database, $host = "easysales.asia"){
        if($this->connect_db($host,$user,$passowrd,$database)){
            return true;
        }else{
            return false;
        }
    }

	function connect_db_with_db_config($database)
	{
		global $_DB;

		if($this->connect_db($_DB['host'], $_DB['username'], $_DB['password'], $database)){
			return true;
		}else{
			return false;
		}
			
	}
	
	function connect_db_with_db_config_demo()
    {
    	global $_DB;
        if($this->connect_db($_DB['host'], $_DB['username'], $_DB['password'], $_DB['dbname_demo']))
        	return true;
    }

	function connect_db($host, $user, $pwd, $dbname)
	{
		$dbConn = mysql_connect($host, $user, $pwd);

		if (! $dbConn){
			// file_put_contents('dbErrorLog.log',mysql_error($dbConn),FILE_APPEND);
			return false;
		}

		mysql_query("SET NAMES utf8");

		if (! mysql_select_db($dbname, $dbConn)){
			// file_put_contents('dbErrorLog.log',mysql_error($dbConn),FILE_APPEND);
			return false;
		}

		$this->_dbConn = $dbConn;

		return true;
	}

	function query($sql)
	{
		if (! $queryResource = mysql_query($sql, $this->_dbConn))
			return [0,mysql_error($this->_dbConn)];
			
		$this->_queryResource = $queryResource;

		return $queryResource;
	}

	/** Get array return by MySQL */
	function fetch_array()
	{
		return mysql_fetch_array($this->_queryResource, MYSQL_ASSOC);
	}

	function get_num_rows()
	{
		return mysql_num_rows($this->_queryResource);
	}

	function get_affected_rows()
	{
		return mysql_affected_rows($this->_dbConn);
	}
	
	/** Get the cuurent id */
	function get_insert_id()
	{
		return mysql_insert_id($this->_dbConn);
	}

	function close(){
		mysql_close($this->_dbConn);
	}
}
?>
