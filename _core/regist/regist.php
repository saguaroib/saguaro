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

        $this->insert($this->cache); //Returns 'no' (post number), however this is also stored back in $this->cache['post']['number']
        //var_dump($this->cache);
        $this->updateCache();
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
            require_once("process/image.php"); //Required for video thumbnail stats.
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
            'now'       => $info['post']['now'],
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
        $mysql->query($query);
        $final = (int) $mysql->result('select last_insert_id()');
        /* if (!$result = ?) { echo E_REGFAILED; }*/

        $this->cache['post']['number'] = $final;
        return $final; //Return 'no', latest auto-incremented column.
    }

    private function updateCache() {
        global $mysql, $my_log;

        $child = $this->cache['post']['child'];
        $number = (int) $this->cache['post']['number'];
        $parent = (int) (!$child) ? $number : $this->cache['post']['parent'];
        $mysql->query("update " . SQLLOG . " set last=$number where no=$parent");

        //Run update process.
        $static_rebuild = defined("STATIC_REBUILD") && (STATIC_REBUILD == 1);
        $target = ($child) ? $parent : $number;
        $my_log->update($target, $static_rebuild);

        //Auto-noko.
        $url = DATA_SERVER . BOARD_DIR . "/" . RES_DIR;
        $target = $url . $target . PHP_EXT . (($child) ? "#$number" : "");
        echo "<html><head><meta http-equiv='refresh' content='60;URL=$target'></head><body><a href='$target'>Redirecting...</a></body></html>";
        //echo "<body>" . S_SCRCHANGE . "</body></html>";
        header("Location: $target"); /* Redirect browser */
        exit();
    }

    function initialCheck() {
        require_once('process/upload.php');
        $check = new UploadCheck;
        $check->run();
    }

    function extractForm() {
        require_once('sanitize.php'); //Load sanitation class.
        //Default post information.
        srand((double) microtime() * 1000000); //Seed the RNG.

        $time = time();
        $day = [S_SUN,S_MON,S_TUE,S_WED,S_THU,S_FRI,S_SAT]; $day = $day[date("w")];

        $post = [
            'name' => (FORCED_ANON == false && $_POST['name']) ? $_POST['name'] : S_ANONAME,
            'subject' => (FORCED_ANON == false && $_POST['sub']) ? $_POST['sub'] : S_ANOTITLE,
            'email' => ($_POST['email']) ? $_POST['email'] : "",
            'comment' => ($_POST['com']) ? $_POST['com'] : S_ANOTEXT,
            'password' => ($_POST['pwd'] !== "") ? substr($_POST['pwd'],0,8) : ($_COOKIE['saguaro_pass']) ? $_COOKIE['saguaro_pass'] : substr(md5(rand()),0,8), //Get and/or supply deletion password.
            'now' => date("m/d/y", $time) . "(" . (string) $day . ")" . date("H:i:s", $time),
            'special' => [
                'sticky' => false,
                'locked' => false,
                'permasage' => false
            ],
            'parent' => ($_POST['resto']) ? (int) $_POST['resto'] : 0
        ];
        
        //Basic sanitization.
        $sanitize = ['name','subject','email','comment'];
        foreach ($sanitize as $key) {
            $post[$key] = Sanitize::CleanStr($post[$key]);
        }

        $post['child'] = (bool) ($post['parent'] !== 0);
        $post['comment_md5'] = md5($post['comment']);

        //Apply trip/capcodes, user IDs, dice, fortune, etc to post.
        /*require_once("addons.php");
        $post = parseAddons($post);*/ //Wrong. Also currently has the indirect benefit of blowing up the entire variable.

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