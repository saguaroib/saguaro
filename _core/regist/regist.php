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
        $time = time();
        $tim  = $time . substr(microtime(), 2, 3);

        $info = [
            'post' => $this->extractForm(), //Get the post info.
            'host' => $_SERVER['REMOTE_ADDR'],
            'time' => $time,
            'local_name' => $tim,
            'board' => null
        ];
        //Get the file info and copy/rename to target directory.
        $info['files'] = (count($_FILES['upfile']['name']) > 0) ? $this->extractFiles($tim) : null;

        $this->cache = $info; //Copy to cache.

        $this->insert($this->cache); //Returns 'no' (post number), however this is also stored back in $this->cache['post']['number']
        //var_dump($this->cache);
        $this->updateCache();
    }

    private function cleanup($message) {
        error($message,$this->cache['file']['location']);
    }

    private function checkDuplicate($md5) {
        //Fix this.
        //If there is a file (hopefully), check the table for duplicates.
        global $mysql;

        if (1 == 2 && DUPE_CHECK) {
            $result = $mysql->query("select no,resto from " . SQLLOG . " where md5='$md5'");
            if ($mysql->num_rows($result)) {
                list($dupeno, $duperesto) = $mysql->fetch_row($result);
                if (!$duperesto)
                    $duperesto = $dupeno;
                $this->cleanup('<a href="' . DATA_SERVER . BOARD_DIR . "/" . RES_DIR . "/" . $duperesto . PHP_EXT . '#' . $dupeno . '">' . S_DUPE . '</a>');
            }
            $mysql->free_result($result);
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
            //'tim'     => $info['local_name'],
            'time'      => $info['time'],
            'pwd'       => $info['post']['password'],
            'sticky'    => $info['post']['special']['sticky'],
            'permasage' => $info['post']['special']['permasage'],
            'locked'    => $info['post']['special']['locked'],
            'resto'     => ($_POST['resto']) ? (int) $_POST['resto'] : 0,
            'board'     => BOARD_DIR
        ];

        $set = $this->dynamicBuild($data);
        $query = "insert into ".SQLLOG." (" . $set['keys'] . ") values (" . $set['vals'] .")";
        $mysql->query($query);
        $final = (int) $mysql->result('select last_insert_id()');
        $this->cache['post']['number'] = $final;
        /* if (!$result = ?) { echo E_REGFAILED; }*/

        //Files
        if ($info['files']) {
            $items = [];
            foreach($info['files'] as $file) {
                $out = [
                    'parent'       => $final,
                    'extension'    => "." . $file['original_extension'],
                    'width'        => $file['width'],
                    'height'       => $file['height'],
                    'localthumbname' => ($file['thumbnail']) ? $file['thumbnail']['filename'] : null,
                    'thumb_width'  => ($file['thumbnail']) ? $file['thumbnail']['width'] : null,
                    'thumb_height' => ($file['thumbnail']) ? $file['thumbnail']['height'] : null,
                    'hash'         => $file['md5'],
                    'filesize'     => $file['filesize'],
                    'filename'     => $file['original_name'],
                    'localname'    => $file['localname'],
                    'board' => BOARD_DIR
                ];

                $set = $this->dynamicBuild($out);
                $query = "insert into ".SQLMEDIA." (" . $set['keys'] . ") values (" . $set['vals'] .")";
                $mysql->query($query);

                array_push($items, $mysql->result('select last_insert_id()'));
            }
            $items = implode(" ", $items);


            //Update the medial column of the parent.
            $query = "update ".SQLLOG." set media='$items' where no=$final";
            $mysql->query($query);
        }

        return $final; //Return 'no', latest auto-incremented column.
    }

    private function dynamicBuild($set) {
        global $mysql;

        //Dynamically build the SQL command, numerous advantages.
        $keys = []; $vals = [];
        foreach($set as $column => $value) {
            array_push($keys,$column);

            //If the value is numeric (but may be a string) or a boolean, cast it to an integer. Otherwise wrap in doublequotes and escape it.
            array_push($vals,(is_numeric($value) || is_bool($value)) ? (int) $value : '"' . $mysql->escape_string($value) . '"');
        }
        $keys = implode(",",$keys);
        $vals = implode(",",$vals);

        return ['keys' => $keys, 'vals' => $vals];
    }

    private function updateCache() {
        global $mysql, $my_log;

        $child = $this->cache['post']['child'];
        $number = (int) $this->cache['post']['number'];
        $parent = (int) (!$child) ? $number : $this->cache['post']['parent'];
        $mysql->query("update " . SQLLOG . " set last=$number where no=$parent");

        //Initiate prune now that we're clear of all potential errors. Do this before rebuilding any pages!
        require_once("prune.php");
        prune_old(); //Does the page pruning

        if ($this->cache['post']['special']['sticky'] == 2)
            pruneThread($parent); //Event stickies.

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
            'special' => $this->sortSpecial(),
            'parent' => ($_POST['resto']) ? (int) $_POST['resto'] : 0
        ];

        //Basic sanitization.
        $sanitize = ['name','subject','email','comment'];
        foreach ($sanitize as $key) {
            $post[$key] = Sanitize::CleanStr($post[$key]);
        }

        $post['child'] = (bool) ($post['parent'] !== 0);
        $post['comment_md5'] = md5($post['comment']);

        require_once('tripcode.php');
        $post['name'] = Tripcode::format($post['name']);
        $post['name'] = ($post['special']['capcode']) ? Tripcode::adminify($post['name']) : $post['name'];

        //Apply user IDs, dice, EXIF etc to post.
        require_once("addons.php");
        $post['now'] = userID($post['now'], $post['email']);
        $post['comment'] = parseComment($post['comment'], $post['email']);

        return $post;
    }

    function extractFiles($tim) {
        $files = [];
        $f = $_FILES['upfile'];
        $num_files = count($f['name']);
        $max = min($num_files, MAX_FILE_COUNT); //Set the loop cap to the number of files or maximum allowed, whichever is lower.
        if ($num_files > MAX_FILE_COUNT) { //IF we want to impose the file limit and disregard the post itself, this is how we do it.
            //foreach ($f['fname'] as $key => $value) { unlink($value); } //By default, upload files should be in a temporary status and location which PHP should clean up on script completion.
            error(S_TOOMANYFILES);
        }

        //$this->checkDuplicate($info['file']['md5']);

        require_once('process/upload_file.php');

        for ($i = 0; $i < $max; $i++) {
            $temp = [
                'index'=> ($i + 1),
                'name' => $f['name'][$i],
                'type' => $f['type'][$i],
                'temp' => $f['tmp_name'][$i],
                'size' => $f['size'][$i],
                'tim'  => $tim
            ];

            $check = new ProcessFile;
            $temp = $check->run($temp);

            //If we made it this far, generate thumbnail if possible.
            $temp['thumbnail'] = (USE_THUMB) ? $this->generateThumbnail($temp['location']) : null;

            array_push($files, $temp);
        }

        return $files;
    }

    private function generateThumbnail($location) {
        require_once("thumb/thumb.php");
        require_once("process/image.php"); //Required for video thumbnail stats.

        $output = thumb($location, ($this->cache['post']['child']));
        if (!$output['location'] && $ext != ".pdf") {
            cleanup(S_UNUSUAL);
        }

        return $output;
    }

    private function sortSpecial() {
        if (valid('janitor')) {
            //Must leave int values as ints, bool values as bools
            $cap = (isset($_POST['showCap'])) ? true : false;
            $sticky = (isset($_POST['isSticky'])) ? true : false;
            $eventSticky = (isset($_POST['eventSticky'])) ? $sticky = 2 : false;
            $locked = (isset($_POST['isLocked'])) ? true : false;
        }

        return [
            'sticky' => $sticky,
            'locked' => $locked,
            'capcode' => $cap
        ];
    }
}

$regist = new Regist;
$regist->run();

?>