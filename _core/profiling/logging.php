<?php

/*
    Saguaro profiling and logging class.
    Writes to the SQL log, which currently has no front end way to read.
    Use logme($message) available from the root in imgboard.php for quick access, or call the class elsewhere.
    
    require_once(CORE_DIR . "/profiling/logging.php");
    $profile = new Profiling;
    $profile->log($message);
*/


class Profiling {

    public function log($message) { //Message for the system log.
        global $mysql;  static $run = -1;

        if ($run == -1) {
            $run = getmypid(); // rand(0,16777215);
            if (PROCESSLIST) {
                register_shutdown_function( function() {
                    $this->endLog($id); 
                }, $run);
                $dump = $mysql->escape_string(serialize(array(
                    'GET' => $_GET,
                    'POST' => $_POST,
                    'SERVER' => $_SERVER
                )));
                $mysql->query("INSERT INTO " . SQLPROFILING . "  VALUES ('$run','$dump','')");
            }
        }
        if (PROCESSLIST) {
            $mysql->query("UPDATE " . SQLPROFILING . "  SET descr='$desc' WHERE id='$run'");
            $board = BOARD_DIR;
            $time  = microtime(true);
            $mysql->query("INSERT INTO " . SQLPROFILING2 . " VALUES ('$board',$run,$time,'$desc')");
        }
    }
    
    //Shutdown function, currently unused anywhere. shrug
    public function endLog($id) {
        global $mysql;
        $this->log('Done');
        if (CLEAR_ON_SUCCESS) $mysql->query("DELETE FROM " . SQLPROFILING . "  WHERE id='$id'");
    }

}