<?php

/*

    Legacy/deprecated MySQL support for SaguaroQL.

*/

require_once("saguaroql.php");

class SaguaroMySQL extends SaguaroQL {
    function connect($host, $username, $password) {
        $this->connection = mysql_connect($host, $username, $password);

        if (!$this->connection) {
            die(S_SQLCONF);
        }
    }

    function selectDatabase($database) {
        //Attempts to select the working database.
        if (!mysql_select_db(SQLDB, $this->connection)) {
            echo S_SQLDBSF;
        }
    }

    function escape_string($string) {
        return mysql_real_escape_string($string);
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
                //echo "MySQL error!<br>";
            }
        }
        return $ret;
    }

    function result($string, $index = 0, $field = null) {
        $true = (is_resource($string)) ? $string : $this->query($string);
        if ($field == null)
            return mysql_result($true, $index);
        else
            return mysql_result($true, $index, $field);
    }

    function free_result($res) {
        return mysql_free_result($res);
    }

    function fetch_row($string) {
        if (!$string) return $this->last;

        $true = (is_resource($string)) ? $string : $this->query($string);
        $this->last = mysql_fetch_row($true);
        return $this->last;
    }

    function fetch_array($string) {
        if (!$string) return $this->last;

        $true = (is_resource($string)) ? $string : $this->query($string);
        $this->last = mysql_fetch_array($true);
        return $this->last;
    }

    function fetch_assoc($string) {
        if (!$string) return $this->last;

        $true = (is_resource($string)) ? $string : $this->query($string);
        $this->last = mysql_fetch_assoc($true);
        return $this->last;
    }

    function num_rows($string) {
        if (!$string) return mysql_num_rows($this->last);

        $true = (is_resource($string)) ? $string : $this->query($string);
        $this->last = mysql_num_rows($true);
        return $this->last;
    }
}

?>