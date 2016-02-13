<?php

/*

    Regist

    The most functionally important and hair removing part of the board software.
    Handles posting and uploads.

    Security for the future:
        MD5 hash the comment and use for checking duplicates (store otherwise).

    This file's structure and functions may change drastically over time.
    The initial idea is to get it functional and easier to manage.

*/

class Regist {
    private $cache = [];

    function run() {
        $this->initialCheck(); //Run preliminary checks.

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
        $this->cache = $info; //Copy to cache.

        if ($info['file']) {
            $this->checkDuplicate($info['file']['md5']);
            $this->generateThumbnail(); //If we made it this far, generate the thumbnail.
        }
        var_dump($this->cache);
        $this->insert($this->cache);
        //Update the log and cache files.
    }

    private function cleanup($message) {
        error($message,$this->cache['file']['location']);
    }

    private function checkDuplicate($md5) {
        //If there is a file (hopefully), check the table for duplicates.
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

    private function generateThumbnail() {
        if (USE_THUMB) {
            require_once("thumb/thumb.php");
            $output = thumb($this->cache['file']['location'], ($this->cache['post']['child']));
            if (!$output['location'] && $ext != ".pdf") {
                cleanup(S_UNUSUAL);
            }
            $this->cache['file']['thumbnail'] = $output;
        } else {

        }
    }

    private function insert($info) {
        global $mysql;

        $data = [ //Ironically aligned to "permasage".
            'now'       => 0,
            'name'      => $info['post']['name'],
            'email'     => $info['post']['email'],
            'sub'       => $info['post']['subject'],
            'com'       => $info['post']['comment'],
            'host'      => $info['host'],
            'pwd'       => $info['post']['password'],
            'ext'       => "." . $info['file']['original_extension'],
            'w'         => $info['file']['width'],
            'h'         => $info['file']['height'],
            'tn_w'      => $info['file']['thumbnail']['width'],
            'tn_h'      => $info['file']['thumbnail']['height'],
            'tim'       => $info['local_name'],
            'time'      => $info['time'],
            'md5'       => $info['file']['md5'],
            'fsize'     => $info['file']['filesize'],
            'fname'     => $info['file']['original_name'],
            'sticky'    => $info['post']['special']['sticky'],
            'permasage' => $info['post']['special']['permasage'],
            'locked'    => $info['post']['special']['locked'],
            'root'      => 0,
            'resto'     => ($_POST['resto']) ? (int) $_POST['resto'] : 0
        ];

        //Dynamically build the SQL command, numerous advantages.
        $keys = []; $vals = [];
        foreach($data as $column => $value) {
            array_push($keys,$column);

            //If the value is numeric (but may be a string) or a boolean, cast it to an integer. Otherwise wrap in doublequotes and escape it.
            array_push($vals,(is_numeric($value) || is_bool($value)) ? (int) $value : '"' . $mysql->escape_string($value) . '"');
        }
        $keys = implode(",",$keys);
        $vals = implode(",",$vals);

        $query = "insert into ".SQLLOG." ($keys) values ($vals)";
        if (!$result = $mysql->query($query)) {
            echo E_REGFAILED;
        }

        //Rebuild and log stuff.
        $this->updateCache();
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
            'password' => ($_POST['pwd'] !== "") ? substr($_POST['pwd'],0,8) : ($_COOKIE['saguaro_pass']) ? $_COOKIE['saguaro_pass'] : substr(md5(rand()),0,8), //Get and/or supply deletion password.
            'special' => [
                'sticky' => false,
                'locked' => false,
                'permasage' => false
            ],
            'parent' => ($_POST['resto']) ? (int) $_POST['resto'] : 0
        ];

        $post['child'] = (bool) ($post['parent'] !== 0);
        $post['comment_md5'] = md5($post['comment']);

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