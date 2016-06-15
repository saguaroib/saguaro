<?php
/*      
    Handles all post reporting related functions.
*/

class Report {
    
    
    //BEGIN FRONT END (end user) FUNCTIONS

    
    /*function process() {
    	global $mysql;
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            //User initiated report window.
            $this->canSubmit($_GET['no']);
            $this->showForm($_GET['no']);
        } else {
            //Form popup has been submitted, file it.
            $this->canSubmit($_POST['no']);
            $this->formSubmit();
        }
        die('</body></html>');
    }*/
    
    //Checks for  end-user report form
    function canSubmit($no) {
        global $mysql, $my_log, $host;

        if (!is_numeric($no)) $this->error("Invalid post", 0);
        $no = (int) $no;
        $board = BOARD_DIR;
        
        $row = $mysql->fetch_assoc("SELECT * FROM " . SQLLOG . " WHERE no='{$no}' AND board='" . BOARD_DIR . "'");

        

        $query = "SELECT COUNT(*) FROM reports WHERE ip='$host' AND (board='$board' OR global=1)";

        //Post exists?
        if (!isset($log[$no]))
            return $this->error("That post doesn't exist.", $no);

        //Trying to report a sticky?
        if ($log[$no]['sticky'] > 0) return $this->error("Stop trying to report a sticky!", $no);

        //User is reporting themself?
        if ($host == $log[$no]['host']) return $this->error("You can't report your own post!", $no);
        
        //Already reported this ip or is going on a reporting spree?
        if ($mysql->result($query) > REPORT_FLOOD && !valid('reportflood'))
            return $this->error('Report flood limit reached.', $no);

        $query = "SELECT COUNT(*) FROM reports WHERE ip='$host' AND no='$no' AND board='$board'";
        if ($mysql->result($query) > 0) {
            return $this->error("You already reported this post, dummy.", $no);
        } 
        
        /* 
        //Has report been cleared?
        $query = "SELECT `no`,`type` FROM reports WHERE no='" . $no . "' AND type='0' LIMIT 1";
        if ($mysql->num_rows($query) > 0)
            return $this->error('This post has been reviewed and cleared.', $no);
        */

    }
    
    function formSubmit() {
    	global $mysql, $host;
        require_once(CORE_DIR . "/general/captcha.php");
        $captcha = new Captcha;
        
        $style = (NSFW) ? "saguaba" : "sagurichan";
        $no = (int) $_POST['no'];
        $type = (int) $_POST['cat'];
        $global = (isset($_POST['global1'])) ?  1 : null;
        
        /*if ($captcha->isValid() !== true) {
            die("<head><link rel='stylesheet' type='text/css' href='" . CSS_PATH . "/stylesheets/" . $style . ".css'/></head><body>
        <center><font color=blue size=5>You did not solve the captcha correctly.</b></font><br><br>[<a href='" . PHP_SELF . "?mode=report&no=" . $no . "'>Try again?</a>]</center></body>");
        }
        /*cat = 1: Rule violation
        cat = 2: spam
        cat = 3:  Illegal content
        cat = 4: CP*/
        
        if (isset($_POST['note'])) {
            if (strlen($_POST['note']) > 150)
                $this->error("Your note was too long.", $no);
            $note = $mysql->escape_string(htmlentities($_POST['note']));
        }

        if (!is_int($type) ||  $type < 1 || $type > 5) //User is trying to make their post unreportable.
            $this->error("Invalid post option!", $no);
        
        if (($global && $type > 3) || (!$global && $type < 4)) {
            $mysql->query("INSERT INTO reports (no, board, type, global, note, ip) VALUES ( '" . $no . "', '" . BOARD_DIR . "', '" . $type . "', '" . $global . "', '" . $note . "', '" . $host . "')");
        }
        echo "<head><link rel='stylesheet' type='text/css' href='" . CSS_PATH . "/" . $style . ".css'/><script>$('div.inlineFrame').remove(); function loaded(){window.setTimeout(CloseMe, 2000);}function CloseMe() {window.close();}</script></head><body onLoad='loaded()'>
	<center><font color=blue size=5>Report submitted! This window will close in 2 seconds...</b></font></center></body>";
    }
    
    private function showForm($no) {
    	require_once(CORE_DIR . "/page/head.php");
        $head = new Head;
        
        require_once(CORE_DIR . "/general/captcha.php");
        $captcha = new Captcha;
        $temp .= "<center><div style='margin: 0px auto;display:block;' id='saguaroCaptchaContainer'><img src='" . CORE_DIR_PUBLIC . "/general/captcha.php' /><br><input type='text' name='num' size='20' placeholder='Captcha'></div></center>";
        echo '
		<body>
		<form action="' . PHP_SELF_ABS . '?mode=report&no=' . $no . '" method="POST">
		<table width="100%">
		<tr><td>
		<fieldset><legend>Report type</legend>
		<input type="hidden" name="no" value="' . $no . '" />
		<input type="radio" name="cat" value="2" checked>Rule violation<br>
        <input type="radio" name="cat" value="1">Spam<br>
		<input type="radio" name="cat" value="3">Illegal content<br>
		<input type="radio" name="cat" value="4">Child pornography<br><br>
<textarea name="note" align="center" placeholder="Additional notes (150 chars)" maxlength="150"></textarea><br><input type="checkbox" name="global1" value="true" onclick="globalMsg();"><b>File as global report?</b><br></td><td>
		' . $temp . '</fieldset>
		</td></tr>
		</table>
		<table width="100%" class="alertSplashdown"><tr><td width="240px"></td><td>
		<input type="submit" value="Submit">
		</td></tr></table></center></form></body></html>';
    }
    //END FRONT END REPORT FUNCTIONS

    /*//BEGIN ADMIN PANEL FUNCTIONS
    function init() {
        global $page, $table;
        
        if (!valid('janitor')) error(S_NOPERM);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->clearNum();
        } else {
            $html = $table->reportTable();
            echo $page->generate($html, true, false);
        }
    }
    
    //Outputs json for rendering report queue
    public function reportJson() { 
        global $mysql;
        $proceed = valid('janitor');
        $ret = ($proceed) ? array("reply" => "ok") : array("reply" => "bad");
        if (!$proceed) error(S_NOPERM);
        $ret['result'] = array();
        $board = $mysql->escape_string($_GET['b']);
        $res = $mysql->query("SELECT * FROM reports WHERE type<>0 AND board='$board' ORDER BY type DESC");
        while ($row = $mysql->fetch_assoc($res)) {
            $result = array(
                "no" => $row['no'],
                "board" => $row['board'],
                "type" => $row['type'],
                "time" => $row['reported']
            );
            array_push($ret['result'], $result);
        }
        return json_encode($ret);
    }*/
    
    /*//Returns counts for active reports in json
    public function countJson() { //Displays counts for report types for a specific board
        global $mysql;
        $proceed = valid('janitor');
        $ret = ($proceed) ? array("reply" => "ok") : array("reply" => "bad");
        if (!$proceed) error(S_NOPERM);

        $board = $mysql->escape_string($_GET['b']);
        $ruleCount = (int) $mysql->result("SELECT COUNT(type) FROM reports WHERE type<4 AND board='$board'");
        $illegalCount = (int) $mysql->result("SELECT COUNT(type) FROM reports WHERE type>3 AND board='$board'");
        $result = array(
                "rule" => $ruleCount,
                "illegal" => $illegalCount
        );
        array_push($ret['result'], $result);

        return json_encode($ret);
    }*/
    
    /*//Counts active reports for admin panel
    public function countReports($board = 0) {
    	global $mysql;
        if (!valid('user')) error(S_NOPERM);
        $board = $mysql->escape_string($board);
        $query = (!$board && valid('global')) ? "SELECT COUNT(*) FROM reports WHERE global<>0" : "SELECT COUNT(*) FROM reports WHERE board='$board' AND type>0";
        $active = $mysql->result($query);
        $active = ($active) ? "<b><font color='red'/>$active reports!</font></b>" : "No reports";
        
        return $active;
    }*/
    
    //Clears a report
    /*function clearNum($no, $board) {
    	global $mysql, $csrf;
        
        if (!valid('janitor')) $this->error(S_NOPERM); 
        if (!$csrf->validate()) $this->error(S_RELOGIN);
        
        $no = (int) $no;
        $board = $mysql->escape_string($board);

        //Set report type to *inactive* if it's been cleared by a mod. 
        // _core/admin/delete.php deletes the report(s) from the queue when the post is removed
        if (is_int($no)) $mysql->query("UPDATE reports SET type='0' WHERE no='$no' AND board='$board'");
        
        return true;
    }*/
    private function error($mes, $no) {
        
        $this->reportFormHead($no);
        echo "<br><br><font style='color:blue; font-size:large; text-align:center;'>$mes</font>";
        die("</body></html>");
    }
}

?>
