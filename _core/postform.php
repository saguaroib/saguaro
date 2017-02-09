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
    function format($resno = null, $admin = false) {
        //echo debug_backtrace()[1]['function'];

        $resno = (is_numeric($resno)) ? $resno : null;
        $admin = (!!$admin) ? !!$admin : false; //Should probably move validation to something more secure.

        $maxbyte = MAX_KB * 1024;
        $temp = "";

        if ($resno) $temp .= "<div class='theader'>" . S_POSTING . "</div>\n";

        $temp .= "<div class='postForm' align='center'><div class='postarea'>";
        $temp .= "<form id='contribform' action='" . PHP_SELF_ABS . "' method='post' name='contrib' enctype='multipart/form-data'>";

        if ($admin) {
            $name = "";

            if (valid('moderator')) 
                $name = '<span style="color:#770099;font-weight:bold;">Anonymous ## Mod</span>';
            if (valid('manager'))
                $name = '<span style="color:#2E2EFE;font-weight:bold;">Anonymous ## Manager</span>';
            if (valid('admin'))
                $name = '<span style="color:#FF101A;font-weight:bold;">Anonymous ## Admin</span>';

            $temp .= "<em>" . S_NOTAGS . " Posting as</em>: " . $name;
            $temp .= "<input type='hidden' name='admin' value='" . PANEL_PASS . "'>";
        }

        $temp .= "<input type='hidden' name='mode' value='regist'><input type='hidden' name='MAX_FILE_SIZE' value='" . $maxbyte . "'>";

        if ($resno)
            $temp .= "<input type='hidden' name='resto' value='" . $resno . "'>";

        $temp .= "<table>";

        if (!FORCED_ANON) //Name
            $temp .= "<tr><td class='postblock' align='left'>" . S_NAME . "</td><td align='left'><input type='text' name='name' size='28'></td></tr>";

        $temp .= "<tr><td class='postblock' align='left'>" . S_EMAIL . "</td><td align='left'><input type='text' name='email' size='28'>";

        if (!$resno) //Subject if a new thread.
             $temp .= "</td></tr><tr><td class='postblock' align='left'>" . S_SUBJECT . "</td><td align='left'><input type='text' name='sub' size='35'>";

        $temp .= "<input type='submit' value='" . S_SUBMIT . "'></td></tr>";

        $temp .= "<tr id='comrow'><td class='postblock' align='left'>" . S_COMMENT . "</td><td align='left'><textarea id='comtxt' name='com' cols='34' rows='4'></textarea></td></tr>";

        if (BOTCHECK && !$admin) { //Captcha
            if (RECAPTCHA) {
                $temp .= "<tr id='captchaRow'><td class='postblock' id='captcha' align='left'>Verification</td><td><script src='//www.google.com/recaptcha/api.js'></script><div class='g-recaptcha' data-sitekey='" . RECAPTCHA_SITEKEY ."'></div></tr>";
            } else {
                $temp .= "<tr id='captchaRow'><td class='postblock' id='captcha' align='left'><img src='" . CORE_DIR_PUBLIC . "/general/captcha.php' /></td><td align='left'><input type='text' name='num' size='28'></td></tr>";
            }
        }

        //File selection
        $temp .= "<tr><td class='postblock' align='left'>" . S_UPLOADFILE . "</td><td><input type='file' name='upfile[]' accept='image/*|.webm' size='35' multiple='multiple'>";

        if (NOPICBOX && !SPOILER)
            $temp .= "[<label><input type='checkbox' name='textonly' value='on'>" . S_NOFILE . "</label>]</td></tr>";

        /*if (SPOILER && !NOPICBOX) //Spoiler checkbox
            $temp .= "[<label><input type='checkbox' name='spoiler' value='spoiler'>" . S_SPOILERS . "</label>]</td></tr>";
        else*/
            $temp .= "</td></tr>";

        if ($admin) { //Admin-specific posting options
            $temp .= "<tr><td align='left' class='postblock' align='left'>
                Options</td><td align='left'>
                Sticky: <input type='checkbox' name='isSticky' value='isSticky'>
                Event sticky: <input type='checkbox' name='eventSticky' value='eventSticky'>
                Lock:<input type='checkbox' name='isLocked' value='isLocked'>
                Capcode:<input type='checkbox' name='showCap' value='showCap'>
                <tr><td class='postblock' align='left'>" . S_RESNUM . "</td><td align='left'><input type='text' name='resto' size='28'></td></tr>";
        }

        //Deletion password entry
        $temp .= "<tr><td align='left' class='postblock' id='delField' align='left'>" . S_DELPASS . "</td><td align='left'><input type='password' name='pwd' size='8' maxlength='8' value='' />" . S_DELEXPL . "</td></tr>";

        if (!$admin) //Show rules for non-admin
            $temp .= "<tr><td colspan='2'><div align='left' class='rules'>" . S_RULES . "</div></td></tr></table></form></div></div><hr>";
        else
            $temp .= '</table></form></div></div>';

        if (ENABLE_ADS) $temp .= ADS_BELOWFORM . "<hr>";
            
        if (file_exists(GLOBAL_NEWS)) {
            $news = file_get_contents(GLOBAL_NEWS);

            if ($news !== "")
                $temp .= "<div class='globalNews desktop'>" . file_get_contents( GLOBAL_NEWS ) . "</div><hr>";
        }
        
        if ($resno) //Navigation bar above thread.
            $temp .= "<div class='navlinks desktop' /> [<a href='" . PHP_SELF2_ABS . "'>" . S_RETURN . "</a>] [<a href='" . $resno . PHP_EXT . "#bottom'/>Bottom</a>] </div>\n<hr>";
        else
            $temp .= "<div id='ctrl-top' class='desktop' /> [<a href='" . PHP_SELF2_ABS . "#bottom'/>Bottom</a>]  [<a href='/" . BOARD_DIR . "/" . PHP_SELF . "?mode=catalog'>Catalog</a>]</div><hr>";
        
		if ($admin) $temp = "<div id='adminForm' style='display:none; align:center;' />" . $temp . "</div>";
		
        return $temp;
    }

}

?>
