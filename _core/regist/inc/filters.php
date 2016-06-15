<?php

/*
    Post filtering class. The most evil class in the entire repo.
    To be used exclusively by Regist.
    
    $filter = new Filter;
*/
    


class Filter {

    //
    public function tripcode($name, $trip, $dest) {
        return true;
    }
    
    public function host($dest) {
        global $host; return true;
    }
    
    //Basic filter checks
    public function post($com, $sub, $name, $fsize, $resto, $W, $H, $dest, $upfile_name, $email)  {
        return true;
    }
    
    //Check if post trips autosage filter. If true, thread will be created with permasage automatically enabled.
    public function autosage($com, $sub, $name, $fsize, $resto, $W, $H, $dest, $insertid) {
        return false;
    }
    
    /*Simple text replacement. Ex. *opinion you don't like* ==> *something that doesn't trigger you*
    If you use this, you are a bad person.*/
    public function simpleFilter($input, $field) {
        switch($field) {
            case "com":
                break;
            case "sub":
                break;
            case "name":
                break;
            default:
                break;
        }
         return true;
    }

    //The robot. Under construction, pls come back later.
    public function r9k($com, $md5, $moderator = false) { 
        return "ok";
    }
    
    //Blacklist, reads from blacklist table. Different from simple filter, matching values in filter will prevent the post from being made.
    public function blacklist($post, $dest) {
        global $mysql;
        $board    = BOARD_DIR;
        $querystr = "SELECT SQL_NO_CACHE * FROM " . SQLBLACKLIST . " WHERE active=1 AND (board='' or board='$board') AND (0 ";
        foreach ($post as $field => $contents) {
            if ($contents) {
                $contents = $mysql->escape_string(html_entity_decode($contents));
                $querystr .= " OR (field='$field' AND contents='$contents') ";
            }
        }
        $querystr .= " ) LIMIT 1";
        $query = $mysql->num_rows($querystr);
        if ($query == 0)
            return false;
        $row = $mysql->fetch_assoc($query);
        if ($row['ban']) {
            require_once(CORE_DIR . "/admin/bans.php");
            $ban = new Banish;
            $prvreason = "Blacklisted ${row['field']} - " . htmlspecialchars($row['contents']);
            $ban->autoBan($post['trip'] ? $post['nametrip'] : $post['name'], $row['banlength'], 1, $prvreason, $row['banreason']);
        }
        error(S_UPFAIL, $dest);
    }
}