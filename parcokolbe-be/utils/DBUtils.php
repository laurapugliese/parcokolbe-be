<?php
include "../config.php";

class DBUtils {
 
    private $host;
    private $user;
    private $pass;
    private $database;
    private $port;
    private $connection;

    function __construct() {
    	global $dbhost, $dbuser, $dbpassword, $database, $dbport;
        $this->host = $dbhost;
        $this->user = $dbuser;
        $this->pass = $dbpassword;
        $this->port = $dbport;
        $this->database = $database;
        $this->connect();
    }
 
    private function disconnect() {
        @mysql_close($this->connection);
    }
 
    private function connect() {
    	$this->connection = new mysqli($this->host, $this->user, $this->pass, $this->database, $this->port);
    	if ($this->connection->connect_error) {
			die('Connect Error (' . $this->connection->connect_errno . ') ' . $this->connection->connect_error);
		}
    }

    public function execute_query($query) {
 		$array = null;
    	$result = mysqli_query($this->connection, $query);
    	if ($result !== false) {
    		while ( $row = mysqli_fetch_assoc($result)){
    			$array[] = $row;
    		}
    		mysqli_free_result($result);
    	}
    	$this->disconnect();
    	return $array;
    }
    
    public function execute_insert_query($query) {
    	$result = mysqli_query($this->connection, $query);
    	if ($result === false) {
    		$this->disconnect();
    		return $result;
    	}
    	$id = mysqli_insert_id($this->connection);
    	$this->disconnect();
    	return $id;
    }
 
}
?>