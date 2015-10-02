<?php

/*

    SaguaroQL - because it's a cool name.

    Centralized MySQL-handling class.

*/

class SaguaroQL {
    public $connection;
    private $last; //This is updated after an advanced query (anything other than 'query') to cache the result. Calls without arguments will return this.
    
    /*function __construct() {
        $this->init(); //Init automatically.
    }*/

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
    
    function result($string, $index = 0) {
        return mysql_result($this->query($string), $index);
    }

    function fetch_row($string) {
        if (!$string) return $this->last;
        
        $this->last = mysql_fetch_row($this->query($string));
        return $this->last;
    }

    function fetch_array($string) {
        if (!$string) return $this->last;

        $this->last = mysql_fetch_array($this->query($string));
        return $this->last;
    }
    
    function num_rows($string) {
        if (!$string) return mysql_num_rows($this->last);
        
        $this->last = mysql_num_rows($this->query($string));
        return $this->last;
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