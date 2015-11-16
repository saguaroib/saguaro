<?php

class Banish
{
    
	//IP, active, placedon, board, type, reason, staffnotes
	
    function checkBan($ip) {
       global $mysql;
		$query = $mysql->query("SELECT ip,active FROM " . SQLBANLOG . " WHERE ip='" . $mysql->escape_string($ip) . "' AND active <> '0' LIMIT 1"); 		//check if ban is in table
		if ( !$query ) 		//if active ban exists, stop all further action
			return false;
			
		return true;		//Good to start processing	
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
	
}

?>
