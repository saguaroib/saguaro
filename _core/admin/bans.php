<?php

class Banish {
	
    function isBanned($ip) {
		global $mysql;

		$query = "SELECT ip FROM " . SQLBANLOG . " WHERE ip='" . $mysql->escape_string($ip) . "' AND active>0";
		$result = $mysql->num_rows($query);

		return ($result > 0) ? true : false;
    }
    
	function postOptions($no, $ip, $expires, $expires2, $banType, $perma, $pubreason, $staffnote, $custmess, $showbanmess, $afterban) {
        global $mysql, $my_log;
		//This will do the POST processing and pass it to applyBan
		
		$str = "+" . $expires . " " . $expires2;
		$expires = strtotime($str, time() );
		
        $custmess = ($showbanmess) ? ($custmess == '') ? "(USER WAS BANNED FOR THIS POST)" : "(" . $custmess . ")" : 0; //pls ignore

        $afterban = (int) $afterban;
		if ($afterban > 0) {
            require_once(CORE_DIR . '/admin/delete.php');
            $del = new Delete;
			if ($afterban == 1):
                $del->targeted($no, $pwd, $imgonly = 0, $automatic = 1, $children = 1, $die = 1);
			elseif ($afterban == 2):
                $del->targeted($no, $pwd, $imgonly = 1, $automatic = 1, $children = 1, $die = 1);
			else:
                $del->targeted($no, $pwd, $imgonly = 0, $automatic = 1, $children = 0, $die = 1, $ip);
            endif;
		}
		
		$mysql->query( "INSERT INTO " . SQLBANLOG . " (ip, active, placedon, expires, board, type, reason, staffnotes) 
		VALUES ('" . $mysql->escape_string( $ip ) . "', 
		'1', 
		UNIX_TIMESTAMP(),
		'" . $mysql->escape_string( $expires ) . "',
		'" . BOARD_DIR . "', 
		'" . $mysql->escape_string( $banType ) . "', 
		'" . $mysql->escape_string( $pubreason ) . "', 
		'" . $mysql->escape_string( $staffnote ) . "' )");

		if ($custmess)
			$mysql->query( "UPDATE " . SQLLOG . " SET com = CONCAT(com, '<br><b><font color=\"FF101A\">" . $mysql->escape_string( $custmess ) . "</font></b>') where no='" . $no . "'");  
	
        $my_log->update($no);
    }
	
    function form($no) {
        global $mysql;
    
        $host = $mysql->result("SELECT host FROM " . SQLLOG . " WHERE no='" . $mysql->escape_string($no) . "'", 0, 0);
        $alart = ($host) ? $mysql->num_rows("SELECT COUNT(*) FROM " . SQLBANLOG . " WHERE ip='" . $host . "'") : 0;
        $alert = ( $alart > 0) ? "<b><font color=\"FF101A\"> $alart ban(s) on record for $host!</font></b>" : "No bans on record for IP $host";

        $temp = head(1);
        
        $temp .= "<!---banning #:$no; host:$host---><br><table border='0' cellpadding='0' cellspacing='0' /><form action='admin.php?mode=ban' method='POST' />
            <input type='hidden' name='no' value='$no' />
            <input type='hidden' name='ip' value='$host' />
            <tr><td class='postblock'>IP History: </td><td>$alert</td></tr>
            <tr><td class='postblock'>Unban in:</td><td><input type='number' min='0' size='7' name='banlength1'  /> <select name='banlength2' />
                <option value='second' />seconds</option>
                <option value='minute' />minutes</option>
                <option value='day' />days</option>
                <option value='month' />months</option>
                <option value='year' />years</option>
                </select></td></tr>
            <center><tr><td class='postblock'>Ban type:</td><td></center>
                <select name='banType' />
                <option value='1' />Warning only</option>
                <option value='2' />This board - /" . BOARD_DIR . "/ </option>
                <option value='3' />All boards</option>
                <option value='4' />Permanent - All boards</option>
                </select>
            </td></tr>
            <tr><td class='postblock'>Public reason:</td><td><textarea rows='2' cols='25' name='pubreason' /></textarea></td></tr>
            <tr><td class='postblock'>Staff notes:</td><td><input type='text' name='staffnote' /></td></tr>
            <tr><td class='postblock'>Append user's comment:</td><td><input type='text' name='custmess' placeholder='Leave blank for USER WAS BAN etc.' /><br>[ Show message<input type='checkbox' name='showbanmess' /> ] </td></tr>
            <tr><td class='postblock'>After-ban options:</td><td>
                <select name='afterban' />
                <option value='0' />None</option>
                <option value='1' />Delete this post</option>
                <option value='2' />Delete image only</option>
                <option value='3' />Delete all by this IP</option>
                </select>
            </td></tr>";
            /*if (valid('admin'))
                $temp .= "
                <tr><td class='postblock'>Add to Blacklist:</td><td>[ Comment<input type='checkbox' name='blacklistcom' /> ] [ Image MD5<input type='checkbox' name='blacklistimage' /> ] </td></tr>";*/ //Soon.
            $temp .= "<center><tr><td><input type='submit' value='Ban'/></td></tr></center></table></form>";
            
            echo $temp;
    }
    
	function afterBan() {
		//Display information for ban just executed, allow revert option, redirect to admin.
		echo "Banned IP: " . $ip . ", Redirecting. <META HTTP-EQUIV=\"refresh\" content=\"2;URL=" . PHP_ASELF_ABS . "\">";
	}
	
}

?>
