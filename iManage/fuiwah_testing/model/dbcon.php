<?php
require_once('model/DB_class.php');

function dbcon()
	{
		global $_DB;

		if($this->connect_db($_DB['host'], $_DB['username'], $_DB['password'], "easysale_manishtest"))
			return true;
	}

?>