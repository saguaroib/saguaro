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

        $temp .= '<tfoot><tr><td colspan="2"><div id="postFormError"></div></td></tr></tfoot></table></form><hr>';

        if (ENABLE_ADS) $temp .= ADS_BELOWFORM . "<hr>";
            
        if (file_exists(GLOBAL_NEWS)) {
            $news = file_get_contents(GLOBAL_NEWS);

            if ($news !== "")
                $temp .= "<div class='globalNews desktop'>" . file_get_contents( GLOBAL_NEWS ) . "</div><hr>";
        }
        
        if ($resno) //Navigation bar above thread.
            $temp .= "<div class='navLinks desktop' /> [<a href='" . PHP_SELF2_ABS . "'>" . S_RETURN . "</a>] [<a href='" . $resno . PHP_EXT . "#bottom'/>Bottom</a>] </div>\n<hr>";
        else
            $temp .= "<div id='ctrl-top' class='desktop' /> [<a href='" . PHP_SELF2_ABS . "#bottom'/>Bottom</a>]  [<a href='/" . BOARD_DIR . "/" . PHP_SELF . "?mode=catalog'>Catalog</a>]</div><hr>";
		
        return $temp;
    }
}