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
    function format($resno = null, $admin = false, $catalog = false) {
        global $my_log;  
        //echo debug_backtrace()[1]['function'];
        $my_log->update_cache();
        $resno = (is_numeric($resno)) ? $resno : null;
        $admin = (!!$admin) ? !!$admin : false; //Should probably move validation to something more secure.

        $maxbyte = MAX_KB * 1024;
        $temp = "";


        $temp .= "<div class='postForm' align='center'>";
        if ($resno) $temp .= "<div class='theader'>" . S_POSTING . "</div>";
        $toggleTxt = ($resno) ? "New reply" : "New thread";
        //$temp .= "<div class='formToggle noDesktop'><h3>[<a onclick='toggle_visibility(\"postForm\");'>$toggleTxt</a>]</h3></div>";
        $temp .="<div class='postarea noMobile' id='postForm'>";
        
        if ($my_log->cache[$resno]['locked'] > 0) {
            $tmppart .= ($my_log->cache[$resno]['locked'] == 2) ?  S_THREADARCHIVED : S_THREADLOCKED;
            $temp .= "<h1>" . $tmppart  . "</h1><br><hr></div></div>";
            $temp .= $this->afterForm($resno);
            return $temp;
        }

        $temp .= "<form action='" . PHP_SELF_ABS . "' method='post' name='post' enctype='multipart/form-data'>";
        $temp .= "<input type='hidden' name='mode' value='regist'><input type='hidden' name='MAX_FILE_SIZE' value='" . $maxbyte . "'>";
        $temp .= "<input type='hidden' name='board' value='" . BOARD_DIR . "'>";

        if ($resno) 
            $temp .= "<input type='hidden' name='resto' value='" . $resno . "'>";

        $temp .= "<table id='contribform'>";

        if (!FORCED_ANON) //Name
            $temp .= "<tr><td class='postblock' align='left'>" . S_NAME . "</td><td align='left'><input type='text' name='name' size='28'></td></tr>";

        $temp .= "<tr><td class='postblock' align='left'>" . S_EMAIL . "</td><td align='left'><input type='text' name='email' size='28'>";

        if (ALLOW_SUBJECT_REPLY || !$resno) //Subject if a new thread.
             $temp .= "</td></tr><tr><td class='postblock' align='left'>" . S_SUBJECT . "</td><td align='left'><input type='text' name='sub' size='35' autocomplete='off'>";

        $temp .= "<input type='submit' value='" . S_SUBMIT . "'></td></tr>";

        $temp .= "<tr id='comrow'><td class='postblock' align='left'>" . S_COMMENT . "</td><td align='left'><textarea id='comtxt' name='com' cols='34' rows='4'></textarea></td></tr>";

        if (BOTCHECK) { //Captcha
                $temp .= "<tr id='captchaRow'><td class='postblock' id='captcha' align='left'><img src='" . CORE_DIR_PUBLIC . "/general/captcha.php' /></td><td align='left'><input type='text' name='num' size='28' autocomplete='off'></td></tr>";
        }

        //File selection
        $temp .= "<tr><td class='postblock' align='left'>" . S_UPLOADFILE . "</td><td><input type='file' name='upfile' accept='image/*|.webm' size='35'>";

        if (NOPICBOX && !SPOILERS)
            $temp .= "[<label><input type='checkbox' name='textonly' value='on'>" . S_POSTNOFILE . "</label>]";

        if (SPOILERS && !NOPICBOX) //Spoiler checkbox
            $temp .= "[<label><input type='checkbox' name='spoiler' value='spoiler'>" . S_SPOILERS . "</label>]";

        if (FILE_BOARD && !$resno)
            $temp .= "<tr><td class='postblock'>" . S_TAG . "</td><td><select name='tagSelect'>
                <option value='0'>Choose tag:</option>
                <option value='1'>Hentai</option>
                <option value='2'>Porn</option>
                <option value='3'>Japanese</option>
                <option value='4'>Anime</option>
                <option value='5'>Game</option>
                <option value='6'>Loop</option>
                <option value='7'>Music</option>
                <option value='8'>Other</option></select></td></tr>";

        //if (ALLOW_EMBEDS) $temp .=  "<tr><td class='postblock' align='left'>" . S_EMBED . "</td><td><input type='text' name='embed' maxlength='60'></td></tr>";
        
        $ips = ($resno) ? "" : "<li><b>" . $my_log->cache['ipcount'] ."</b> unique posters on this board.</li>";
        
        //Deletion password entry
        //$temp .= "<tr><td align='left' class='postblock' id='delField' align='left'>" . S_DELPASS . "</td><td align='left'><input type='password' name='pwd' size='8' maxlength='8' value='' />" . S_DELEXPL . "</td></tr>";
        //$temp .= "<tr ><td colspan='1'></td><td align='left'>[<a class='ruleToggle' onclick='toggle_visibility(\"pfExtra\");'>Show posting guidelines</a>]</td></tr>";
        
        if (!$catalog) $temp .= "<tr><td colspan='2'><div align='left' id='pfExtra' class='rules' style='/*display:none;*/'><ul>" . S_RULES . $ips ."</ul></div></td></tr>";
        
        $temp .= "</div></table>";

        
        $temp .= "</form></div></div>";
        
        if (ENABLE_ADS) $temp .= ADS_BELOWFORM . "";
            
        if (file_exists(GLOBAL_NEWS)) {
            $news = file_get_contents(GLOBAL_NEWS);

            if ($news !== "")
                $temp .= "<div class='globalNews desktop'>$news</div><hr>";
        }
        
        $temp .= $this->afterForm($resno, $catalog);
        
        return $temp;
    }

    function afterForm($resno, $mode) {
        
        //Navigation bar above thread, below postform.
        $temp .= ($resno) ? "<div class='navLinks' />" : "<div id='ctrl-top' class='desktop'>";
        if ($resno || $mode) $temp .= "[<a class='navButton' href='//" . SITE_ROOT_BD . "'>" . S_RETURN . "</a>] ";
        
        $temp .= "[<a class='navButton' href='#bottom'/>" . S_BOTTOM . "</a>] ";
        if (!$mode) $temp .= "[<a class='navButton' href='/" . BOARD_DIR . "/catalog'>" . S_CATALOG . "</a>] ";
        if (ENABLE_ARCHIVE) $temp .= "[<a class='navButton' href='//" . SITE_ROOT_BD . "/imgboard.php?mode=arc'/>" . S_ARCHIVE . "</a>] ";
        
        //if (SHOW_ADMIN_LOGS) $temp .= "[<a class='navButton' href='//" . SITE_ROOT . "/logs.php?board=" . BOARD_DIR . "' target='_blank'>" . S_LOGS . "</a>]";
        $temp .= "</div><hr>"; //End nav bar.
        
        return $temp;
    }
    
}

?>
