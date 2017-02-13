<?php
/*
    Generates the post form.
    Needs to be heavily revised.
    $postform->format()
        Generates a post form for a new thread.
    $postform->format(1)
        Generates a post form to reply to a thread (1).
*/

class PostForm {
    public $ribbon = [];

    function format($resno = null, $admin = false) {
        //echo debug_backtrace()[1]['function'];

        $resno = (is_numeric($resno)) ? $resno : null;

        $maxbyte = MAX_KB * 1024;
        $temp = "";

        if ($resno) $temp .= "<div class='theader'>" . S_POSTING . "</div>\n";

        $temp .= "<form id='contribform' action='" . PHP_SELF_ABS . "' method='post' name='post' enctype='multipart/form-data'>";

        $temp .= "<input type='hidden' name='mode' value='regist'><input type='hidden' name='MAX_FILE_SIZE' value='" . $maxbyte . "'>";

        if ($resno)
            $temp .= "<input type='hidden' name='resto' value='" . $resno . "'>";

        $temp .= "<table>";

        if (!FORCED_ANON) //Name
            $temp .= "<tr data-type='Name'><td class='postblock' align='left'>" . S_NAME . "</td><td align='left'><input type='text' name='name' size='28'></td></tr>";

        $temp .= "<tr data-type='Email'><td class='postblock' align='left'>" . S_EMAIL . "</td><td align='left'><input type='text' name='email' size='28'>";

        if (!$resno) //Subject if a new thread.
             $temp .= "</td></tr><tr data-type='Subject'><td class='postblock' align='left'>" . S_SUBJECT . "</td><td align='left'><input type='text' name='sub' size='35'>";

        $temp .= "<input type='submit' value='" . S_SUBMIT . "'></td></tr>";

        $temp .= "<tr id='comrow' data-type='Comment'><td class='postblock' align='left'>" . S_COMMENT . "</td><td align='left'><textarea id='comtxt' name='com' cols='34' rows='4'></textarea></td></tr>";

        if (BOTCHECK) { //Captcha
            if (RECAPTCHA) {
                $temp .= "<tr id='captchaRow'><td class='postblock' id='captcha' align='left'>Verification</td><td><script src='//www.google.com/recaptcha/api.js'></script><div class='g-recaptcha' data-sitekey='" . RECAPTCHA_SITEKEY ."'></div></tr>";
            } else {
                $temp .= "<tr id='captchaRow'><td class='postblock' id='captcha' align='left'><img src='" . CORE_DIR_PUBLIC . "/general/captcha.php' /></td><td align='left'><input type='text' name='num' size='28'></td></tr>";
            }
        }

        //File selection
        $temp .= "<tr data-type='File'><td class='postblock' align='left'>" . S_UPLOADFILE . "</td><td><input type='file' name='upfile[]' accept='image/*|.webm' size='35' multiple='multiple'>";

        if (NOPICBOX && !SPOILER)
            $temp .= "[<label><input type='checkbox' name='textonly' value='on'>" . S_NOFILE . "</label>]</td></tr>";

        /*if (SPOILER && !NOPICBOX) //Spoiler checkbox
            $temp .= "[<label><input type='checkbox' name='spoiler' value='spoiler'>" . S_SPOILERS . "</label>]</td></tr>";
        else*/
            $temp .= "</td></tr>";

        //Deletion password entry
        $temp .= "<tr><td align='left' class='postblock' id='delField' align='left'>" . S_DELPASS . "</td><td align='left'><input type='password' name='pwd' size='8' maxlength='8' value='' />" . S_DELEXPL . "</td></tr>";

        $temp .= "</tbody>";
        $temp .= "<tfoot><tr><td colspan='2'><div id='postFormError'></div></td></tr></tfoot>";
        $temp .= "</table></form>";

        if (defined('ENABLE_BLOTTER') && ENABLE_BLOTTER)
            $temp .= $this->generateBlotter();
        
        if (ENABLE_ADS) $temp .= ADS_BELOWFORM . "<hr>";
            
        $temp .= (get_cached("news") != "") ? "<hr><div id='globalMessage' class='globalNews desktop'>" . get_cached("news") . "</div><hr>" : "";
        $temp .= $this->afterForm($resno);

        return $temp;
    }
    
    private function generateBlotter() {
        static $blotter;
        
        if (!isset($blotter)) {
            global $mysql;
            $blotter .= '<table id="blotter" class="desktop"><thead><tr><td colspan="2"><hr class="aboveMidAd"></td></tr></thead><tbody id="blotter-msgs">';
            $query = $mysql->query("SELECT * FROM " . SQLRESOURCES . " WHERE type='blotter' ORDER BY ts ASC LIMIT 3");
            while($row = $mysql->fetch_assoc($query)) {
                $blotter .= "<tr><td data-utc='{$row['ts']}' class='blotter-date'>" . date("m/d/y", $row['ts']) . "</td>";
                $blotter .= "<td class='blotter-content'>{$row['message']}</td></tr>";
            }
            $blotter .= '</tbody></table>';
        }
        return $blotter;
    }

    private function afterForm($resno) {

        //Navigation bar above thread, below postform.
        $temp .= ($resno) ? "<div class='navLinks' />" : "<div id='ctrl-top' class='desktop'>";
        if ($resno) $temp .= "[<a class='navButton' href='/" . BOARD_DIR  . "'>" . S_RETURN . "</a>] ";

        foreach($this->ribbon as $item) {
            $temp .= "[<a class='navButton' href='{$item['link']}'>{$item['name']}</a>] ";
        }
        $temp .= "</div><hr>"; //End nav bar.

        return $temp;
    }
}