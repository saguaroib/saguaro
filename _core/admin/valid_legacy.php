<?php

class Valid {
    function verify($action = "none") {
        global $mysql;
        
        static $valid_cache; // the access level of the user
        $access_level = array(
            'none' => 0,
            'has_account' => 1,
            'janitor_board' => 5,
            'janitor' => 25,
            'moderator' => 30,
            'admin' => 50,
            'global' => 75,
            'owner' => 99
       );
        if (!isset($valid_cache)) {
            $valid_cache = $access_level['none'];
            $boardAuth = false;
            $validBoard = false;
            
            if (isset($_COOKIE['saguaro_auser']) && isset($_COOKIE['saguaro_apass'])) {
                $user = $mysql->escape_string($_COOKIE['saguaro_auser']);
                $pass = $mysql->escape_string($_COOKIE['saguaro_apass']);
            } else {
                return false;
            }
            if ($user && $pass) {
                list($allow, $type) = $mysql->fetch_row("SELECT boards,type FROM " . SQLMODSLOG . " WHERE username='$user' and password='$pass'");
                if ($allow) {
                    $allowed = ($allow == "*") ? "*" : explode(',', $allow); 
                    $type = (int) $type;
                    //User has a valid account, time to check what boards they can perform actions on
                    if (@in_array($_GET['b'], $allowed) || $allowed == "*" || @in_array(BOARD_DIR, $allowed)) {
                        switch($type){
                            case 5:
                                $valid_cache = $access_level['janitor_board'];
                                break;
                            case 25:
                                $valid_cache = $access_level['janitor'];
                                break;
                            case 30:
                                $valid_cache = $access_level['moderator'];
                                break;
                            case 50:
                                $valid_cache = $access_level['admin'];
                                break;
                            case 75:
                                $valid_cache = $access_level['global'];
                                break;
                            case 99:
                                $valid_cache = $access_level['owner'];
                                break;
                            default:
                                $valid_cache = $access_level['none'];
                                break;
                        }   
                    } else { //User has a valid account, but didn't request a permission level.
                        $valid_cache = $access_level['has_account'];
                    }
                }
            }
        }
        switch ($action) {
            case 'user':
                return $valid_cache >= $access_level['has_accounts'];
            case 'moderator':
                return $valid_cache >= $access_level['moderator'];
            case 'global':
                return $valid_cache >= $access_level['global'];
            case 'admin':
                return $valid_cache >= $access_level['admin'];
            case 'textonly':
                return $valid_cache >= $access_level['moderator'];
            case 'janitor':
                return $valid_cache >= $access_level['janitor'];
            case 'manager':
                return $valid_cache >= $access_level['manager'];
            case 'delete':
                return $valid_cache >= $access_level['janitor'];
            case 'reportflood':
                return $valid_cach >= $access_level['janitor'];
            case 'floodbypass':
                return $valid_cache >= $access_level['moderator'];
            case 'owner':
                return $valid_cache >= $access_level['owner'];
            case 'boardlist':
                return $this->userBoards();
            default: // unsupported action
                return false;
        }
    }
    
    private function userBoards() {
        global $mysql;
        if (isset($_COOKIE['saguaro_auser']) && isset($_COOKIE['saguaro_apass'])) {
            $user = $mysql->escape_string($_COOKIE['saguaro_auser']);
            $pass = $mysql->escape_string($_COOKIE['saguaro_apass']);
        } else {
            return false;
        }
            return $mysql->result("SELECT boards FROM " . SQLMODSLOG . " WHERE username='{$user}' and password='{$pass}'");
    }
}

?>
