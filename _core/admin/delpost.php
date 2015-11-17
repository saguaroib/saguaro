<?php

require_once(CORE_DIR . "/log/log.php");

class DeletePost extends Log {
    function userDel($no, $pwd) {
        global $mysql;
        $host         = $_SERVER["REMOTE_ADDR"];
        $delno        = array();
        $rebuildindex = !(defined("STATIC_REBUILD") && STATIC_REBUILD);
        $delflag      = FALSE;
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
        $flag     = FALSE;
        $rebuild  = array(); // keys are pages that need to be rebuilt (0 is index, of course)
        for ($i = 0; $i < $countdel; $i++) {
            $resto = $this->targeted($delno[$i], $pwd, $onlyimgdel, 0, 1, $countdel == 1); // only show error for user deletion, not multi
            if ($resto)
                $rebuild[$resto] = 1;
        }
        /*if (!$flag)
            error(S_BADDELPASS);*/
        $log = $this->cache;

        foreach ($rebuild as $key => $val) {
            $this->update($key, 1); // leaving the second parameter as 0 rebuilds the index each time!
        }
        if ($rebuildindex)
            $this->update(0, 1); // update the index page last
    }

    function targeted($resno, $pwd, $imgonly = 0, $automatic = 0, $children = 1, $die = 1, $allbyip = 0, $delhost = '') {
        global $path, $mysql;

        $this->update_cache();
        $log = $this->cache;
        $resno = intval($resno);

        // get post info
        if (!isset($log[$resno])) {
            if ($die)
                echo "Can't find the post $resno.";
        }
        $row       = $log[$resno];
        // check password- if not ok, check admin status (and set $admindel if allowed)
        $delete_ok = ($automatic || (substr(md5($pwd), 2, 8) == $row['pwd']) || ($row['host'] == $_SERVER['REMOTE_ADDR']));
        if (valid('janitor_board') && !$automatic) {
            $delete_ok = $admindel = valid('delete', $resno);
        }
        if (!$delete_ok)
            error(S_BADDELPASS);
        // check ghost bumping
        if (!isset($admindel) || !$admindel) {
            if (BOARD_DIR == 'a' && (int) $row['time'] > (time() - 25) && $row['email'] != 'sage') {
                $ghostdump = var_export(array(
                     'server' => $_SERVER,
                    'post' => $_POST,
                    'cookie' => $_COOKIE,
                    'row' => $row
               ), true);
                //file_put_contents('ghostbump.'.time(),$ghostdump);
            }
        }
        if (isset($admindel) && $admindel) { // extra actions for admin user
            $auser   = $mysql->escape_string($_COOKIE['saguaro_auser']);
            $adfsize = ($row['fsize'] > 0) ? 1 : 0;
            $adname  = str_replace('</span> <span class="postertrip">!', '#', $row['name']);
            if ($imgonly) {
                $imgonly = 1;
            } else {
                $imgonly = 0;
            }
            $row['sub']      = $mysql->escape_string($row['sub']);
            $row['com']      = $mysql->escape_string($row['com']);
            $row['filename'] = $mysql->escape_string($row['filename']);
            $mysql->query("INSERT INTO " . SQLDELLOG . " (postno, imgonly, board,name,sub,com,img,filename,admin) values('$resno','$imgonly','" . BOARD_DIR . "','$adname','{$row['sub']}','{$row['com']}','$adfsize','{$row['filename']}','$auser')");
        }
        if ($allbyip && $delhost !== '') 
            $result = $mysql->query("select no,resto,tim,ext from " . SQLLOG . " where host='" . $delhost . "'");
        if ($row['resto'] == 0 && $children && !$imgonly && !$allbyip) // select thread and children
            $result = $mysql->query("select no,resto,tim,ext from " . SQLLOG . " where no=$resno or resto=$resno");
        else // just select the post
            $result = $mysql->query("select no,resto,tim,ext from " . SQLLOG . " where no=$resno");
        while ($delrow = $mysql->fetch_assoc($result)) {
            // delete
            $path = realpath("./") . '/' . IMG_DIR;
            $delfile  = $path . $delrow['tim'] . $delrow['ext']; //path to delete
            $delthumb = THUMB_DIR . $delrow['tim'] . 's.jpg';
            if (is_file($delfile))
                unlink($delfile); // delete image
            if (is_file($delthumb))
                unlink($delthumb); // delete thumb
            if (OEKAKI_BOARD == 1 && is_file($path . $delrow['tim'] . '.pch'))
                unlink($path . $delrow['tim'] . '.pch'); // delete oe animation
            if (!$imgonly) { // delete thread page & log_cache row
                if ($delrow['resto'])
                    unset($log[$delrow['resto']]['children'][$delrow['no']]);
                unset($log[$delrow['no']]);
                $log['THREADS'] = array_diff($log['THREADS'], array($delrow['no'])); // remove from THREADS
                $mysql->query("DELETE FROM reports WHERE no=" . $delrow['no']); // clear reports
                if (USE_GZIP == 1)
                    @unlink(RES_DIR . $delrow['no'] . PHP_EXT . '.gz');
                @unlink(RES_DIR . $delrow['no'] . PHP_EXT);
            }
        }
        //delete from DB
        if ($row['resto'] == 0 && $children && !$imgonly) // delete thread and children
            $result = $mysql->query("delete from " . SQLLOG . " where no=$resno or resto=$resno");
        elseif (!$imgonly) // just delete the post
            $result = $mysql->query("delete from " . SQLLOG . " where no=$resno");
        return $row['resto']; // so the caller can know what pages need to be rebuilt
    }
}
?>
