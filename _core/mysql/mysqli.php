<?php

/*

    MySQLi support for SaguaroQL.

*/

require_once("saguaroql.php");

class SaguaroMySQLi extends SaguaroQL {
    function connect($host, $username, $password) {
        $this->connection = new mysqli($host, $username, $password);

        if (!$this->connection) {
            die(S_SQLCONF);
        }
    }

    function selectDatabase($database) {
        //Attempts to select the working database.
        if (!$this->connection->select_db(SQLDB)) {
            echo S_SQLDBSF;
        }
    }

    function escape_string($string) {
        if (!$this->connection)
            return 'Not connected to a SQL database, cannot escape.';

        return $this->connection->escape_string($string);
    }

    private function sanitizeString($string) {
        return $string;
    }

    function query($string) {
        $string = $this->sanitizeString($string);

        $ret = $this->connection->query($string);
        if (!$ret) {
            if (DEBUG_MODE) {
                echo "Error #" . $this->connection->error . " on query: " . $string . "<br>";
            } else {
                //echo "MySQL error!<br>";
            }
        }
        return $ret;
    }

    function result($string, $index = 0, $field = null) {
        //MySQLi has no compliment to mysql_result, so this is specifically for backwards compatability.
        $true = (is_resource($string)) ? $string : $this->query($string);
        $string->data_seek($true);
        if ($field == null) {
            return $true;
        } else {
            $datarow = $true->fetch_array();
            return $datarow[$true];
        }
    }

    function free_result($res) {
        return mysqli_free_result($res);
    }

    function fetch_row($string) {
        if (!$string) return $this->last;

        $true = is_resource($string) ? $string : $this->query($string);
        $this->last = $true->fetch_row();
        return $this->last;
    }

    function fetch_array($string) {
        if (!$string) return $this->last;

        $true = is_resource($string) ? $string : $this->query($string);
        $this->last = $true->fetch_array();
        return $this->last;
    }

    function fetch_assoc($string) {
        if (!$string) return $this->last;

        $true = is_resource($string) ? $string : $this->query($string);
        $this->last = $true->fetch_assoc();
        return $this->last;
    }

    function num_rows($string) {
        if (!$string) return $this->last->num_rows;

        $true = is_resource($string) ? $string : $this->query($string);
        $this->last = $true->num_rows;
        return $this->last;
    }

    function stats() {
        return mysqli_stat($this->connection);
    }
}

?>