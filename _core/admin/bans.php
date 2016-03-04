<?php
/*

    ==================================== Saguaro Ban Class ========================================
    
        This handles all the fun things ban related.
        Stores ip information 3 different ways now. Absolutely incredible.
        
        Check if a user is banned. If they are, the function will return true. Call with the "redirect" flag to send the user to banned.php.
        require_once(CORE_DIR . "/admin/bans.php);
        $bans = new Banish;
        $bans->isBanned($ip, true); 
    
    ==========================================================================================
    
*/



class Banish {
    
    //If user is banned, return true.
    function isBanned($ip, $redirect = 0) {
        global $mysql;
        
        $query   = $mysql->query("SELECT count(*)>0 FROM " . SQLBANLOG . " WHERE host='$ip' AND (board='" . BOARD_DIR . "' or global=1)");
        $exists = $mysql->result($query, 0, 0);

        $respond = ($redirect) ? header("Location: banned.php") : true; //If isbanned is called with redirect, then return the new header
        return ($exists > 0) ? $respond : false;
    }


    function process($info) {
        global $mysql, $my_log;

		$info = [
			'no' => $mysql->escape_string($_POST['no']),				//Post # being banned for
			'intlength' => $mysql->escape_string($_POST['banlength1']), //Integer ban length
			'strlength' => $mysql->escape_string($_POST['banlength2']), //Unit of time banned for
			'type' => $mysql->escape_string($_POST['banType']), 		//...
			'after' => $mysql->escape_string($_POST['afterban'])		//What to do after ban is processed
			];

		//Let's sort through all the items that are supposed to be integers only.
		foreach ($info as $key => $item) { 
			if (!is_numeric($item))
				die("Invalid POST option!");
		}

		$info['host']	 = $mysql->escape_string($_POST['host']);			//Banned IP
		$info['reason']  = $mysql->escape_string($_POST['pubreason']);		//Publically displayed reason
		$info['areason'] = $mysql->escape_string($_POST['staffnote']);		//Admin notes
		$info['append']  = $mysql->escape_string($_POST['custmess']);		//Message appended to post
		$info['public']  = $mysql->escape_string($_POST['showbanmess']);	//Show message appened to post

		if ($this->isBanned($info['host']))
			die("A ban for this ip already exists");	

		$row = $mysql->fetch_assoc("SELECT name, com FROM " . SQLLOG . " WHERE no='" . $info['no'] "' AND board='" . BOARD_DIR . "'");			

		//Calculate the end time()
		switch($info['strlength']) {
			case '1':
				$info['length'] = strtotime("+ " . $info['intlength'] . " seconds", time());
				break;
			case '2': 
				$info['length'] = strtotime("+ " . $info['intlength'] . " minutes", time());
				break;
			case '3':
				$info['length'] = strtotime("+ " . $info['intlength'] . " days", time());
				break;
			case '4':
				$info['length'] = strtotime("+ " . $info['intlength'] . " weeks", time());
				break;
			case '5':
				$info['length'] = strtotime("+ " . $info['intlength'] . " months", time());
				break;
			default:
				$info['length'] = 0; //Warning
				break;
		}
		
		if($info['type'] == '4') $info['length'] = -1; //Permabanned!
		
		if ($info['after'] || $info['public']) { //Gotta rebuild the thread or index if after-actions are set
			
			if ($info['after']) {
				require_once(CORE_DIR . "/admin/delete.php");
				$delete = new Delete;
				
				switch($info['after']) {
					case '1': //Delete post
						$delete->targeted($post['no'], 'pwd', $imgonly = 0, $automatic = 1, $children = 1, $die = 0, $delhost = '');
						break;
					case '2': //Image only
						$delete->targeted($post['no'], 'pwd', $imgonly = 1, $automatic = 1, $children = 1, $die = 0, $delhost = '');
						break;
					case '3': //All by IP. 
						$delete->targeted($post['no'], 'pwd', $imgonly = 0, $automatic = 1, $children = 1, $die = 0, $info['host']);
						break;
					default: //whoops
						break;
				}
			}
			
			
			$resto = $mysql->result("SELECT last FROM " . SQLLOG . " WHERE no='" . $info['no'] . "'"); //For rebuild selection
			$rebuild = ($resto) ? $resto : $info['no'];
			
			//Append public ban message
			if ($info['public']) {
				$info['append'] = ($info['append']) ? $info['append'] : "(USER WAS BANNED FOR THIS POST)";
				$mysql->query("UPDATE " . SQLLOG . " SET com = CONCAT(com, '<br><strong><font color=\"FF101A\">" . $info['append'] . "</font></strong>') where no='" . $rebuild . "'");
			}
			$my_log->update($rebuild);
		}
        
        $mysql->query( "INSERT INTO " . SQLBANLOG . " (board, global, name, host, com reason, length, admin, reverse, xff, placed) 
		VALUES ( '" . BOARD_DIR .
		"', '" . $info['global'] .
		"', '" . $row['name'] . 
		"', '" . $info['host'] .
		"', '" . $row['com'] . 
		"', '" . $info['reason'] . 
		"', '" . $info['length'] . 
		"', '" . $info['areason'] . 
		"', 'null',
		'null',
		'" . time() ."')");
		
        echo "<script>window.close();</script>"; //Close ban window
        
        return true; //Success
    }
    
    function autoBan($name, $host, $length, $global, $reason, $pubreason = '') {
		//TODO: Update insert query to reflect table column changes
		
        /*global $mysql;
        
        //Get all IP info for the ban
        $reverse = $mysql->escape_string(gethostbyaddr($host));
        $xff     = $mysql->escape_string(getenv("HTTP_X_FORWARDED_FOR"));
        $host    = $mysql->escape_string($host); //Proceed with tidy host
        
        //Already banned, don't insert again
        if ($this->isBanned($host)) { 
            Delete::deleteUploaded();
            die();
        }
        
        if (!$name) $name = S_ANONAME;
        
        if (strpos($name, '</span> <span class="postertrip">!') !== FALSE) {
            $nameparts = explode('</span> <span class="postertrip">!', $name);
            $name  = "{$nameparts[0]} #{$nameparts[1]}";
        }        
        
        $name  = $mysql->escape_string($name);
        $global    = ($global) ? 1 : 0;
        $board     = BOARD_DIR;
        $reason    = $mysql->escape_string($reason);
        $pubreason = $mysql->escape_string($pubreason);

        if ($pubreason) $pubreason .= "<>";

        switch($banlength) {
            case 0: //Auto-warn
                $autowarnq     = $mysql->query("SELECT COUNT(*) FROM " . SQLBANLOG . " WHERE host='$host' AND admin='Auto-ban' AND now > DATE_SUB(NOW(),INTERVAL 3 DAY) AND reason like '%$reason'");
                $autowarncount = $mysql->result($autowarnq, 0, 0);
                if ($autowarncount > 3) $banlength = 14; //14 days
                break;
            case -1: //Permanent
                $length = -1; 
                break;
            default: //Normal ban
                $banlength = (int) $banlength;
                $length = date("Ymd", time() + $banlength * (24 * 60 * 60));
                break;
        }

        $length .= "00" . "00" . "00"; // H:M:S

        if (!$result = $mysql->query("INSERT INTO " . SQLBANLOG . " (board,global,name,host,reason,length,admin) VALUES('$board','$global','$name','$host','$pubreason<b>Auto-ban</b>: $reason','$length','Auto-ban')"))
            echo S_SQLFAIL;
            
        @$mysql->free_result($result);*/
    }
    
    //Ban filing form.
    function form($no) {
        global $mysql, $page;

		$no = $mysql->escape_string($_GET['no']);
		
        $host  = $mysql->result("SELECT host FROM " . SQLLOG . " WHERE no='$no'", 0, 0);
        $alart = ($host) ? $mysql->result("SELECT COUNT(*) FROM " . SQLBANLOG . " WHERE host='" . $host . "'") : 0;
        $alert = ($alart > 0) ? "<b><font color=\"FF101A\"> $alart ban(s) on record for $host!</font></b>" : "No bans on record for IP $host";
        
        $temp .= "<!---banning #:$no; host:$host---><br><table border='0' cellpadding='0' cellspacing='0' /><form action='admin.php?mode=ban' method='POST' />
            <input type='hidden' name='no' value='$no' />
            <input type='hidden' name='ip' value='$host' />
            <tr><td class='postblock'>IP History: </td><td>$alert</td></tr>
            <tr><td class='postblock'>Unban in:</td><td><input type='number' min='0' size='7' name='banlength1'  /> <select name='banlength2' />
                <option value='1' />seconds</option>
                <option value='2' />minutes</option>
                <option value='3' />days</option>
                <option value='4' />weeks</option>
                <option value='5' />months</option>
                </select></td></tr>
            <center><tr><td class='postblock'>Ban type:</td><td></center>
                <select name='banType' />
                <option value='0' />Warning only</option>
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
        
        echo $page->generate($temp, true, true);
    }
	
	//Returns & processes banned.php HTML
	function banScreen() {
		global $page;

		//If ban exists in the table, get the information array. Otherwise, user isn't banned
		if ($this->isBanned($_SERVER['REMORE_ADDR'])) {
			$info = $ban->banInfo();
			$page->headVars['page']['title'] = "You are banned!";
			$page->headVars['css']['extra'] = "banned.css";
		}		

	}
	
    //returns processed ban info array blah blah blah.
    function banInfo() {
		global $mysql;

		$row = $mysql->fetch_assoc("SELECT * FROM " . SQLBANLOG . " WHERE host='$ip'");
		
		$name = "<span class='name'>" . $row['name'] . "</span>";
		$global = ($row['global']) ? "<strong>all boards</strong>" : "<strong>/" . $row['board'] . "/</strong> ";


		return [
			'name' => $name,
			'global' => $global,
			'board' => $row['board'],
			'host'	   => $row['host'],
			'reason' => $row['reason'],
			'placed' => $placed,
			'length'  => $length,
			'expires' => $expires,
			'expstring' => $expiresString
		];
		
    }
    
    function append() {
    
    }
    
}

?>
