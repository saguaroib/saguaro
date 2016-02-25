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
        
        $query   = $mysql->query("SELECT count(*)>0 FROM " . SQLBANLOG . " WHERE host='$ip' AND active=1 AND (board='$board' or global=1)");
        $exists = $mysql->result($query, 0, 0);

        $respond = ($redirect) ? header("Location: banned.php") : true; //If isbanned is called with redirect, then return the new header
        return ($exists > 0) ? $respond : false;
    }


    function process($no, $ip, $length, $global, $reason, $pubreason) {
        global $mysql, $my_log;

        //Append public ban message
        $mysql->query("UPDATE " . SQLLOG . " SET com = CONCAT(com, '<br><b><font color=\"FF101A\">" . $mysql->escape_string($custmess) . "</font></b>') where no='" . $no . "'");

        $my_log->update($no);
        
        echo "<script>window.close();</script>"; //Close ban window
        
        return true; //Success
    }
    
    function autoBan($name, $host, $length, $global, $reason, $pubreason = '') {
        global $mysql;
        
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
            case 0:
                //Auto-warn
                $autowarnq     = $mysql->query("SELECT COUNT(*) FROM " . SQLLOGBAN . " WHERE host='$host' AND admin='Auto-ban' AND now > DATE_SUB(NOW(),INTERVAL 3 DAY) AND reason like '%$reason'");
                $autowarncount = $mysql->result($autowarnq, 0, 0);
                if ($autowarncount > 3) $banlength = 14; //14 days
                break;
            case -1:
                //Permanent
                $length = '0000' . '00' . '00'; // YYYY/MM/DD
                break;
            default:
                //Normal ban
                $banlength = (int) $banlength;
                $length = date("Ymd", time() + $banlength * (24 * 60 * 60));
                break;
        }

        $length .= "00" . "00" . "00"; // H:M:S

        if (!$result = $mysql->query("INSERT INTO " . SQLLOGBAN . " (board,global,name,host,reason,length,admin,reverse,xff) VALUES('$board','$global','$name','$host','$pubreason<b>Auto-ban</b>: $reason','$length','Auto-ban','$reverse','$xff')"))
            echo S_SQLFAIL;
            
        @$mysql->free_result($result);
    }
    
    //Ban filing form.
    function form($no) {
        global $mysql;
        
        $host  = $mysql->result("SELECT host FROM " . SQLLOG . " WHERE no='" . $mysql->escape_string($no) . "'", 0, 0);
        $alart = ($host) ? $mysql->result("SELECT COUNT(*) FROM " . SQLBANLOG . " WHERE ip='" . $host) : 0;
        $alert = ($alart > 0) ? "<b><font color=\"FF101A\"> $alart ban(s) on record for $host!</font></b>" : "No bans on record for IP $host";
        
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
        
        echo $temp;
    }
	
	function banScreen($info) {
		//Returns & processes banned.php HTML
		
	}
    
}

?>
