<?php

class Valid {
    function verify($action, $no = 0) {
        global $mysql;
        
        static $valid_cache; // the access level of the user
        
        if (!isset($valid_cache)) {           
            if (isset($_COOKIE['saguaro_auser']) && isset($_COOKIE['saguaro_apass'])) {
                $user = $mysql->escape_string($_COOKIE['saguaro_auser']);
                $pass = $mysql->escape_string($_COOKIE['saguaro_apass']);
            } else {
                return false;
            }
            if ($user && $pass) {
				$row = $mysql->result("SELECT perms FROM " . SQLMODSLOG . " WHERE user='{$user}' and password='{$pass}' LIMIT 1");
				$valid_cache = json_decode($row, true);
            }
        }

        switch ($action) {
            case 'delete': //If they're a janitor on another board, check for illegal post unlock	
				$illegal_count = $mysql->result("SELECT COUNT(*) FROM " . SQLREPORTS . " WHERE board='" . BOARD_DIR . "' AND no='{$no}' AND cat=2");
				return $illegal_count >= 3;
            case 'boardlist':
				return $valid_cache['_boards'];
            default:
				if ($valid_cache[BOARD_DIR][$action] === 1 || $valid_cache['_flags'][$action] === 1) {
					return true;
				}
				return false;
        }
    }
}