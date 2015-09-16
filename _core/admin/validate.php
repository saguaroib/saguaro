<?php

class Validation
{
    function verify( $action )
    {
        
        static $valid_cache; // the access level of the user
        $access_level = array(
            'none' => 0,
            'janitor' => 1,
            'janitor_this_board' => 2,
            'moderator' => 5,
            'manager' => 10,
            'admin' => 20
        );
        if (!isset($valid_cache)) {
            $valid_cache = $access_level['none'];
            if (isset($_COOKIE['saguaro_auser']) && isset($_COOKIE['saguaro_apass'])) {
                $user = mysql_real_escape_string($_COOKIE['saguaro_auser']);
                $pass = mysql_real_escape_string($_COOKIE['saguaro_apass']);
            }
            if ($user && $pass) {
                $result = mysql_call("SELECT allowed,denied FROM " . SQLMODSLOG . " WHERE user='$user' and password='$pass'");
                list($allow, $deny) = mysql_fetch_row($result);
                mysql_free_result($result);
                if ($allow) {
                    $allows             = explode(',', $allow);
                    $seen_janitor_token = false;
                    // each token can increase the access level,
                    // except that we only know that they're a moderator or a janitor for another board
                    // AFTER we read all the tokens
                    foreach ($allows as $token) {
                        if ($token == 'janitor')
                            $seen_janitor_token = true;
                        /*  else if ( $token == 'manager' && $valid_cache < $access_level['manager'] )
                        $valid_cache = $access_level['manager'];*/
                        else if ($token == 'admin' && $valid_cache < $access_level['admin'])
                            $valid_cache = $access_level['admin'];
                        else if (($token == BOARD_DIR || $token == 'all') && $valid_cache < $access_level['janitor_this_board'])
                            $valid_cache = $access_level['janitor_this_board']; // or could be moderator, will be increased in next step
                    }
                    // now we can set moderator or janitor status 
                    if (!$seen_janitor_token) {
                        if ($valid_cache < $access_level['moderator'])
                            $valid_cache = $access_level['moderator'];
                    } else {
                        if ($valid_cache < $access_level['janitor'])
                            $valid_cache = $access_level['janitor'];
                    }
                    if ($deny) {
                        $denies = explode(',', $deny);
                        if (in_array(BOARD_DIR, $denies)) {
                            $valid_cache = $access_level['none'];
                        }
                    }
                }
            }
        }
        switch ($action) {
            case 'moderator':
                return $valid_cache >= $access_level['moderator'];
            case 'admin':
                return $valid_cache >= $access_level['admin'];
            case 'textonly':
                return $valid_cache >= $access_level['moderator'];
            case 'janitor_board':
                return $valid_cache >= $access_level['janitor'];
            /*case 'manager':
            return $valid_cache >= $access_level['manager'];*/
            case 'delete':
                if ($valid_cache >= $access_level['janitor_this_board']) {
                    return true;
                }
                // if they're a janitor on another board, check for illegal post unlock			
                else if ($valid_cache >= $access_level['janitor']) {
                    $query         = mysql_call("SELECT COUNT(*) from reports WHERE board='" . BOARD_DIR . "' AND no=$no AND cat=2");
                    $illegal_count = mysql_result($query, 0, 0);
                    mysql_free_result($query);
                    return $illegal_count >= 3;
                }
            case 'reportflood':
                return $valid_cache >= $access_level['janitor'];
            case 'floodbypass':
                return $valid_cache >= $access_level['moderator'];
            default: // unsupported action
                return false;
        }
    }
}

?>