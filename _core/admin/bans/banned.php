<?php

/*
    This handles all parsing, processing and output related to ?mode=banned,
    the ban information screen shown when a banned user tries to post.
*/

require_once("check.php");
class BanishScreen extends BanishCheck {
    
    //Formats banned.php HTML
	public function init($host) {
        require_once(CORE_DIR . "/page/page.php"); //Init page class. repod whispers "finally" somehwere.
        $page = new Page;

        $page->headVars['page']['title'] = "You are not banned!";
        $page->headVars['css']['sheet'] = array("/stylesheets/banned.css");

        //If ban exists in the table, get the information array. Otherwise, user isn't banned
		if ($this->isBanned($host, false)) {
			$info = $this->getInfo($host);

            $page->headVars['page']['title'] = "You have been" . $info['type'] . "!";

            if ($info['append']) $this->append($host);
            
            if ($info['type'] === 'warned') {
                $temp = '<div class="container"><div class="header">You have been ' . $info['type'] . '! :~:</div><div class="banBody">';
                $temp .= '<p>You were ' . $info['type'] . ' on ' . $info['global'] . ' for the following reason: </p><p>' . $info['reason'] . '</p><br>
                        The ban was filed on your post (without image):<br><br> ' . $info['post'] . '<br><hr />
                        <p>This warn was placed on ' . $info['placed'] . '. Now that you have seen it, you should be able to post again. 
                        <p>Please review the board rules and be aware that further rule violations can result in an extended ban.</p><br />                        
                        <br/>This action was filed for the following IP address: ' . $info['host'] . '</div>';
                return $temp;
            } else {
                $temp .= '<div class="container"><div class="header">You have been ' . $info['type'] . '! :~:</div><div class="banBody">';
                
                $expired = ($info['append']) ? ". Your ban is now lifted and you should be able to continue posting. Please be review and be mindful of the board rules to prevent future bans" :  ' and will expire on: ' . $info['expires'] . $info['length'];
                
                $temp .= '<p>You were ' . $info['type'] . ' on ' . $info['global'] . ' for the following reason: </p><p>' . $info['reason'] . ' .</p><br>
                        The ban was filed on your post (without image):<br><br> ' . $info['post'] . '<br><hr />
                        <p>This ban was placed on ' . $info['placed'] . $expired . '  
                        <br>This action was filed for the following IP address: ' . $info['host'] . '</div>';
                
                return $page->generate($temp);
            }
		} else {
            $page->headVars['page']['title'] = "You are not banned!";
			
            $temp = '<div class="container"><div class="header">You are not banned!</div><div class="banBody">You are not banned from posting.<div class="return"><hr>[<a href="/' . BOARD_DIR . '">Return</a>]</div></div></div>';
            
            return $page->generate($temp);
        }
        
        return "There was an issue retrieving ban information.";
	}
    
    //Returns ban info array blah blah blah.
    private function getInfo($host) {
		global $mysql;

		$row = $mysql->fetch_assoc("SELECT * FROM " . SQLBANLOG . " WHERE host='$host' AND active='1'");
		
		$post = "<span class='post reply'style='border:1px solid black;'><input type='checkbox'>" . $row['name'] . "<br><blockquote>" . $row['com'] . "</blockquote></span>";
		$global = ($row['global']) ? "<strong>all boards</strong>" : "<strong>/" . $row['board'] . "/</strong> ";
		$host = "<span class='bannedHost' >" . $host . "</span>";
		$reason = "<span class='reason'>" . $row['reason'] . "</span>";
		$placed = "<strong>" . date("l, F d, Y \(H:m:s\)" , $row['placed']) . "</strong>";
		$expires = "<strong>" . date("l, F d, Y \(H:m:s\)", $row['length']) . "</strong>";
		$appendFlag = false;
		switch($row['length']) { //Do calculation for the time difference...
			case 0:
				$type = "warned";
				$appendFlag = true;
				break;
			case -1:
				$type = "permanently banned";
				$expires = "<strong>never</strong";
                $length = '.';
				break;
			default:
				$type = "banned";
				$clength = $row['length'] - time();
				if ($clength <= 0) $appendFlag = true;
				$length = ", which is <strong>" . $this->calculate_age($row['length'], $row['placed']) . "</strong> from now. ";
				break;
		}

		return [
            'global'    => $global,
            'post'      => $post,
            'board'     => $row['board'],
            'host'      => $host,
            'reason'    => $reason,
            'placed'    => $placed,
            'length'    => $length,
            'type'      => $type,
            'append'    => $appendFlag,
            'expires'   => $expires
		];
		
    }
    
    //Removes ban from table.
    private function append($host) {
        global $mysql;
        
        $row = $mysql->fetch_assoc("SELECT host, length, com, reason, admin FROM " . SQLBANLOG . " WHERE host='$host'");
        //Remove ban fron table
        $mysql->query("DELETE FROM " . SQLBANLOG . " WHERE host='$host'");
        //Insert into notes table
        switch($row['length']) {
            case 0:
                $row['length'] = "warn";
                break;
            case -1:
                $row['length'] = "permanent";
                break;
            default:
                $row['length'] = "ban";
                break;
        }
        $mysql->query("INSERT INTO" . SQLBANNOTES . " (board, host, type, com, reason, admin) VALUES ('" 
        . $row['board']. "', '" 
        . $row['host']. "', '"
        . $row['length']. "', '" 
        . $row['com']. "', '" 
        . $row['reason']. "', '" 
        . $row['admin']. "')");
        
        return true;
    }
    
    //Calculate time units from UNIX timestamps
	public function calculate_age($timestamp, $comparison) {
        require_once(CORE_DIR . "/general/calculate_age.php");
        $calculate = new CalculateAge;
        return $calculate->age($timestamp, $comparison);
    }
}