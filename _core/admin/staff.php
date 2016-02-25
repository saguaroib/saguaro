<?php

class Staff {

    function isStaff($user) {
        global $mysql;
        //See if user exists in mod table. Returns false if user isn't in table. 
        if ($mysql->num_rows("SELECT * FROM " . SQLMODSLOG . " WHERE user='$user'") > 0)
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

    function display() {
        
        if (!valid('admin'))  error(S_NOPERM);
        
        global $mysql;
        //Staff list for panel
        
        if (!$active = $mysql->query("SELECT * FROM " . SQLMODSLOG . "")) 
            echo S_SQLFAIL;
        $j = 0;
        $temp = '';
        $temp .= "<br><br>[<a href='" . PHP_ASELF_ABS . "'>Back to Panel</a><input type='hidden' name='mode' value='admin'>]";
        $temp .= "<input type=hidden name=pass value=\"$pass\">";
        $temp .= "<div class='delbuttons'>";
        $temp .= "<table class='postlists'><br>";
        $temp .=  "<tr class='postTable head'><th>User</th><th>Allowed permissions</th><th>Denied permission</th><th>Delete user</th>";
        $temp .=  "</tr>";

        while ($row = $mysql->fetch_assoc($active)) {
                $j++;               
                $class = 'row' . ($j % 2 + 1); //BG color
                $temp .= "<form action='" . PHP_ASELF_ABS ."?mode=staff' method='post' ><tr class='$class'>";
                $temp .= "<td>" . $row['user'] . "</td><td>" . $row['allowed'] . "</td><td>" . $row['denied'] . "</td>
                <td><input type='submit' value='" . $row['user'] . "' name='delete' /></td>";
                $temp .= "</tr>";
        }	
        $temp .= "</form><div class='managerBanner' >[<a href='#' onclick=\"toggle_visibility('userForm');\" style='color:white;text-align:center;'>Toggle New User Form</a>]</div>";
        $temp .= "<div><table id='userForm' style='text-align:center;display:none;'><br><hr style='width:50%;'>";
        $temp .= "<form action='" . PHP_ASELF_ABS ."?mode=staff' method='post'><tr><td>New username: <input type='text' name='user' required></td>";
        $temp .= "<td>New password: <input type='password' name='pwd1' required></td><td>Confirm password: <input type='password' name='pwd2' required></td>";
        $temp .= "<td>Access level: <select name='action' required>
            <option value='' /></option>
            <option class='cap admin' value='admin' />Admin</option>
            <option class='cap manager' value='manager' />Manager</option>
            <option class='cap moderator' value='mod' />Moderator</option>
            <option class='cap jani' value='janitor' />Global Janitor</option>
            <option value='janitor_board' />Janitor (/" . BOARD_DIR . "/ only)</option>
            </select></td><td><input type='submit' value='Submit'/></td></tr></table></div>";
        return $temp;
    }
}

?>
