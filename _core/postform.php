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
    function format($resno, $admin) {
        //echo debug_backtrace()[1]['function'];

        $resno = (is_numeric($resno)) ? $resno : null;
        $admin = (!!$admin) ? !!$admin : false; //Should probably move validation to something more secure.

        $maxbyte = MAX_KB * 1024;
        $temp = "";

        if ($resno) $temp .= "<div class='theader'>" . S_POSTING . "</div>\n";

        if ($admin) {
            $hidden = "<input type='hidden' name='admin' value='" . PANEL_PASS . "'>";
            $temp .= "<em>" . S_NOTAGS . "</em>";
        }

        $temp .= "<div align='center'><div class='postarea'>";

        $temp .= "<form id='contribform' action='" . PHP_SELF_ABS . "' method='post' name='contrib' enctype='multipart/form-data'>";

        $temp .= "<input type='hidden' name='mode' value='regist' />" . $hidden . "<input type='hidden' name='MAX_FILE_SIZE' value='" . $maxbyte . "'>";

        if ($resno)
            $temp .= "<input type='hidden' name='resto' value='" . $resno . "'>";

        $temp .= "<table>";

        if (!FORCED_ANON) //Name
            $temp .= "<tr><td class='postblock' align='left'>" . S_NAME . "</td><td align='left'><input type='text' name='name' size='28'></td></tr>";

        $temp .= "<tr><td class='postblock' align='left'>" . S_EMAIL . "</td><td align='left'><input type='text' name='email' size='28'>";

        if (!$resno) //Subject if a new thread.
             $temp .= "</td></tr><tr><td class='postblock' align='left'>" . S_SUBJECT . "</td><td align='left'><input type='text' name='sub' size='35'>";

        $temp .= "<input type='submit' value='" . S_SUBMIT . "'></td></tr>";

        $temp .= "<tr><td class='postblock' align='left'>" . S_COMMENT . "</td><td align='left'><textarea name='com' cols='48' rows='4'></textarea></td></tr>";

        if (BOTCHECK && !$admin) //Captcha
            $temp .= "<tr><td class='postblock' align='left'><img src='" . CORE_DIR . "/general/captcha.php' /></td><td align='left'><input type='text' name='num' size='28'></td></tr>";

        //File selection
        $temp .= "<tr><td class='postblock' align='left'>" . S_UPLOADFILE . "</td><td><input type='file' name='upfile' accept='image/*|.webm' size='35'>";

        if (NOPICBOX && !SPOILERS)
            $temp .= "[<label><input type='checkbox' name='textonly' value='on'>" . S_NOFILE . "</label>]</td></tr>";

        if (SPOILERS) //Spoiler checkbox
            $temp .= "[<label><input type='checkbox' name='spoiler' value='on'>" . S_SPOILERS . "</label>]</td></tr>";
        else
            $temp .= "</td></tr>";

        if ($admin) { //Admin-specific posting options
            $temp .= "<tr><td align='left' class='postblock' align='left'>
                Options</td><td align='left'>
                Sticky: <input type='checkbox' name='isSticky' value='isSticky'>
                Lock:<input type='checkbox' name='isLocked' value='isLocked'>
                Capcode:<input type='checkbox' name='showCap' value='showCap'>
                <tr><td class='postblock' align='left'>" . S_RESNUM . "</td><td align='left'><input type='text' name='resto' size='28'></td></tr>";
        }

        //Deletion password entry
        $temp .= "<tr><td align='left' class='postblock' align='left'>" . S_DELPASS . "</td><td align='left'><input type='password' name='pwd' size='8' maxlength='8' value='' />" . S_DELEXPL . "</td></tr>";

        if (!$admin) //Show rules for non-admin
            $temp .= "<tr><td colspan='2'><div align='left' class='rules'>" . S_RULES . "</div></td></tr></table></form></div></div><hr>";
        else
            $temp .= '</table></form></div></div>';

        if (!$resno && !$admin)
            $news = file_get_contents(GLOBAL_NEWS);
        if ($news !== "") //Could this be invalidated if file_get_contents doesn't return anything? if ($news)?
            $temp .= "<div class='globalnews'>" . file_get_contents( GLOBAL_NEWS ) . "</div><hr>";


        if ($resno) //Navigation bar above thread.
            $temp .= "<div class='threadnav' /> [<a href='" . PHP_SELF2_ABS . "'>" . S_RETURN . "</a>] [<a href='" . $resno . PHP_EXT . "#bottom'/>Bottom</a>] </div>\n<hr>";
        if (USE_ADS2) $temp .= ADS2 . "<hr>";

        return $temp;
    }
}

?>
