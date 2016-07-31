<?php

class SaguaroDelete {
    //Receive & process POST info submitted from checkbox deletion method
    public function userDel() {
        global $my_log, $mysql, $host;
        
        $pwdc = (isset($_COOKIE['saguaro_pwdc']) && !empty($_COOKIE['saguaro_pwdc'])) ? $mysql->escape_string($_COOKIE['saguaro_pwdc']) : false;
        $imgonly = ($_POST['onlyimgdel'] == "on") ? true : false;
        $pwd = (!empty($_POST['pwd'])) ? $mysql->escape_string($_POST['pwd']) : $pwdc;
        $delno        = array();
        $rebuildindex = !(defined("STATIC_REBUILD") && STATIC_REBUILD);
        $resnum = (is_numeric($_POST['resnum'])) ? (int) $_POST['resnum'] : null;
        
        reset($_POST);
        while ($item = each($_POST)) {
            if ($item[1] == 'delete') {
                array_push($delno, $item[0]);
            }
        }
        
        $my_log->update_cache(1);
        
        $countdel = count($delno);
        $rebuild  = array(); //Keys are pages to be rebuilt, 0 is the index
        for ($i = 0; $i < $countdel; $i++) {
            $resto = $this->delete_post($delno[$i], $pwd, $imgonly, 0, 1, $countdel == 1); //Only show error for single post/user deletion, not multipost deletion
            if ($resto)
                $rebuild[$resto] = 1;
        }

        foreach ($rebuild as $key => $val) {
            if (ENABLE_API) {
                require_once(CORE_DIR . "/api/apoi.php");
                $api = new SaguaroAPI;
                $api->formatThread($key, 0); //Update .json files to reflect deleted posts
            }
            $my_log->update($key, 1); //Leaving second parameter as 0 rebuilds the index each time!
        }
        if ($rebuildindex)
            $my_log->update(0, 1); //Update indexes after all deletions are done

        //If posts were deleted from a thread and the thread still exists, redirect the user to that same thread.
        $redir = (!empty($my_log->cache[$resnum])) ? RES_DIR . $resnum . PHP_EXT : "//" . SITE_ROOT_BD; 
        header("Location: $redir");
    }

    //Deletion handler, accepts one post at a time.
    private function delete_post($no, $pwd, $imgonly = 0, $automatic = 0, $children = 1, $die = 1) {
        global $path, $mysql, $host, $my_log;

        $no = (int) $no;

        if (empty($my_log->cache[$no])) //Does post exist?
            if ($die) error(S_NODELPOST . $no);

        $row = $my_log->cache[$no];
        //Check password. If no password, check admin status
        $delete_ok = ($automatic || (substr(md5($pwd), 2, 8) == $row['pwd']) || ($row['host'] == $host));
        if (valid('janitor') && !$automatic)
            $admindel = true;//valid('delete');

        if (!$delete_ok && !$admindel)
            error(S_BADDELPASS);

        if ($admindel) { //Any actions for staff deletions
            $auser   = $mysql->escape_string($_COOKIE['saguaro_auser']);
            $mysql->query("INSERT INTO " . SQLDELLOG . " (admin, type, action, time, board, postno) VALUES ('{$auser}', '2', 'Deleted post #{$no}', '" . time() . "', '" . BOARD_DIR . "', '{$no}')");
        }
        
        $conditions = ($row['resto'] == 0 && $children && !$imgonly) ? "(no='{$no}' OR resto='{$no}')" : "no='{$no}'"; 
        $result = $mysql->query("SELECT no,resto,tim,ext FROM " . SQLLOG . " WHERE {$conditions} AND board='" . BOARD_DIR . "'");

        while ($delrow = $mysql->fetch_assoc($result)) { //This does the resource deletions
            $this->deleteFile($delrow, $imgonly); //Delete all associated media.
            $this->deleteResources($delrow, $imgonly); //Delete API, HTML page if necessary
        }

        //Remove posts from the database
        $mysql->query("DELETE FROM " . SQLLOG . " WHERE {$conditions} AND board='" . BOARD_DIR . "'");
        
        return $row['resto']; // so the caller can know what pages need to be rebuilt
    }

    //Deletes all media associated with post.
    private function deleteFile($post, $imgonly = false) {
        global $mysql;

        $path = realpath("./") . '/' . IMG_DIR;
        $delfile  = $path . $post['tim'] . $post['ext']; //Path to file
        $delthumb = THUMB_DIR . $post['tim'] . 's.jpg';//Path to thumbnail
        
        if ($imgonly) {
            $mysql->query("UPDATE " . SQLLOG . " SET fsize='-1' WHERE no='$no' and board='" . BOARD_DIR ."'");
        }
        if ($post['embed']) { //Unset embed
            $mysql->query("UPDATE " . SQLLOG . " SET embed='deleted' WHERE no='$no'");
        }
        if (is_file($delfile)) {
            unlink($delfile); //Delete image
        }
        if (is_file($delthumb)) {
            unlink($delthumb); //Delete thumbnail
        }
        if (OEKAKI_BOARD == 1 && is_file($path . $post['tim'] . '.pch')) {
            unlink($path . $post['tim'] . '.pch'); // delete oe animation
        }
    }

    //Delete API files, thread HTML, any other associated files
    private function deleteResources($post, $imgonly) {
        global $mysql, $my_log;
        if ($imgonly)
            return true;
        if ($post['resto']) {
            unset($my_log->cache[$post['resto']]['children'][$post['no']]);
        }
        unset($my_log->cache[$post['no']]);
        
        if (API_ENABLED) {
            @unlink(API_DIR_RES . $post['no'] . ".json"); //Delete API json. Catalog/threads/index files are rebuilt later anyway.
        } 
        $my_log->cache['THREADS'] = array_diff($my_log->cache['THREADS'], array($post['no'])); //Remove from THREADS array
        $mysql->query("DELETE FROM " . SQLREPORTS . " WHERE no='{$post['no']}'"); //Clear associated reports
        if (USE_GZIP) {
            @unlink(RES_DIR . $post['no'] . PHP_EXT . '.gz');
        }
        @unlink(RES_DIR . $post['no'] . PHP_EXT);
    }
}
