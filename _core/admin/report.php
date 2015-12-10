<?php

/*      

        Reports class. 
        Eventually revisit this to make it do a less obscene amount of mysql calls per report.
        Perhaps integrate the log cache to cut down on queries.
        
*/


class Report {
    
    function reportProcess() {
    	global $mysql;
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $no = $mysql->escape_string($_GET['no']);
            //Various checks in the popup window before form is filed
            $this->reportPostExists($no);
            $this->reportIsCleared($no);  
            $this->reportCheckIP(BOARD_DIR, $no, $_SERVER['REMOTE_ADDR']);
            $this->reportForm(BOARD_DIR, $_GET['no']); //User passed checks, display form
        } else {
            //Report form has been filled out, POST'ed and can now be filed
            if ($this->reportCheckIP(BOARD_DIR, $no, $_SERVER['REMOTE_ADDR'])) //One last check for people trying to be sneaky.
                $this->error('Please wait a while before reporting more posts.', $no);
            $this->reportSubmit(BOARD_DIR, $_POST['no'], $_POST['cat']);
        }
        die('</body></html>');
    }
    
    function reportGetAllBoard($list = 0) {
    	global $mysql;
        $query = $mysql->query(" SELECT * FROM reports WHERE board='" . BOARD_DIR . "' AND type > 0");
        
        if (!$list) { //If the call is for the oldvalid() alert in admin.php, this will be 1.	
            $active = $mysql->num_rows($query);
            if ($active > 0)
                $active = "<b><font color='red'/>$active Reports!</font></b>";
            else
                $active = "Reports";
        } else {
            $active = $query;
        }
        return $active;
    }
    
    function reportPostExists($no) {
    	global $mysql;
    //I won't dignify retards who report stickies with a SQL query, just give them the post not found error.
        $query = $mysql->query("SELECT * FROM " . SQLLOG . " WHERE no='$no' AND sticky < 1 LIMIT 1");
        if ($mysql->num_rows($query) < 1)
            return $this->error("That post doesn't exist.", $no);
    }
    
    function reportIsCleared($no) {
    	global $mysql;
        $query = $mysql->query("SELECT `no`,`type` FROM reports WHERE no='" . $no . "' AND type='0' LIMIT 1");
        if ($mysql->num_rows($query) > 0)
            return $this->error('This post has been reviewed and cleared.', $no);
    }

    function reportClear($no) {
    	global $mysql;
        
        if (!valid('moderator'))
            $this->error("Permission denied");
        
        $no = $mysql->escape_string($no);
        if ($this->reportPostExists($no)) {
            @$mysql->query("DELETE FROM reports WHERE no='$no'"); //How did you get there? Attempt to clear up the phantom report.
            $this->error("That report/post doesn't exist anymore!");
        }
        //Set report type to inactive if it's been cleared by a mod. 
        //deletePost.php does the deletion when the post is pruned anyway
        $mysql->query("UPDATE reports SET type='0' WHERE no='$no'");
    }
    
    function reportCheckIP($board, $no, $ip) {
    	global $mysql;
        $query = $mysql->query("SELECT host FROM " . SQLLOG . " WHERE no='$no' AND host='$ip' LIMIT 1");
        if ($mysql->num_rows($query) > 0) //Trying to report own post
            return $this->error("You can't report your own post!", $no);

        //Check if the submitting user has already reported this ip or is going on a reporting spree.
        $query = $mysql->query("SELECT * FROM reports WHERE ip='" . $ip . "' AND board='" . $board . "'");
        if ($mysql->num_rows($query) > 3 && !valid('janitor_board')) //Relax there, tattle tale
            return $this->error('Please wait a while before reporting more posts.', $no);
    }
    
    function reportSubmit($board, $no, $type) {
    	global $mysql;
        require_once(CORE_DIR . "/general/captcha.php");
        $captcha = new Captcha;
        
        $style = (NSFW) ? "saguaba" : "sagurichan";
        
        if ($captcha->isValid() !== true) {
            die("<head><link rel='stylesheet' type='text/css' href='" . CSS_PATH . "/stylesheets/" . $style . ".css'/></head><body>
        <center><font color=blue size=5>You did not solve the captcha correctly.</b></font><br><br>[<a href='" . PHP_SELF . "?mode=report&no=" . $no . "'>Try again?</a>]</center></body>");
        }
        /*cat = 1: Rule violation
        cat = 2: Illegal content
        cat = 3: Advertising
        0 = Cleared by moderator, can't report it again. 
        This is not a valid submit option. 
        If the report isn't submitted with either cat 1,2 or 3, it is discarded */
        $host   = $_SERVER['REMOTE_ADDR'];
        $cboard = $mysql->escape_string($board);
        $cno    = $mysql->escape_string($no);
        $ctype  = $mysql->escape_string($type);
        $mysql->query("INSERT INTO reports (`num`, `no`, `board`, `type`, `time`, `ip`) VALUES ( '" . rand() . "', '" . $cno . "', '" . $cboard . "', '" . $ctype . "', NOW(), '" . $host . "') ");
        
        echo "<head><link rel='stylesheet' type='text/css' href='" . CSS_PATH . "/" . $style . ".css'/><script>function loaded(){window.setTimeout(CloseMe, 3000);}function CloseMe() {window.close();}</script></head><body onLoad='loaded()'>
	<center><font color=blue size=5>Report submitted! This window will close in 3 seconds...</b></font></center></body>";
    }
    
    function reportFormHead($no) {
    	global $mysql;
        $style = (NSFW) ? "saguaba" : "sagurichan";
        
        echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		<html>
		<head>
		<title>Report Post #' . $no . '</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" type="text/css" href="' . CSS_PATH . '/stylesheets/' . $style . '.css"/>
		<style>fieldset { margin-right: 25px; }</style>
		</head>';
    }
    
    function reportForm($board, $no) {
    	
        require_once(CORE_DIR . "/general/captcha.php");
        $captcha = new Captcha;
        if (RECAPTCHA && defined(RECAPTCHA_SITEKEY))
            $temp .= "<div style='margin: 0px auto;display:block;' id='saguaroCaptchaContainer'><script src='//www.google.com/recaptcha/api.js'></script><div class='g-recaptcha' data-sitekey='" . RECAPTCHA_SITEKEY . "'></div>";
        else
            $temp .= "<div style='margin: 0px auto;display:block;' id='saguaroCaptchaContainer'><img src='" . CORE_DIR_PUBLIC . "/general/captcha.php' /><br><input type='text' name='num' size='20' placeholder='Captcha'></div>";
        //Taken from parley who probably took it from 4chan anyway. Yolo.
        $this->reportFormHead($no);
        echo '
		<body>
		<form action="' . PHP_SELF_ABS . '?mode=report&no=' . $no . '" method="POST">
		<table width="100%">
		<tr><td>
		<fieldset><legend>Report type</legend>
		<input type="hidden" name="no" value="' . $no . '" />
		<input type="radio" name="cat" value="2" checked>Rule violation<br/>
		<input type="radio" name="cat" value="3">Illegal content<br/>
		<input type="radio" name="cat" value="1">Spam
		</fieldset>
		</td>
		<td>' . $temp . '
		</td></tr>
		</table>
		<table width="100%"><tr><td width="240px"></td><td>
		<input type="submit" value="Submit">
		</td></tr></table>
		</center>
		</form>
		<br>
		<div class="rules"><u>Note</u>: Submitting frivolous reports will result in a ban. When reporting, make sure that the post in question violates the global/board rules, or contains content illegal in the United States.</div>
		</body>
		</html>';
        
    }
    
    function reportList() {
        global $mysql;
        if (!$active = $mysql->query(" SELECT * FROM reports WHERE board='" . BOARD_DIR . "' AND type>0 ORDER BY `type` DESC "))
            echo S_SQLFAIL;
        $j = 0;
        
        $temp .= "<br><br><div class='managerBanner'>Active reports for /" . BOARD_DIR . "/ - " . TITLE . "</div>";
        $temp .= "<table class='postlists'>";
        $temp .= "<tr class=\"postTable head\"><th>Clear Report</th><th>Post Number</th><th>Board</th><th>Reason</th><th>Reporting IP</th><th>Post info</th>";
        $temp .= "</tr>";
        
        while ($row = $mysql->fetch_array($active)) {
            $j++;

            switch ($row['type']) {
                case '1':
                    $type = 'Spam';
                    break;
                case '2':
                    $type = 'Rule Violation';
                    break;
                case '3':
                    $type = 'Illegal Content';
                    break;
                default:
                    $type = 'Type Error';
                    break;
            }
            $class = ($j % 2) ? "row1" : "row2"; //BG color
            
            $temp .= "<tr class='$class'><td><input type='button' text-align='center' onclick=\"location.href='" . PHP_ASELF_ABS . "?mode=reports&no=" . $row['no'] . "';\" value='Clear' /></td>";
            $temp .= "<td>" . $row['no'] . "</td><td>/" . $row['board'] . "/</td><td>$type</td><td>" . $row['ip'] ." </td>
            <td><input type='button' text-align='center' onclick=\"location.href='" . PHP_ASELF_ABS . "?mode=more&no=" . $row['no'] . "';\" value=\"Post Info\" /></td>";
            $temp .= "</tr>";
            $temp .= "<link rel='stylesheet' type='text/css' href='" . CSS_PATH . "/stylesheets/img.css' />";
            
        }
        
        return $temp;
    }
    
    function error($mes, $no) {
        
        $this->reportFormHead($no);
        echo "<br /><br /><center><font color=blue size=5>$mes</b></font></center>";
        die("</body></html>");
    }
}

?>
