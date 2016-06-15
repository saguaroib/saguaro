<?php

/*
    Board loading class used for multiple boards spread out over a handful of tables.
    Lengthy explanation of this garbage coming soon on the wiki. 
    Won't affect most users.
*/


class BoardLoader {
    
    public function getTable() {
        global $mysql;
        if (!defined('SQLLOG')) {
            $table = $mysql->result("SELECT `table` FROM " . SQLTABLES . " WHERE board='" . BOARD_DIR . "'");
            if (!$table) 
                error(S_SQLFAIL);
            $authed = $mysql->num_rows("SHOW TABLES LIKE '{$table}' ");

            if ($authed == 1) {
                define('SQLLOG', $table);
            } else {
                error(S_SQLCONF);
            } 
        }
    }
    
    /*public function getConfig() {
        $board = explode("/", $_GET['b']); //WEW
        require_once($board[0] . "/config.php");
    }*/
}