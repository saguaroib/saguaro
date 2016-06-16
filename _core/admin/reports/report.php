<?php

/*
    Handles everything to do with client side report handling.
    Report inserting/removing to/from database is done in process.php
*/

class Report {
    public function init() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            //Form popup has been submitted, file it.
            return $this->process();
        } else {
            //User initiated report window.
            return $this->form();
        }
    }
    
    //Checks for  end-user report form
    private function canSubmit() {
        global $mysql, $my_log, $host;

        $no = (isset($_POST['no'])) ? $_POST['no'] : $_GET['no'];

        if (!is_numeric($no)) $this->error("Invalid post", 0);
        $no = (int) $no;
        $board = BOARD_DIR;
        
        $row = $mysql->fetch_assoc("SELECT * FROM " . SQLLOG . " WHERE no='{$no}' AND board='{$board}'");

        //Post exists?
        if (!isset($row['no']))
            return $this->error("That post doesn't exist.", $no);

        //Trying to report a sticky?
        if ($row['sticky'] > 0) return $this->error("Stop trying to report a sticky!", $no);

        //User is reporting themself?
        if ($host == $row['host']) return $this->error("You can't report your own post!", $no);
        
        //Already reported this ip or is going on a reporting spree?
        $query = "SELECT COUNT(no) FROM reports WHERE ip='$host' AND (board='{$board}' OR global=1)";
        if ($mysql->result($query) > REPORT_FLOOD && !valid('reportflood'))
            return $this->error('Report flood limit reached.', $no);

        $query = "SELECT COUNT(no) FROM reports WHERE ip='$host' AND no='{$no}' AND board='{$board}'";
        if ($mysql->result($query) > 0) {
            return $this->error("You already reported this post, dummy.", $no);
        } 

        return $no;
    }
    
    //Display the report form.
    private function form() {
        
        $no = $this->canSubmit();
        
        require_once(CORE_DIR . "/page/head.php");
        $head = new Head;
        
        $head->info['page']['title'] = "Reporting #{$no} on /" . BOARD_DIR . "/";
        $temp = $head->generate($noHead = true);
        
        require_once(CORE_DIR . "/general/captcha.php");
        $captcha = new Captcha;

        $temp .= '<form action="' . PHP_SELF_ABS . '?mode=report&no=' . $no . '" method="POST">
		<table width="100%">
		<tr><td>
		<fieldset><legend>Report type</legend>
		<input type="hidden" name="no" value="' . $no . '" />
		<input type="radio" name="cat" value="2" checked>Rule violation<br>
        <input type="radio" name="cat" value="1">Spam<br>
		<input type="radio" name="cat" value="3">Illegal content<br>
		<input type="radio" name="cat" value="4">Child pornography<br><br>
<textarea name="note" align="center" placeholder="Additional notes (150 chars)" maxlength="150"></textarea><br><input type="checkbox" name="global" value="true" onclick="globalMsg();"><b>File as global report?</b><br></td><td>
		<center><div style="margin: 0px auto;display:block;" id="saguaroCaptchaContainer"><img src="' . CORE_DIR_PUBLIC . '/general/captcha.php" /><br><input type="text" name="num" size="20" placeholder="Captcha"></div></center></fieldset>
		</td></tr>
		</table>
		<table width="100%" class="alertSplashdown"><tr><td width="240px"></td><td>
		<input type="submit" value="Submit">
		</td></tr></table></center></form></body></html>';
        
        return $temp;
    }
    
    private function process() {
    	global $mysql, $host;
        require_once(CORE_DIR . "/general/captcha.php");
        $captcha = new Captcha;

        $no = $this->canSubmit();

        if (!is_numeric($_POST['cat']))
            $this->error("Invalid option!", $no);

        $type = (int) $_POST['cat'];
        if ($type < 1 || $type > 5) //User is trying to make their post unreportable.
            $this->error("Invalid option!", $no);

        $global = (isset($_POST['global']) && is_numeric($_POST['global'])) ?  1 : null;

        /*if ($captcha->isValid() !== true) {
            die("<head><link rel='stylesheet' type='text/css' href='" . CSS_PATH . "/stylesheets/" . $style . ".css'/></head><body>
        <center><font color=blue size=5>You did not solve the captcha correctly.</b></font><br><br>[<a href='" . PHP_SELF . "?mode=report&no=" . $no . "'>Try again?</a>]</center></body>");
        }
        /*cat = 1: Rule violation
        cat = 2: spam
        cat = 3:  Illegal content
        cat = 4: CP*/
        
        if (isset($_POST['note'])) {
            $note = $_POST['note'];
            
            if (strlen(trim($note)) > 150)
                $this->error("Your note was too long.", $no);
            $note = $mysql->escape_string(htmlspecialchars($note));
        }

        if (($global && $type > 3) || (!$global && $type < 4)) {
            $mysql->query("INSERT INTO reports (no, board, type, global, note, ip) VALUES ( '{$no}', '" . BOARD_DIR . "', '{$type}', '{$global}', '{$note}', '{$host}')");
        }
        
        require_once(CORE_DIR . "/page/head.php");
        $head = new Head;
        $head->info['page']['title'] = "Report #{$no} success!";
        $head->info['css']['raw'] = array("body {text-align:center;}");
        
        $temp = $head->generate($noHead = true);
        $temp .= "<head><link rel='stylesheet' type='text/css' href='" . CSS_PATH . "/" . $style . ".css'/><script>$('div.inlineFrame').remove(); function loaded(){window.setTimeout(CloseMe, 2000);}function CloseMe() {window.close();}</script></head><body onLoad='loaded()'>
	<center><font color=blue size=5>Report submitted! This window will close in 2 seconds...</b></font></center></body>";
        return $temp;
    }
    
    private function error($mes, $no) {
        require_once(CORE_DIR . "/page/head.php");
        $head = new Head;
        
        $head->info['page']['title'] = "$mes";
        $head->info['css']['raw'] = array("body {text-align:center;}");
        
        $temp = $head->generate($noHead = true);
        
        $temp .= "<br><br><font style='color:blue; font-size:xx-large; text-align:center;'>$mes</font>";
        echo $temp;
        die("</body></html>");
    }
}