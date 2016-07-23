<?php

/*
    Ban & ban request submission forms for the admin panel. Currently needs to be rewritten.
*/

class BanishForms {
    
    //Initializes ban/ban requests form
    public function init() {
        global $mysql;
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->canSubmit();
            $this->submit();
        } else  {
            $no = $this->canSubmit();

            if (valid('ban')) {
                return $this->banForm($no); 
            }
            
            if (valid('banrequest')) {
                return $this->requestForm($no);
            }
            
            //At this point, user is not validated for any ban related actions on this board.
            $this->error(S_NOPERM);
        }
    }

    //Ban submission form.
    private function banForm($no) {
        global $mysql, $csrf, $head;

        require_once(CORE_DIR . "/page/head.php");
        $head = new Head;
		$head->info['page']['title'] = "/" . BOARD_DIR . "/ - Ban post #{$no}";
        $head->info['css']['raw'] = array(".banTable { border-spacing: 1px; width:95%; margin:0;} .txtbox {width:100%;} .postblock { padding: 0 5px;} .postHistory { padding: 5px; font-weight:700;} .banTable tr td:first-child {font-weight: bold; padding-right: 5px;");
        
		$temp = $head->generate($noHead = true); //Get head elements without head text
		
        //$host  = $mysql->result("SELECT host FROM " . SQLLOG . " WHERE no='{$no}' AND board='" . BOARD_DIR . "'");
        //$alart = ($host) ? @$mysql->result("SELECT COUNT(*) FROM " . SQLBANNOTES . " WHERE host='{$host}'") : 0;
        $alert = ($alart > 0) ? "<font color='FF101A'><a href=''> {$alart} actions on record this poster! Click to view.</a></font>" : "No previous history for this poster.";
        
        $temp .= "<!---banning #:$no;---><table class='banTable' border='0' cellpadding='0' cellspacing='0' /><form action='" . PHP_SELF_ABS . "?mode=admin&admin=ban' method='POST' />
            <input type='hidden' name='no' value='$no' />";
        $temp .= $csrf->field();
        $temp .= "<tr><td class='postblock'>History</td><td class='postHistory'>$alert</td></tr>
           <tr><td class='postblock'>Type</td><td>
                <select name='banType' />
                <option value='0' />Select ban type:</option>
                <option value='1' />Warning only</option>
                <option value='2' />This board - /" . BOARD_DIR . "/ </option>
                <option value='3' style='background-color:red; font-weight:700;'/>Permanent</option>
                </select>
            </td></tr>
            <tr><td class='postblock'>Length</td><td><input class='txtbox' type='text' name='length' placeholder='3d4m10s, 5year2day, 5m etc.'/></td></tr>
            <tr><td class='postblock'>Reason</td><td><textarea class='txtbox' name='pubreason' placeholder='Public reason for the ban'/></textarea></td></tr>
            <tr><td class='postblock'>Private note</td><td><input class='txtbox' type='text' name='staffnote' placeholder='Private note for staff only'/></td></tr>";
        if (valid('public')) $temp .= "<tr><td class='postblock'>Append</td><td><input type='checkbox' name='showbanmess' title='Public ban, attaches this message to the post' /><input style='width:97%;'  type='text' name='custmess' placeholder='USER WAS BANNED FOR THIS POST' /></td></tr>";
        
        $temp .= "<tr><td class='postblock'>After</td><td>
                <select name='afterban' />
                <option value='0' />None</option>
                <option value='1' />Delete this post</option>
                <option value='2' />Delete image only</option>
                <option value='3' />Delete all by this IP</option>
                </select>
            <input type='submit' value='Submit ban' /></td></tr>";
        /*if (valid('admin'))
        $temp .= "
        <tr><td class='postblock'>Add to Blacklist:</td><td>[ Comment<input type='checkbox' name='blacklistcom' /> ] [ Image MD5<input type='checkbox' name='blacklistimage' /> ] </td></tr>";*/ //Soon.
        $temp .= "</table></form>";
        
        return $temp;
    }
	
	//Ban request form.
    private function requestForm($no) {
        global $csrf, $head;

        require_once(CORE_DIR . "/page/head.php");
        $head = new Head;
        
        $head->info['page']['title'] = "/" . BOARD_DIR . "/ - Ban Request #{$no}";
        $head->info['css']['raw'] = array(".reqTable { border-spacing: 1px; width:95%; margin:0;} .txtbox {width:100%;} .postblock { padding: 0 5px;} .postHistory { padding: 5px; font-weight:700;} .reqTable tr td:first-child {font-weight: bold; padding-right: 5px;");
		$temp = $head->generate($noHead = true); //Get head elements without head text
        
        $temp .= "<!---request on #:$no;---><table class='reqTable' border='0' cellpadding='0' cellspacing='0' /><form action='" . PHP_SELF_ABS . "?mode=admin&admin=ban' method='POST' />
            <input type='hidden' name='no' value='$no' />";
        $temp .= $csrf->field();
        $temp .= "<tr><td class='postblock'>Reason</td><td><textarea class='txtbox' type='text' name='pubreason' placeholder='Public reason for request' required/></textarea></td></tr>
            <tr><td class='postblock'>IP note</td><td><textarea class='txtbox' name='staffnote' /></textarea></td></tr>
            <tr><td class='postblock'>After</td><td>
                <select name='afterban' />
                <option value='0' />None</option>
                <option value='1' selected='selected' />Delete this post</option>
                <option value='2' />Delete image only</option>
                </select>
            </td></tr>
            <tr><td class='postblock'>Warn</td><td>[<input type='checkbox' name='banType' value='1'>Warn only?] <input  type='submit' value='Submit request' /></td></table></form>";
        
        return $temp;
    }
    
    private function canSubmit() {
        global $mysql;
        $no = (isset($_POST['no'])) ? $_POST['no'] : $_GET['no'];

        if (!is_numeric($no)) $this->error("Invalid post");
        $no = (int) $no;
            
        $count = (int) $mysql->result("SELECT COUNT(no) FROM " . SQLLOG . " WHERE no='{$no}' AND board='" . BOARD_DIR . "'");
        if ($count < 1) $this->error("The post has been deleted, or never existed.");
        
        return $no;
    }
    
    //The form has been submitted, time to process the data
    private function submit() {
        require_once("process.php"); //Where all our processing is done
        $process = new Banish;
        return $process->process();
    }
    
    //In house error message for bans.
    private function error($mes) { 
        require_once(CORE_DIR . "/page/head.php");
        $head = new Head;
        $head->info['page']['title'] = $mes;
        $head = $head->generate($noHead = true);
        
        echo $head;
        echo "<br><br><hr><br><br><div style='text-align:center;font-size:24px;font-color:#blue'>$mes<br><br>[<a href='//" . SITE_ROOT_BD . "'>" . S_RELOAD . "</a>]</div><br><br><hr>";
        die("</body></html>");
    }
}