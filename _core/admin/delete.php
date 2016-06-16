<?php

require_once(CORE_DIR . "/log/log.php");

class Delete extends Log {
    function userDel() {
        global $mysql, $host;
        
        $pwdc = (isset($_COOKIE['saguaro_pwdc']) && !empty($_COOKIE['saguaro_pwdc'])) ? $mysql->escape_string($_COOKIE['saguaro_pwdc']) : false;
        $imgonly = ($_POST['onlyimgdel'] == "on") ? true : false;
        $pwd = (!empty($_POST['pwd'])) ? $mysql->escape_string($_POST['pwd']) : $pwdc;
        $delno        = array();
        $rebuildindex = !(defined("STATIC_REBUILD") && STATIC_REBUILD);
        $delflag      = FALSE;
        $resnum = (int) $_POST['threadno'];

        reset($_POST);
        while ($item = each($_POST)) {
            if ($item[1] == 'delete') {
                array_push($delno, $item[0]);
                $delflag = TRUE;
            }
        }
        
        $this->update_cache(1);
        
        $countdel = count($delno);
        $rebuild  = array(); // keys are pages that need to be rebuilt (0 is index, of course)
        for ($i = 0; $i < $countdel; $i++) {
            $resto = $this->targeted($delno[$i], $pwd, $imgonly, 0, 1, $countdel == 1); // only show error for user deletion, not multi
            if ($resto)
                $rebuild[$resto] = 1;
        }
        
        $log = $this->cache;

        foreach ($rebuild as $key => $val) {
            if (ENABLE_API) {
                require_once(CORE_DIR . "/api/apoi.php");
                $api = new SaguaroAPI;
                $api->formatThread($key, 0); //Update .json files to reflect deleted posts
            }
            $this->update($key, 1); // leaving the second parameter as 0 rebuilds the index each time!
        }
        if ($rebuildindex)
            $this->update(0, 1); // update the index page last
        

        $redir = (isset($resnum) && isset($log[$resnum])) ? RES_DIR . $resnum . PHP_EXT : PHP_SELF2_ABS; //Thread was deleted, redirect to index
        
        echo "<META HTTP-EQUIV='refresh' content='0;URL={$redir}'>";
    }

    function targeted($no, $pwd, $imgonly = 0, $automatic = 0, $children = 1, $die = 1, $delhost = '') {
        global $path, $mysql, $host;

        $no = (int) $no;

        if (empty($this->cache[$no])) //Does post exist?
            if ($die) error(S_NODELPOST . $no);

        $row = $this->cache[$no];
        //Check password. If no password, check admin status
        $delete_ok = ($automatic || (substr(md5($pwd), 2, 8) == $row['pwd']) || ($row['host'] == $host));
        if (valid('janitor') && !$automatic)
            $admindel = valid('delete');

        if (!$delete_ok && !$admindel)
            error(S_BADDELPASS);

        if ($admindel) { //Actions for staff go here
            $auser   = $mysql->escape_string($_COOKIE['saguaro_auser']);
            $adfsize = ($row['fsize'] > 0) ? 1 : 0;
            $adname  = str_replace('</span> <span class="postertrip">!', '#', $row['name']);
            $imgonly2 = ($imgonly) ? "image" : "post";
            
            $row['sub']      = $mysql->escape_string($row['sub']);
            $row['com']      = $mysql->escape_string($row['com']);
            $row['filename'] = $mysql->escape_string($row['filename']);
            $mysql->query("INSERT INTO " . SQLDELLOG . " (admin, postno, action, board,name,sub,com,img) 
            VALUES('$auser','$no', '$imgonly2', '" . BOARD_DIR . ", '$adname','{$row['sub']}','{$row['com']}')");
        }

        if ($delhost !== ''): //Select all posts by IP
            $result = $mysql->query("SELECT no,resto,tim,ext FROM " . SQLLOG . " WHERE host='" . $delhost . "'");
        elseif ($row['resto'] == 0 && $children && !$imgonly): //Select thread and children
            $result = $mysql->query("SELECT no,resto,tim,ext FROM " . SQLLOG . " WHERE no=$no OR resto=$no");
        else: //Only selecting the post
            $result = $mysql->query("SELECT no,resto,tim,ext,embed FROM " . SQLLOG . " WHERE no=$no");
        endif;

        while ($delrow = $mysql->fetch_assoc($result)) { //This does the resource deletions
            $path = realpath("./") . '/' . IMG_DIR;
            $delfile  = $path . $delrow['tim'] . $delrow['ext']; //Path to files
            $delthumb = THUMB_DIR . $delrow['tim'] . 's.jpg';
            $mysql->query("UPDATE " . SQLLOG . " SET fsize='-1' WHERE no='$no' and board='" . BOARD_DIR ."'");
            if ($delrow['embed']) $mysql->query("UPDATE " . SQLLOG . " SET embed='deleted' WHERE no='$no'");
            if (is_file($delfile))
                unlink($delfile); //Delete image
            if (is_file($delthumb))
                unlink($delthumb); //Delete thumbnail
            if (OEKAKI_BOARD == 1 && is_file($path . $delrow['tim'] . '.pch'))
                unlink($path . $delrow['tim'] . '.pch'); // delete oe animation
            if (!$imgonly) { //Delete cached HTML page and log cache
                if ($delrow['resto'])
                    unset($this->cache[$delrow['resto']]['children'][$delrow['no']]);
                unset($this->cache[$delrow['no']]);
                if (API_ENABLED) {
                    @unlink("../" . API_DIR_RES . $delrow['no'] . ".json"); //Delete API json. Catalog/threads/index files are rebuilt later anyway.
                } 
                $this->cache['THREADS'] = array_diff($this->cache['THREADS'], array($delrow['no'])); //Remove from THREADS array
                $mysql->query("DELETE FROM reports WHERE no=" . $delrow['no']); //Clear associated reports
                if (USE_GZIP)
                    @unlink(RES_DIR . $delrow['no'] . PHP_EXT . '.gz');
                @unlink(RES_DIR . $delrow['no'] . PHP_EXT);
            }
        }

        //This actually does the database deletion
        if ($row['resto'] == 0 && $children && !$imgonly) //Delete thread and children
            $result = $mysql->query("DELETE FROM " . SQLLOG . " WHERE no=$no OR resto=$no");
        elseif (!$imgonly) //We're only deleting the post
            $result = $mysql->query("DELETE FROM " . SQLLOG . " WHERE no=$no");
        
        return $row['resto']; // so the caller can know what pages need to be rebuilt
    }
    
    //Delete posts from the archive.
    public function arcDel($no, $automatic = false) {
        
    }
    
    
}
?>
