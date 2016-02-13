<?php

/*

    Regist

    The most functionally important and hair removing part of the board software.
    Handles posting and uploads.

    Security for the future:
        MD5 hash the comment and use for checking duplicates (store otherwise).

*/

class Regist {
    private $cache = [];
    
    function run() {
        $this->initialCheck(); //Run prelimary checks.

        $file = $_FILES["upfile"]["tmp_name"];
        $time = time(); $tim  = $time . substr(microtime(), 2, 3);
        $info = [ 
            'post' => $this->extractForm(), //Get the post info.
            'file' => (is_uploaded_file($file)) ? $this->extractFile($file,$tim) : false, //Get the file info and copy/rename to target directory.
            'host' => $_SERVER['REMOTE_ADDR'],
            'time' => $time,
            'local_name' => $tim,
            'board' => null
        ];
        
        var_dump($info);
        $this->cache = $info; //Copy to cache.
        
        if ($info['file'])
            $this->checkDuplicate($info['file']['md5']);
        
        //$this->insert($info);
        //Update the log and cache files.
    }
    
    private function cleanup($message) {
        error($message,$this->cache['file']['location']);
    }
    
    private function checkDuplicate($md5) {
        //If there is a file, check the table for duplicates.
        global $mysql;

        if (DUPE_CHECK) {
            $result = $mysql->query("select no,resto from " . SQLLOG . " where md5='$md5'");
            if ($mysql->num_rows($result)) {
                list($dupeno, $duperesto) = $mysql->fetch_row($result);
                if (!$duperesto)
                    $duperesto = $dupeno;
                $this->cleanup('<a href="' . DATA_SERVER . BOARD_DIR . "/res/" . $duperesto . PHP_EXT . '#' . $dupeno . '">' . S_DUPE . '</a>');
            }
            $mysql->free_result($result);
        }
    }
    
    private function insert($info) {
        global $mysql;
        $query = "INSERT INTO {SQLLOG} () VALUES ()";
    }
    
    private function updateCache() {
        
    }

    function initialCheck() {
        require_once('process/upload.php');
        $check = new UploadCheck;
        $check->run();
    }

    function extractForm() {
        //Default post information.
        srand((double) microtime() * 1000000); //Seed the RNG.

        $post = [
            'name' => (FORCED_ANON == false && $_POST['name']) ? $_POST['name'] : S_ANONAME,
            'subject' => (FORCED_ANON == false && $_POST['sub']) ? $_POST['sub'] : S_ANOTITLE,
            'email' => ($_POST['email']) ? $_POST['email'] : "",
            'comment' => ($_POST['com']) ? $_POST['com'] : S_ANOTEXT,
            'parent' => ($_POST['resto']) ? (int) $_POST['resto'] : 0,
            'password' => ($_POST['pwd'] !== "") ? substr($_POST['pwd'],0,8) : ($_COOKIE['saguaro_pass']) ? $_COOKIE['saguaro_pass'] : substr(md5(rand()),0,8), //Get and/or supply deletion password.
            'special' => [
                'sticky' => false,
                'locked' => false,
                'permasage' => false
            ]
        ];

        //Apply trip/capcodes to $post['name'].
        //All other magic required here.

        return $post;
    }
    
    function extractFile($file,$tim) {
        //Need the file's width/height, MD5 hash, and size.
        //Determine the file type and load the appropriate processor.

        require_once('process/upload_file.php');
        $check = new ProcessFile;
        return $check->run($file,$tim);
    }
}

$regist = new Regist;
$regist->run();

?>