<?php

class Staff {

    function isStaff($user) {
        //See if user exists in mod table. Returns false if user is in table. Why does it do that.
        if (!$mysql->query(" SELECT `user` FROM " . SQLMODSLOG . " WHERE user='$user'"))
            return true;           
        return false;
    }
    
    function addStaff($user = 0, $pass1 = 0, $pass2 = 0, $perm) {
        //add staff member
        if (!valid('admin'))
            error("Permission denied");

         if ($this->isStaff(mysql_real_escape_string($user)))
            error("This user already exists!");
            
        switch ($perm) {
            case 'admin':
                $allowed = 'janitor_board,moderator,admin';
                $denied = 'none';
                break;
            case 'mod':
                $allowed = 'janitor_board,moderator';
                $denied = 'admin';
                break;
            case 'janitor':
                $allowed = 'janitor_board';
                $denied = 'moderator, admin';
                break;
            default:
                error("Attempted to set unknown permission type.");
                break;
        }
        
        if ($pass1 !== $pass2)
            error("Passwords did not match!");
            
        require_once(CORE_DIR . "/crypt/legacy.php");
        $crypt = new SaguaroCryptLegacy;
        $salt = $crypt->generate_hash($pass2);

        mysql_query("INSERT INTO " . SQLMODSLOG . " (`user`, `password`, `public_salt`, `allowed`, `denied`) VALUES ('" . mysql_real_escape_string($user) . "', '" . $salt['hash'] . "', '" . $salt['public_salt'] . "', '" . $allowed . "', '" . $denied . "')");
    }
    
    function remStaff($targUser = '', $actUser, $actPass) {
        //remove staff member
        $targUser = mysql_real_escape_string($targUser);
        
        if (!valid('admin'))
            error("Permission denied");
        if ($this->isStaff($targUser))
            error("User doesn't exist! (GET error?");
        if ($_COOKIE['saguaro_auser'] == $targUser)
            error("You can't delete yourself!"); //oi ya cheeky shit ill bash yer fookin head in i sware on me mum
        
        mysql_query("DELETE FROM " . SQLMODSLOG . " WHERE user='" . $targUser ."'");
    }
    
    function modifyStaff($targUser, $actUser, $actPass, $perms = array(), $self = 0) {
        //modify existing user
    }

    function getStaff() {
        //Staff list for panel
        
        if ( !$active = mysql_query("SELECT * FROM " . SQLMODSLOG . "")) 
            echo S_SQLFAIL;
        $j = 0;
        $temp = '';
        $temp .= "<br><br>[<a href='" . PHP_ASELF_ABS . "'>Back to Panel</a><input type='hidden' name='mode' value='admin'>]";
        $temp .= "<input type=hidden name=pass value=\"$pass\">";
        $temp .= "<div class='delbuttons'>";
        $temp .= "<table class='postlists'><br>";
        $temp .=  "<tr class='postTable head'><th>User</th><th>Allowed permissions</th><th>Denied permission</th><th>Modify user</th>";
        $temp .=  "</tr><form action='" . PHP_ASELF_ABS ."?mode=staff' method='get'>";

        while ( $row = mysql_fetch_assoc( $active ) ) {
                $j++;               
                $class = 'row' . ($j % 2 + 1); //BG color
                $temp .= "<tr class='$class'>";
                $temp .= "<td>" . $row['user'] . "</td><td>" . $row['allowed'] . "</td><td>" . $row['denied'] . "</td>
                <td><input type='button' text-align='center' onclick=\"location.href='" . PHP_ASELF_ABS . "?mode=staff&deluse=" . $row['user'] . "';\" value=\"Delete User\" /></td>";
                $temp .= "</tr>";
        }	
        $temp .= "</form><div class='managerBanner' >[<a href='#' onclick=\"toggle_visibility('userForm');\" style='color:white;text-align:center;'>Toggle New User Form</a>]</div>";
        $temp .= "<div><table id='userForm' style='text-align:center;display:none;'><br><hr style='width:50%;'>";
        $temp .= "<form action='" . PHP_ASELF_ABS ."?mode=staff' method='post'><tr><td>New username: <input type='text' name='user' required></td>";
        $temp .= "<td>New password: <input type='password' name='pwd1' required></td><td>Confirm password: <input type='password' name='pwd2' required></td>";
        $temp .= "<td>Access level: <select name='action' required>
            <option value='' /></option>
            <option value='admin' />Admin</option>
            <option value='mod' />Moderator</option>
            <option value='janitor' />Janitor</option>
            </select></td><td><input type='submit' value='Submit'/></td></tr></table></div>";
        return $temp;
    }
}

?>
