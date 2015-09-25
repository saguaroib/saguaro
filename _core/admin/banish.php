<?php

class Banish
{
    
	//IP, active, placedon, board, type, reason, staffnotes
	
    function checkBan($ip)
    {
		$ip = mysql_real_escape_string( $ip ) ;        //sanitize values
		$query = mysql_call("SELECT ip,active FROM " . SQLBANLOG . " WHERE ip='" . $ip . "' AND active <> '0' LIMIT 1"); 		//check if ban is in table
		if ( !$query ) 		//if active ban exists, stop all further action
			return false;
			
		return true;		//Good to start processing	
    }
    
	function postOptions($no, $ip, $expires, $banType, $perma, $pubreason, $staffnote, $custmess, $showbanmess, $afterban) {
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
			//delete shit
		}
			
	//}
	
	//function applyBan( $no, $ip, $length, $type, $pubreason, $staffnote, $custmess) {
		
		mysql_call( "INSERT INTO " . SQLBANLOG . " (ip, active, placedon, expires, board, type, reason, staffnotes) 
		VALUES ('" . mysql_real_escape_string( $ip ) . "', 
		'1', 
		UNIX_TIMESTAMP(),
		'" . mysql_real_escape_string( $expires ) . "',
		'" . BOARD_DIR . "', 
		'" . mysql_real_escape_string( $banType ) . "', 
		'" . mysql_real_escape_string( $pubreason ) . "', 
		'" . mysql_real_escape_string( $staffnote ) . "' )");

		if ($custmess)
			mysql_call( "UPDATE " . SQLLOG . " SET com = CONCAT(com, '<br><b><font color=\"FF101A\">" . mysql_real_escape_string( $custmess ) . "</font></b>') where no='" . $no . "'");  
		
	}
	
	function afterBan() {
		//Display information for ban just executed, allow revert option, redirect to admin.
		echo "Banned IP: " . $ip . ", Redirecting. <META HTTP-EQUIV=\"refresh\" content=\"2;URL=" . PHP_ASELF_ABS . "\">";
	}
	
}

?>
