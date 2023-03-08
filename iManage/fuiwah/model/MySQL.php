<?php
/**
 * Copyright (c) 2019. Julfikar Mahmud
 * @author Md Julfikar Mahmud
 * @url https://medium.com/@md.julfikar.mahmud/php-mysql-object-oriented-programming-oop-e88a3dedbae
 */
class MySQL {

    protected $host, $user, $password, $database;

    public $connection;
    public $LastInsertedIds     = array();
    public $ConnectionLastId    = NULL;
    public $error               = array();

    /**
     * @inheritDoc
     * @param bool $openConnection default true
     */
    public function __construct($config,$openConnection = true)
    {
        $this->debug();

        if(!isset($this->connection)){
            try {
                if($openConnection){
                    $this->Connect($config);
                }
            } catch (Exception $e) {
                $this->setError($e);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        $this->Close();
    }

    /**
     * Close MySQL connection
     */
    public function Close(){
        $this->host         = NULL;
        $this->user         = NULL;
        $this->password     = NULL;
        $this->database     = NULL;

        mysqli_close($this->connection);
    }

    /**
     * @param bool $error_enabled
     */
    public function debug($error_enabled = false)
    {
        if(!$error_enabled){
            error_reporting(E_ALL ^ E_DEPRECATED);
            error_reporting(0);
        }else{
            error_reporting(1);
        }
    }


    /**
     * Connect with mysql
     * @throws Exception
     */
    public function Connect($config){
        try{
            $this->host         = isset($config['host']) ? $config['host'] : 'easysales.asia';
            $this->user         = $config['user'];
            $this->password     = $config['password'];
            $this->database     = $config['db'];

            $this->connection = mysqli_connect($this->host,$this->user,$this->password,$this->database);
            if(!$this->connection){
                return false;
            }
            return $this->connection;

        }catch(Exception $e){
            throw new Exception(__METHOD__.$e);
        }
    }

    /**
     * @return mysql connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param $inp string
     * @return array|mixed
     */
    public static function sanitize($inp) {
        if(is_array($inp)){
            return array_map(__METHOD__, $inp);
        }
        if(!empty($inp) && is_string($inp)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
        }
        return $inp;
    }

    /**
     * @param $table
     * @param int $numOfColumns
     * @return array
     */
    public function LastInsertedIdsByTable($table, $numOfColumns = 1){
        $sql                    = "SHOW KEYS FROM login WHERE Key_name = 'PRIMARY'";
        $columns                = $this->Execute($sql);
        $column_name            = $columns[0]['Column_name'];
        $sql                    = "SELECT " . $column_name . " FROM " . $table . " ORDER BY " . $column_name . " DESC LIMIT " . $numOfColumns;
        $this->LastInsertedIds  = $this->Execute($sql);

        return $this->LastInsertedIds;
    }

    /**
     * @return int |null
     */
    public function ConnectionLastInsertId(){
        $sql                    = "SELECT LAST_INSERT_ID() as id";
        $this->ConnectionLastId = $this->Execute($sql);
        $this->ConnectionLastId = $this->ConnectionLastId[0]['id'];

        return $this->ConnectionLastId;
    }

    /**
     * @param $query
     * @return bool|mysqli_result
     */
    protected function Query($query){
        if($this->CheckConnection() === false){
            return false;
        }
        $execute            = mysqli_query($this->connection,$query);
        if(!$execute){
            $e              = 'MySQL query error '.mysqli_error($this->connection);
            $this->setError($e);
        }
        return $execute;
    }

    /**
     * @return bool
     */
    protected function CheckConnection(){
        if(! $this->connection){
            $e              = 'DB connection failed';
            $this->setError($e);
            return false;
        }
        return true;
    }

    /**
     * @return int
     */
    public function AffectedRows(){
        return mysqli_affected_rows($this->connection);
    }

    /**
     * @param $query
     * @return array|bool
     */
    public function Execute($query){
        if($this->CheckConnection() === false){
            return false;
        }
        $return             = array();
        $execute = $this->Query($query);
        if($execute === false){
            $e = 'MySQL query error '.mysqli_error($this->connection);
            $this->setError($e);
            return false;
        }
        if(!is_bool($execute)){
            while($row = mysqli_fetch_array($execute)){
                $return[]   = $row;
            }
        }else{
            return $execute;
        }
        return $this->ReactNativeSafeArray($return);
    }

    /**
     * @param $error
     */
    protected function setError($error){
        array_push($this->error,$error);
    }

    /**
     * @return mixed
     */
    public function error(){
        return $this->error[count($this->error)-1];
    }

    /**
     * @param $result
     * @return bool
     */
    public function isValid($result){
        return (is_array($result) && count($result) > 0);
    }

    protected function ReactNativeSafeArray($arr){
        for ($i=0; $i < count($arr); $i++) { 
            $obj = $arr[$i];
            foreach ($obj as $key => $value) {
                if (is_int($key)) {
                    unset($obj[$key]);
                }
            }
            $arr[$i] = $obj;
        }
        return $arr;
    }
}