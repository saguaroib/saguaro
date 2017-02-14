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
            'board' => null,
            'errors' => []
        ];
        $this->cache = $info; //Copy to cache.

        //Get the file info and copy/rename to target directory, then append file info to post info cache.
        $this->cache['files'] = (count($_FILES['upfile']['name']) > 0) ? $this->extractFiles($tim) : null;

        $check = $this->postCheck();
        if ($check !== true) {
            error($check);
        }

        $this->insert($this->cache); //Returns 'no' (post number), however this is also stored back in $this->cache['post']['number']
        $this->updateCache();
    }

    private function cleanup($message) {
        error($message,$this->cache['file']['location']);
    }

    private function insert($info) {
        global $mysql;

        $resto = ($_POST['resto']) ? (int) $_POST['resto'] : 0;
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
            'capcode'   => $info['post']['capcode'],
            'id'        => $info['post']['id'],
            'tripcode'  => $info['post']['tripcode'],
            'country'   => $info['post']['country'],
            'resto'     => $resto,
            'board'     => BOARD_DIR
        ];

        $set = $this->dynamicBuild($data);
        $query = "insert into ".SQLLOG." (" . $set['keys'] . ") values (" . $set['vals'] .")";
        $mysql->query($query);

        $final = (int) $mysql->result('select last_insert_id()');

        $last_modified = ($resto) ? $resto : $final;
        $mysql->query("UPDATE " . SQLLOG . " SET last_modified='{$info['time']}' WHERE no='{$last_modified}' AND board='" . BOARD_DIR . "'");        

        $this->cache['post']['number'] = $final;
        /* if (!$result = ?) { echo E_REGFAILED; }*/

        //Files
        if ($info['files']) {
            $items = [];
            foreach($info['files'] as $file) {
                $out = [
                    'parent'       => $final,
                    'resto'        => $resto,
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
                $mysql->query($query); //Disable this to skip writing to the media table.

                //array_push($items, $mysql->result('select last_insert_id()')); //Old behavior of just pushing out the media row numbers.
                unset($out['parent'], $out['resto'], $out['board']); //Delete unnecessary keys.
                array_push($items, $out); //Push.
            }
            //$items = implode(" ", $items); //Old behavior of joining the media row numbers.
            $items = json_encode($items);

            //Update the media column of the parent.
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
        require_once("inc/prune.php");
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
        require_once('inc/process/upload.php');
        $check = new UploadCheck;
        $check->run();
    }

    function postCheck() {
        if (max($this->cache['files'], strlen($this->cache['post']['comment'])) == 0) {
            $out = "No comment or acceptable file included.";
            foreach($this->cache['errors'] as $error) {
                $out .= '<br><small>'.$error.'</small>';
            }
            return $out;
        }
        return true;
    }

    function extractForm() {
        require_once('inc/sanitize.php'); //Load sanitation class.
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
            'parent' => ($_POST['resto']) ? (int) $_POST['resto'] : 0
        ];

        //Basic sanitization.
        $moderator = valid("moderator");
        $saniCls = new Sanitize;
        $sanitize = $saniCls->process($post, $moderator); //['name','subject','email','comment'];
        foreach ($sanitize as $key => $value) {
            $post[$key] = $value;
        }

        $post['child'] = (bool) ($post['parent'] !== 0);
        $post['comment_md5'] = md5($post['comment']);

        //Apply user IDs, dice, EXIF etc to post..
        require_once("inc/addons.php");
        $addonsClass = new SaguaroRegistExtras;
        $post = $addonsClass->init($post);

        return $post;
    }

    function extractFiles($tim) {
        $files = [];
        $f = $_FILES['upfile'];
        $num_files = count($f['name']);
        $this->cache['filecount'] = (int) $num_files;
        $max = min($num_files, MAX_FILE_COUNT); //Set the loop cap to the number of files or maximum allowed, whichever is lower.
        if ($num_files > MAX_FILE_COUNT) { //IF we want to impose the file limit and disregard the post itself, this is how we do it.
            //foreach ($f['fname'] as $key => $value) { unlink($value); } //By default, upload files should be in a temporary status and location which PHP should clean up on script completion.
            error(S_TOOMANYFILES);
        }

        //$this->checkDuplicate($info['file']['md5']);

        require_once('inc/process/upload_file.php');
        $index = 1; //File counter index.

        for ($i = 0; $i < $max; $i++) {
            $temp = [
                'index'=> $index,
                'name' => $f['name'][$i],
                'type' => $f['type'][$i],
                'temp' => $f['tmp_name'][$i],
                'size' => $f['size'][$i],
                'error' => $f['error'][$i],
                'tim'  => $tim
            ];

            $check = new ProcessFile;
            $temp = $check->run($temp);

            if ($temp['passCheck'] === true) {
                //If we made it this far, generate thumbnail if possible.
                $temp['thumbnail'] = (USE_THUMB) ? $this->generateThumbnail($temp['location']) : null;

                array_push($files, $temp);
                $index++;
            } else {
                //What to do if an individual file fails a check.
                //Fortunately, failed checks leave the files in the temporary area which PHP cleans up itself upon script completion.
                //error($temp['message']);
                if ($temp['message']) {
                    array_push($this->cache['errors'],$temp['message']);
                }
            }
        }

        return (count($files) > 0) ? $files : null;
    }

    private function generateThumbnail($location) {
        require_once("inc/thumb/thumb.php");
        require_once("inc/process/image.php"); //Required for video thumbnail stats.

        $output = thumb($location, ($this->cache['post']['child']), $this->cache['filecount']);
        if (!$output['location'] && $ext != ".pdf") {
            cleanup(S_UNUSUAL);
        }

        return $output;
    }
}

$regist = new Regist;
$regist->run();