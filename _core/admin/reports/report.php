<?php

/*
    Handles everything to do with client side report filing.
    Manual report clearing is handled in queue.php.
    Automated report deletion is done when the post is automatically deleted in ../delete.php
*/

class Report {
    public function init() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            return $this->process(); //Form popup has been submitted, file it.
        } else {
            return $this->form(); //User initiated report window.
        }
    }

    //Checks end-user report form
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

        //Has user reached the report limit for the day?
        $query = "SELECT COUNT(no) FROM reports WHERE ip='$host' AND (board='{$board}' OR global=1)";
        if ($mysql->result($query) > REPORT_FLOOD && !valid('reportflood'))
            return $this->error('Report flood limit reached.', $no);

        //Has user reported this post already
        $query = "SELECT COUNT(no) FROM reports WHERE ip='$host' AND no='{$no}' AND board='{$board}'";
        if ($mysql->result($query) > 0) {
            return $this->error("You already reported this post.", $no);
        }

        return $row;
    }

    //Display the report form.
    private function form() {

        $row = $this->canSubmit();
		$no = $row['no'];

        require_once(CORE_DIR . "/page/head.php");
        $head = new Head;

        $head->info['page']['title'] = "Reporting #{$no} on /" . BOARD_DIR . "/ - " . TITLE;
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
<textarea name="note" align="center" placeholder="Additional notes (150 chars)" maxlength="150"></textarea><br><input type="checkbox" name="global" value="1"><b>File as global report?</b><br></td><td>
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

        $row = $this->canSubmit();
		$no = $row['no'];

        if (!is_numeric($_POST['cat']))
            $this->error("Invalid option!", $no);

        $type = (int) $_POST['cat'];
        if ($type < 1 || $type > 5) //User is trying to make their post unreportable.
            $this->error("Invalid option!", $no);

        $global = (isset($_POST['global']) && is_numeric($_POST['global'])) ?  1 : null;

        /*if ($captcha->isValid() !== true) {
            $this->error("You did not solve the captcha correctly.");
        }*/
        
        /*
            cat = 1: Rule violation
            cat = 2: spam
            cat = 3: Illegal content
            cat = 4: CP
        */

        switch($type) {
            case 1:
                $typestring = "rule_count";
                break;
            case 2:
                $typestring = "spam_count";
                break;
            case 3:
                $typestring = "illegal_count";
                break;
            case 4:
                $typestring = "cp_count";
                break;    
            default:
                logme("Report type error post:{$no} board:{$board}"); //Should never happen anyway, but just in case..
                $this->error("NEVER SHOULD HAVE COME HERE!");
                break;
        }
        
        if (isset($_POST['note'])) {
            $note = $_POST['note'];

            if (strlen(trim($note)) > 150)
                $this->error("Your note was too long.", $no);
            $note = $mysql->escape_string(htmlspecialchars($note));
        }
		
        /*
            How report storing works:
            When a post is reported for the first time, assuming it's valid and all, 
            the reported post is json_encoded and stored in the reports table to save queries in the future.
            Each subsequent report only stores the reporter's IP, the post # reported, and increments the count for the type of the report
            in the original report's row. Newer reports do not track the number of times a post is reported. Only the original report does.
            They are all cleared when a post is deleted, or 
        */
        
        
        if (($global && $type > 3) || (!$global && $type < 4)) {
            
            $query = "SELECT COUNT(no) FROM " . SQLREPORTS . " WHERE no='{$no}' AND board='{$board}' AND post<>''";
            if ($mysql->result($query) >= 1) { //This isn't the first time the post has been reported.
                $mysql->query("INSERT INTO " . SQLREPORTS . " (no, board, global, note, ip, reported) VALUES ( '{$no}', '" . BOARD_DIR . "', '{$global}', '{$note}', '{$host}', '" . time() . "')");
                
                $global = ($global) ? " AND global='1' " : null; //If a newer report flagged this post for the global queue, elevate the original report.
                //Also store the new report so you can see who elevated the original to global in case of abuse.
                
                $mysql->query("UPDATE " . SQLREPORTS . " SET {$typestring}={$typestring}+1 {$global} WHERE no='{$no}' AND board='{$board}' AND post<>''");
            } else { //This IS the first time the post has been reported, encode the post for storage.
                $bad = ['host', 'pwd', 'modified', 'last', 'sticky', 'permasage', 'locked']; //Attributes to be stripped from encoded post.
                foreach ($row as $key => $value) {
                    if (is_numeric($value)) { //Convert values to int where applicable
                        $row[$key] = (int) $value;
                    }
                    if (empty($row[$key])) { //Unset empty values. (Posts without images)
                        unset($row[$key]);
                    }
                }
                foreach ($bad as $unset) { //Unset sensitive/useless attributes
                    unset($row[$unset]);
                }
                $row['now'] = explode(" ", $row['now'])[0];
                $mysql->query("INSERT INTO " . SQLREPORTS . " (active,no, board, {$typestring}, global, note, ip, post, reported) VALUES ( '1', '{$no}', '" . BOARD_DIR . "', '1', '{$global}', '{$note}', '{$host}', '" . $mysql->escape_string(json_encode($row)) . "', '" . time() . "')");
            }
        }

        require_once(CORE_DIR . "/page/head.php");
        $head = new Head;
        $head->info['page']['title'] = "Report #{$no} success!";
        $head->info['css']['raw'] = array("body {text-align:center;}");
		//$head->info['js']['script'] = array("reportclose.js");

        $temp = $head->generate($noHead = true);
        $temp .= "<font color='blue' size='5'>Report submitted!</b></font></body>";
        return $temp;
    }

    //Report window gets its own error handling.
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