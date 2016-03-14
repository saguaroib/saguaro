<?php

require_once(CORE_DIR . "/log/log.php");

class Delete extends Log {
    function userDel($no, $pwd, $onlyimgdel) {
        global $mysql, $host;
        $delno        = array();
        $rebuildindex = !(defined("STATIC_REBUILD") && STATIC_REBUILD);
        $delflag      = FALSE;
        $resnum = $_POST['anchor'];

        reset($_POST);
        while ($item = each($_POST)) {
            if ($item[1] == 'delete') {
                array_push($delno, $item[0]);
                $delflag = TRUE;
            }
        }
        $pwdc = $_COOKIE['saguaro_pwdc'];
        if ($pwd == "" && $pwdc != "")
            $pwd = $pwdc;
        $countdel = count($delno);
        $rebuild  = array(); // keys are pages that need to be rebuilt (0 is index, of course)
        for ($i = 0; $i < $countdel; $i++) {
            $resto = $this->targeted($delno[$i], $pwd, $onlyimgdel, 0, 1, $countdel == 1); // only show error for user deletion, not multi
            if ($resto)
                $rebuild[$resto] = 1;
        }
        
        $this->update_cache(1);
        $log = $this->cache;

        foreach ($rebuild as $key => $val) {
            $this->update($key, 1); // leaving the second parameter as 0 rebuilds the index each time!
        }
        if ($rebuildindex)
            $this->update(0, 1); // update the index page last
        
        if (isset($log[$resnum])) { 
            $redir = RES_DIR . $resnum . ".html"; //Thread was deleted, redirect to index
            header("Location: $redir");
        } else { 
            header("Location: " . PHP_SELF2); //User deleted a reply to a thread while in a thread, redirect to that thread.
        }
            
    }
    
    
    function targeted($no, $pwd, $imgonly = 0, $automatic = 0, $children = 1, $die = 1, $delhost = '') {
        global $path, $mysql, $host;

        $this->update_cache(1);
        $log = $this->cache;
        $no = (int) $no;
        
        if (!isset($log[$no])) //Does post exist?
            if ($die) exit(S_NODELPOST . $no);
        
        $row = $log[$no];
        //Check password. If no password, check admin status
        $delete_ok = ($automatic || (substr(md5($pwd), 2, 8) == $row['pwd']) || ($row['host'] == $host));
        if (valid('janitor') && !$automatic)
            $admindel = valid('delete', $no);
        
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
            $result = $mysql->query("SELECT no,resto,tim,ext FROM " . SQLLOG . " WHERE no=$no");
        endif;
        
        
        while ($delrow = $mysql->fetch_assoc($result)) { //This does the resource deletions
            $path = realpath("./") . '/' . IMG_DIR;
            $delfile  = $path . $delrow['tim'] . $delrow['ext']; //Path to files
            $delthumb = THUMB_DIR . $delrow['tim'] . 's.jpg';
            if (is_file($delfile))
                unlink($delfile); //Delete image
            if (is_file($delthumb))
                unlink($delthumb); //Delete thumbnail
            if (OEKAKI_BOARD == 1 && is_file($path . $delrow['tim'] . '.pch'))
                unlink($path . $delrow['tim'] . '.pch'); // delete oe animation
            if (!$imgonly) { //Delete cached HTML page and log cache
                if ($delrow['resto'])
                    unset($log[$delrow['resto']]['children'][$delrow['no']]);
                unset($log[$delrow['no']]);
                $log['THREADS'] = array_diff($log['THREADS'], array($delrow['no'])); //Remove from THREADS array
                $mysql->query("DELETE FROM reports WHERE no=" . $delrow['no']); //Clear associated reports
                if (USE_GZIP == 1)
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
    
    function deleteUploaded($file, $path) {
        global $upfile, $dest;
        if ($dest || $upfile) {
            @unlink($upfile);
            @unlink($dest);
        }
    }
}
?>
