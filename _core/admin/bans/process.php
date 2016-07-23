<?php

/*
    Bans, ban requests and automatic system bans are processed here.
    Information is either recieved from the forms in forms.php, 
    or automatically filed by the system from regist.php.
*/

class Banish {
    
    //Manual ban/ban requests are done here.
    public function process() {
        
        if (valid('ban')) {
            return $this->ban();
        }
        if (valid('banrequest')) {
            return $this->banRequest();
        }
        //User isn't valid for bans at this point.
        error(S_NOPERM); 
    }
    
    private function ban() {
        global $mysql, $my_log, $csrf;
        
        if (!$csrf->validate()) error(S_RELOGIN);
        
    }
        
        
    private function ban() {    
        global $mysql, $my_log, $csrf;

        

        if ($_GET['a'] == "ban") {
            if (!valid('ban')) {error(S_NOPERM);}
            $info = [
                'no'        => $mysql->escape_string($_POST['no']),             //Post # being banned for
                'length'    => $mysql->escape_string($_POST['length']),            //Integer ban length
                'type'      => $mysql->escape_string($_POST['banType']),        //...
                'after'     => $mysql->escape_string($_POST['afterban']),
                'pubreason' => $mysql->escape_string($_POST['pubreason']),
                'staffnote' => $mysql->escape_string($_POST['staffnote']),
                'public'    => $mysql->escape_string($_POST['showbanmess']),
                'custmess'  => $mysql->escape_string($_POST['custmess'])
                //What to do after ban is processed
            ];

            $info['reason']  = $mysql->escape_string($_POST['pubreason']);		//Publically displayed reason
            $info['areason'] = $mysql->escape_string($_POST['staffnote']);		//Admin notes
            $info['append']  = $mysql->escape_string($_POST['custmess']);		//Message appended to post
            $info['public']  = $mysql->escape_string($_POST['showbanmess']);	//Show message appened to post

            if ($this->isBanned($info['host']))
                die("This IP is already banned.");	

            $post = json_encode($mysql->fetch_row("SELECT * FROM " . SQLLOG . " WHERE no='" . $info['no'] . "' AND board='" . BOARD_DIR ."'"));			

            //Calculate the end time(). Thanks infinity/tinyboard (http://github.com/ctrlcctrlv/infinity/tree/master/inc/bans.php)	
            if ($info['type'] != "1") { //Expiring ban.
                if (preg_match('/^((\d+)\s?ye?a?r?s?)?\s?+((\d+)\s?mon?t?h?s?)?\s?+((\d+)\s?we?e?k?s?)?\s?+((\d+)\s?da?y?s?)?((\d+)\s?ho?u?r?s?)?\s?+((\d+)\s?mi?n?u?t?e?s?)?\s?+((\d+)\s?se?c?o?n?d?s?)?$/', $info['length'], $matches) !== true)
                    die("Invalid length format!");
                $expire = 0;
                if (isset($matches[2])) $expire += $matches[2]*60*60*24*365;    //Years
                if (isset($matches[4])) $expire += $matches[4]*60*60*24*30;     //Months
                if (isset($matches[6])) $expire += $matches[6]*60*60*24*7;      //Weeks
                if (isset($matches[8]))	$expire += $matches[8]*60*60*24;        //Days
                if (isset($matches[10]))$expire += $matches[10]*60*60;          //Hours
                if (isset($matches[12]))$expire += $matches[12]*60;             //Minutes
                if (isset($matches[14]))$expire += $matches[14];                //Seconds
                $info['length'] = time() + $expire;
                
                if($info['type'] == '3') $info['length'] = -1; //Permabanned!
            } else {
                $info['length'] = 0; //Set erronuous/tampered forms to warns by default.
            }
            
            $mysql->query("INSERT INTO " . SQLBANLOG . " (active, board, host, reason, staffreason, expires, placedby, placedon) 
            VALUES ( '1', 
            '" . BOARD_DIR . 
            "', '" . $info['host'] .
            "', '" . $info['reason'] . 
            "', '" . $info['length'] . 
            "', '" . $info['areason'] . 
            "', '" . time() ."')");
            
            if ($info['after'] || $info['public']) { //Gotta rebuild the thread & index if after-actions are set
                
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
                        default: //what is going on here
                            //breakfast
                            break;
                    }
                }
                
                
                @$resto = $mysql->result("SELECT last FROM " . SQLLOG . " WHERE no='" . $info['no'] . "'"); //For rebuild selection
                $rebuild = ($resto) ? $resto : $info['no'];
                
                //Append public ban message
                if ($info['public'] && valid('publicban')) {
                    $info['append'] = ($info['append']) ? $info['append'] : "(USER WAS BANNED FOR THIS POST)";
                    $mysql->query("UPDATE " . SQLLOG . " SET com = CONCAT(com, '<br><strong><font color=\"FF101A\">" . $info['append'] . "</font></strong>') where no='" . $rebuild . "'");
                }
                //$my_log->update($rebuild);
            }
            
            return true;
        }
        
        return true; //Success
    }
    
    //Autoban function - WIP
    public function autoBan($name, $host, $length, $global, $reason, $pubreason = '') {
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

        if (!$result = $mysql->query("INSERT INTO " . SQLBANLOG . " (board,global,name,host,reason,length,admin) VALUES('$board','$global','$name','$host','$pubreason<strong>Auto-ban</b>: $reason','$length','Auto-ban')"))
            echo S_SQLFAIL;
            
        @$mysql->free_result($result);*/
    }
    
}

?>
