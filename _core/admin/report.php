<?php
/*      
    ============================= MEME HEADER ============================= 
        Saguaro reports class. Handles everything related to.....reports.
        What the HELL was going on here?
        
        Cleaned up and optimized. somewhat.
        Next time on reports rewriting: A log style cache that'll let me do fancier things.
        
        Still absolute garbage. The htmlolocaust is coming, heil templates
    =======================================================================
*/

class Report {
    
    function process() {
    	global $mysql;
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            //User initiated report window.
            $this->canSubmit($_GET['no']);
            $this->showForm($_GET['no']);
        } else {
            //Form popup has been submitted, file it.
            $this->formSubmit($_POST['no'], $_POST['cat']);
        }
        die('</body></html>');
    }
    
    function countBoard() {
    	global $mysql;
        
        $query = " SELECT COUNT(*) FROM reports WHERE board='" . BOARD_DIR . "' AND type<>0";
        $active = $mysql->result($query, 0, 0);
        $active = ($active) ? "<b><font color='red'/>$active Reports!</font></b>" : "Reports";
        
        return $active;
    }
    
    function canSubmit($no) {
        global $mysql, $my_log;

        $my_log->update_cache(0);
        $log = $my_log->cache;

        $host = $mysql->escape_string($_SERVER['REMOTE_ADDR']);
        $no = $mysql->escape_string($no);
        $board = BOARD_DIR;

        $query = "SELECT * FROM reports WHERE ip='$host' AND board='$board'";

        //Post exists?
        if (!isset($log[$no]))
            return $this->error("That post doesn't exist.", $no);

        //Trying to report a sticky?
        if ($log[$no]['sticky'] > 0) return $this->error("Stop trying to report a sticky!", $no);

        //User is reporting themself?
        if ($host == $log[$no]['host']) return $this->error("You can't report your own post!", $no);

        //Already reported this ip or is going on a reporting spree?
        if ($mysql->num_rows($query) > REPORT_FLOOD && !valid('reportflood'))
            return $this->error('Report flood limit reached.', $no);

        /* Handled in 
        //Has report been cleared?
        $query = "SELECT `no`,`type` FROM reports WHERE no='" . $no . "' AND type='0' LIMIT 1";
        if ($mysql->num_rows($query) > 0)
            return $this->error('This post has been reviewed and cleared.', $no);
        */

    }

    function clearNum($no) {
    	global $mysql;
        
        if (!valid('moderator'))
            $this->error(S_NOPERM);
        
        $no = $mysql->escape_string($no);

        //Set report type to *inactive* if it's been cleared by a mod. 
        // _core/admin/delete.php deletes the report from the queue when the post is removed
        $mysql->query("UPDATE reports SET type='0' WHERE no='$no'");
        
        return true;
    }
    
    function formSubmit($no, $type) {
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
        0 = Cleared by moderator, can't report it again*/
        
        if ($type == "0") //User is trying to make their post unreportable.
            $this->error("Invalid post option!", $no);
        
        $host   = $_SERVER['REMOTE_ADDR'];
        $no    = $mysql->escape_string($no);
        $type  = $mysql->escape_string($type);
        $mysql->query("INSERT INTO reports (`no`, `board`, `type`, `ip`) VALUES ( '" . $no . "', '" . BOARD_DIR . "', '" . $type . "','" . $host . "')");
        
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
    
    function showForm($no) {
    	
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
    
    function displayTable() {
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
