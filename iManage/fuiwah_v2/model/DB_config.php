<?php
    global $_DB;	
    $config = parse_ini_file(dirname(__FILE__).'/../../config.ini');
    // $_DB['host'] = "localhost:3307";
    // $_DB['username'] = "root";
    // $_DB['password'] = "";
    // $_DB['dbname'] = "test";
    
    /* get back office name */
    // $_DB['dbname'] = 'demo_' . $backOfficeName = basename(dirname(dirname($_SERVER['PHP_SELF'])));
    $_DB['host'] = $config["easysales_host"];
    $_DB['username'] = $config["easysales_username"];
    $_DB['password'] = $config["easysales_password"];
    $_DB['dbname'] = $config["easysales_dbname"];
	// $_DB['dbname_demo'] = "iManage_demo_account";
	// error_reporting(E_ALL ^ E_DEPRECATED);
	// error_reporting(0);
?>