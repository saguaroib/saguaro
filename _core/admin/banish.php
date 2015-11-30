<?php

class Banish {
	
    function checkBan($ip) {
       global $mysql;
		if ( $mysql->num_rows("SELECT ip, active FROM " . SQLBANLOG . " WHERE ip='" . $mysql->escape_string($ip) . "' AND active>0 LIMIT 1") > 0 ) 		//if active ban exists, stop all further action
			return false;
		return true;		//No active bans
    }
    
	function postOptions($no, $ip, $expires, $banType, $perma, $pubreason, $staffnote, $custmess, $showbanmess, $afterban) {
        global $mysql;
		//This will do the POST processing and pass it to applyBan
		
		$str = "+" . $expires . " day";
		$expires = strtotime($str, time() );
		
	if ($banType) {
		if ( $banType == 'warn') 
			$banType = 1;
		elseif ( $banType == 'thisboard' ) 
			$banType = 2;
		elseif ( $banType == 'global')  //bantype is global
			$banType = 3;
		else 
			$banType = 4;
	}
		
		if ( $showbanmess ) {
			if ( $custmess == '')
				$custmess = "(USER WAS BANNED FOR THIS POST)";
			else 
				$custmess = "(" . $custmess . ")";
		} else 
			$custmess = 0;
		
		//$banish->applyBan($no, $ip, $length, $banType, $pubreason, $staffnote, $custmess);
		
		if ( $afterban !== 'none' ) {
            require_once(CORE_DIR . '/admin/delpost.php');
            $del = new DeletePost;
			if ($afterban == 'delpost')
                $del->targeted($no, $pwd, $imgonly = 0, $automatic = 1, $children = 1, $die = 1);
			if ($afterban == 'delallbyip')
                $del->targeted($no, $pwd, $imgonly = 0, $automatic = 1, $children = 1, $die = 1, $allbyip = 1, $ip);
			if ($afterban == 'delimgonly')
                $del->targeted($no, $pwd, $imgonly = 1, $automatic = 1, $children = 0, $die = 1);
		}
			
	//}
	
	//function applyBan( $no, $ip, $length, $type, $pubreason, $staffnote, $custmess) {
		
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
		
	}
	
	function afterBan() {
		//Display information for ban just executed, allow revert option, redirect to admin.
		echo "Banned IP: " . $ip . ", Redirecting. <META HTTP-EQUIV=\"refresh\" content=\"2;URL=" . PHP_ASELF_ABS . "\">";
	}
    
    function form($host,$no) {
        global $mysql;
        $alart = ( $mysql->num_rows("SELECT COUNT(*) FROM " . SQLBANLOG . " WHERE ip='" . $mysql->escape_string($host) . "'") > 0) ? "<b><font color=\"FF101A\"> Previous ban(s) on record for $host!</font></b>" : 'No bans on record for IP $host';
        $temp = "<!DOCTYPE html><br><table border='0' cellpadding='0' cellspacing='0' /><form action='admin.php?mode=ban' method='POST' />
        <input type='hidden' name='no' value='$no' />
        <input type='hidden' name='ip' value='$host' />
        <center><th class='postblock'><b>Ban panel</b></th></center>
        <tr><td class='postblock'>IP History: </td><td>$alert</td></tr>
        <tr><td class='postblock'>Unban in:</td><td><input type='number' min='0' size='4' name='banlength'  /> days</td></tr>
        <center><tr><td class='postblock'>Ban type:</td><td></center>
            <select name='banType' />
            <option value='warn' />Warning only</option>
            <option value='thisboard' />This board - /" . BOARD_DIR . "/ </option>
            <option value='global' />All boards</option>
            <option value='perma' />Permanent - All boards</option>
            </select>
        </td></tr>
        <tr><td class='postblock'>Public reason:</td><td><textarea rows='2' cols='25' name='pubreason' /></textarea></td></tr>
        <tr><td class='postblock'>Staff notes:</td><td><input type='text' name='staffnote' /></td></tr>
        <tr><td class='postblock'>Append user's comment:</td><td><input type='text' name='custmess' placeholder='Leave blank for USER WAS BAN etc.' /> [ Show message<input type='checkbox' name='showbanmess' /> ] </td></tr>
        <tr><td class='postblock'>After-ban options:</td><td>
            <select name='afterban' />
            <option value='none' />None</option>
            <option value='delpost' />Delete this post</option>
            <option value='delallbyip' />Delete all by this IP</option>
            <option value='delimgonly' />Delete image only</option>
            </select>
        </td></tr>";
        if (valid('admin'))
            $temp .= "
            <tr><td class='postblock'>Add to Blacklist:</td><td>[ Comment<input type='checkbox' name='blacklistcom' /> ] [ Image MD5<input type='checkbox' name='blacklistimage' /> ] </td></tr>";
        $temp .= "<center><tr><td><input type='submit' value='Ban'/></td></tr></center></table></form></body></html>";
        
        return $temp;
    }
	
}

?>
