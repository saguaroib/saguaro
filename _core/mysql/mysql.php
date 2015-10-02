<?php

/*

    SaguaroQL - because it's a cool name.

    Centralized MySQL-handling class.

*/

class SaguaroQL {
    public $connection;

    function init() {
        $this->connect(SQLHOST, SQLUSER, SQLPASS);
        $this->selectDatabase(SQLDB);
    }
    
    private function sanitizeString($string) {
        return $string;
    }
    
    function query($string) {
        $string = $this->sanitizeString($string);
        
        $ret = mysql_query($string);
        if (!$ret) {
            if (DEBUG_MODE) {
                echo "Error #" . mysql_error() . " on query: " . $string . "<br>";
            } else {
                echo "MySQL error!<br>";
            }
        }
        return $ret;
    }
    
    function fetch_array_query($string) {      
        return mysql_fetch_array($this->query($string));
    }

    private function connect($host, $username, $password) {
        $this->connection = mysql_connect($host, $username, $password);

        if (!$this->connection) {
            die(S_SQLCONF);
        }
    }

    private function selectDatabase($database) {
        //Attempts to select the working database.
        if (!mysql_select_db(SQLDB, $this->connection)) {
            echo S_SQLDBSF;
        }
    }
}

?>