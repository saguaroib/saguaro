<?php

class Staff {

    function isStaff($user) {
        global $mysql;
        //See if user exists in mod table. Returns false if user isn't in table. 
        if ($mysql->num_rows("SELECT * FROM " . SQLMODSLOG . " WHERE user='$user' LIMIT 1") > 0)
            return true;           
        return false;
    }
    
    function process($array) {
        global $mysql;
        
        if (!valid('admin')) error(S_NOPERM);
        
        $array = [
            'user'              => $mysql->escape_string($_POST['user']),
            'pwd1'             => $mysql->escape_string($_POST['pwd1']),
            'pwd2'             => $mysql->escape_string($_POST['pwd2']),
            'permission'  => $mysql->escape_string($_POST['action']),
            'delete'            => $mysql->escape_string($_POST['delete'])
        ];

        if (Staff::isStaff($array['user'] && !$array['delete']))
            error("This user already exists!");
        
        if ($array['delete'] && Staff::isStaff($array['delete'])) {
            if ($_COOKIE['saguaro_auser'] == $array['delete'])
                error("You can't delete yourself!"); //oi ya cheeky shit ill bash yer fookin head in i sware on me mum

            $mysql->query("DELETE FROM " . SQLMODSLOG . " WHERE user='" . $array['delete'] ."'");
            return true;
        }
            
        switch ($array['permission']) {
            case 'admin':
                $allowed = 'janitor_board,janitor,moderator,manager,admin';
                $denied = 'none';
                break;
            case 'manager':
                $allowed = 'janitor_board,janitor,moderator,manager';
                $denied = 'admin';
                break;
            case 'mod':
                $allowed = 'janitor_board,moderator';
                $denied = 'manager,admin';
                break;
            case 'janitor':
                $allowed = 'janitor_board,janitor';
                $denied = 'moderator,manager,admin';
                break;
            case 'janitor_board':
                $allowed = 'janitor_board,' . BOARD_DIR;
                $denied = 'moderator,manager,admin';
                break;
            default:
                error("Attempted to set unknown permission type.");
                break;
        }
        
        if ($array['pwd1'] !== $array['pwd2'])
            error("Passwords did not match!");
            
        require_once(CORE_DIR . "/crypt/legacy.php");
        $crypt = new SaguaroCryptLegacy;
        $salt = $crypt->generate_hash($array['pwd2']);

        $mysql->query("INSERT INTO " . SQLMODSLOG . " (`user`, `password`, `public_salt`, `allowed`, `denied`) VALUES ('" . $array['user'] . "', '" . $salt['hash'] . "', '" . $salt['public_salt'] . "', '" . $allowed . "', '" . $denied . "')");
    }
    
    function modifyStaff($targUser, $actUser, $actPass, $perms = array(), $self = 0) {
        //modify existing user
    }

}

?>
