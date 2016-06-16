<?php
/*
    All functions related to checking bans based on a provided IP
*/
class BanishCheck {
    //Check if a user is banned. If they are, the function will return the number of ban records on file. 
    //Call with the "redirect" flag to send the user to the banned screen.
    public function isBanned($ip, $redirect = false) {
        global $mysql;
        
        $ip = $mysql->escape_string($ip);
        
        $exists = (int) $mysql->result("SELECT COUNT(*) FROM " . SQLBANLOG . " WHERE host='{$ip}' AND (board='" . BOARD_DIR . "' or global=1)");
        
        if ($exists > 0 && $redirect == true) {
            echo '<META http-equiv="refresh" content="0;URL=//' . SITE_ROOT_BD . '/' . PHP_SELF . '?mode=banned">';
            exit();
        } else {
            return ($exists > 0) ? $exists : false;
        }
    }    
}