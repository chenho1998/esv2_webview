<?php

include('model/DB_config.php');

class DBI extends mysqli
{
	var $_mysqli = 0;
	
    function DBI()
    {
        //do nothing
    }

    function connect_db_with_db_config()
    {
    	global $_DBI;
		
    	$mysqli = new mysqli($_DB['host'], $_DB['username'], $_DB['password'], $_DB['dbname']);
		
    	if ($mysqli->connect_errno) 
		{
    		printf("Connect failed: %s\n", $mysqli->connect_error);
    		
			exit();
    	}
		else
		{
			$this->_mysqli = $mysqli;
    		
			return true;
    	}
    }
    
    /** Get array return by MySQL */
   /* function query($sql)
    {
    	$this->_mysqli = $this->query($sql);
    	if($this->_mysqli)
    		return true;
    	else 
    		return false;
    	//         return mysql_fetch_array($this->_queryResource, MYSQL_ASSOC);
    }*/
    
    /** Get array return by MySQL */
    function fetch_array()
    {
    	return $this->fetch_object();
		
	// return mysql_fetch_array($this->_queryResource, MYSQL_ASSOC);
    }
}
?>