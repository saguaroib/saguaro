<?php

class Valid {
    function verify($action = "none") {
        global $mysql;
        
        static $perms, $allowed;
        
        $isUser = false;
        
        if (!isset($perms)) {
            
            if (isset($_COOKIE['saguaro_auser']) && isset($_COOKIE['saguaro_apass'])) {
                $user = $mysql->escape_string($_COOKIE['saguaro_auser']);
                $pass = $mysql->escape_string($_COOKIE['saguaro_apass']);
            } else {
                return false;
            }
            if ($user && $pass) {
                $row = $mysql->fetch_assoc("SELECT boards,permissions FROM " . SQLMODSLOG . " WHERE username='{$user}' and password='{$pass}'");
                //    if ($row['boards']) {
                    
                $isUser = true;
                if ($row['permissions'] == "*") {
                    $perms = 9001;
                } else {
                    $allowed = $row['boards'];
                    $perms   = unserialize(base64_decode($row['permissions']));
                }
                //}
            }
        }
        switch ($action) {
            case 'user':
                return $isUser;
            case 'janitor':
                return $perms[BOARD_DIR]['level'] >= 1;
            case 'moderator':
                return $perms[BOARD_DIR]['level'] >= 2;
            case 'manager':
                return $perms[BOARD_DIR]['level'] >= 3;
            case 'admin':
                return $perms[BOARD_DIR]['level'] >= 4;
            case 'global':
                return $perms == 9001;
            case 'owner':
                return $perms == 9001;
            case 'boardlist':
                return $allowed;
            case 'allperms':
                return $perms;
            case 'ban':
                return ($perms[BOARD_DIR]['ban'] == 2) || $perms === 9001;
            case 'banrequest':
                return ($perms[BOARD_DIR]['ban'] == 1) || $perms === 9001;    
            default:
                return ($perms[BOARD_DIR][$action] >= 1) || $perms === 9001;
        }
    }
}

?>
