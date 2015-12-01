<?php

class DelTable {
    
    function displayTable($onlyimgdel) {
        global $mysql;
        $delno   = array(
                 dummy
           );
            $delflag = FALSE;
            reset($_POST);
            while ($item = each($_POST)) {
                if ($item[1] == 'delete') {
                    array_push($delno, $item[0]);
                    $delflag = TRUE;
                }
            }
            if ($delflag) {
                if (!$result = $mysql->query("select * from " . SQLLOG . "")) {
                    echo S_SQLFAIL;
                }
                $find = FALSE;

                while ($row = $mysql->fetch_row($result)) {
                list($no, $now, $name, $email, $sub, $com, $host, $pwd, $ext, $w, $h, $tn_w, $tn_h, $tim, $time, $md5, $fsize, $fname, $sticky, $permasage, $locked, $root, $resto) = $row;
                    if ($onlyimgdel == 'on') {
                        delete_post($no, $pwd, 1, 1, 1, 0);
                    } else {
                        if (array_search($no, $delno)) { //It is empty when deleting
                            delete_post($no, $pwd, 0, 1, 1, 0);
                        }
                    }
                }
            }

            function calculate_age($timestamp, $comparison = '')
            {
                $units = array(
                     'second' => 60,
                    'minute' => 60,
                    'hour' => 24,
                    'day' => 7,
                    'week' => 4.25,
                    'month' => 12
               );

                if (empty($comparison)) {
                    $comparison = $_SERVER['REQUEST_TIME'];
                }
                $age_current_unit = abs($comparison - $timestamp);
                foreach ($units as $unit => $max_current_unit) {
                    $age_next_unit = $age_current_unit / $max_current_unit;
                    if ($age_next_unit < 1) {
                        // are there enough of the current unit to make one of the next unit?
                        $age_current_unit = floor($age_current_unit);
                        $formatted_age    = $age_current_unit . ' ' . $unit;
                        return $formatted_age . ($age_current_unit == 1 ? '' : 's');
                    }
                    $age_current_unit = $age_next_unit;
                }

                $age_current_unit = round($age_current_unit, 1);
                $formatted_age    = $age_current_unit . ' year';
                return $formatted_age . (floor($age_current_unit) == 1 ? '' : 's');

            }


            // Deletion screen display
            $temp .= "<form action='" . PHP_ASELF . "' method='post' id='delForm'>
    <input type=hidden name=admin value=del checked>";
            $temp .=  "<input type=hidden name=mode value=admin>";
            $temp .=  "<input type=hidden name=admin value=del>";
            $temp .=  "<input type=hidden name=pass value='$pass'>";
            $temp .=  "<div class='managerBanner'>" . S_DELLIST . "</div>";
            $temp .=  "<div class='delbuttons'><input type=submit value='" . S_ITDELETES . "'>";
            $temp .=  "<input type=reset value='" . S_MDRESET . "'>";
            $temp .=  "[<input type=checkbox name=onlyimgdel value=on><!--checked-->" . S_MDONLYPIC . "]</div>";
            $temp .=  "<table class='postlists' style='border-collapse:collapse;' cellspacing='0' cellpadding='0'>";
            $temp .=  "<tr class='postTable head'>" . S_MDTABLE1;
            $temp .=  S_MDTABLE2;
            $temp .=  "</tr>";

            if (!$result = $mysql->query("select * from " . SQLLOG . " order by no desc")) {
                $temp .=  S_SQLFAIL;
            }
            $j = 0;
            while ($row = $mysql->fetch_row($result)) {
                $j++;
                $path = realpath("./") . '/' . IMG_DIR;
                $img_flag = FALSE;
                list($no, $now, $name, $email, $sub, $com, $host, $pwd, $ext, $w, $h, $tn_w, $tn_h, $tim, $time, $md5, $fsize, $fname, $sticky, $permasage, $locked, $root, $resto) = $row;
                // Format
                /*$now = ereg_replace('.{2}/(.*)$', '\1', $now);
                $now = ereg_replace('\(.*\)', ' ', $now);*/
                if (strlen($name) > 10)
                    $name = substr($name, 0, 9) . "...";
                if (strlen($sub) > 10)
                    $sub = substr($sub, 0, 9) . "...";
                if ($email)
                    $name = "<a href=\"mailto:$email\">$name</a>";
                $com = str_replace("<br />", " ", $com);
                $com = htmlspecialchars($com);
                if (strlen($com) > 20)
                    $trunccom = substr($com, 0, 18) . "...";
                if (strlen($fname) > 10)
                    $fname = substr($fname, 0, 40) . "..." . $ext;
                // Link to the picture
                if ($ext && is_file($path . $tim . $ext)) {
                    $img_flag = TRUE;
                    $clip     = "<a class=\"thumbnail\" target=\"_blank\" href=\"" . IMG_DIR . $tim . $ext . "\">" . $tim . $ext . "<span><img class='postimg' src=\"" . THUMB_DIR . $tim . 's.jpg' . "\" width=\"100\" height=\"100\" /></span></a><br />";
                    if ($fsize >= 1048576) {
                        $size  = round(($fsize / 1048576), 2) . " M";
                        $fsize = $asize;
                    } else if ($fsize >= 1024) {
                        $size  = round($fsize / 1024) . " K";
                        $fsize = $asize;
                    } else {
                        $size  = $fsize . " ";
                        $fsize = $asize;
                    }
                    $all += $asize; //total calculation
                    $md5 = substr($md5, 0, 10);
                } else {
                    $clip = "[No file]";
                    $size = 0;
                    $md5  = "";
                }
                $class = ($j % 2) ? "row1" : "row2"; //BG color

                if ($resto == '0')
                    $resdo = '<b>OP(<a href="' . DATA_SERVER . BOARD_DIR . "/" . RES_DIR . $no . PHP_EXT . '#' . $no . '" target="_blank" />' . $no . '</a>)</b>';
                else
                    $resdo = '<a href="' . DATA_SERVER . BOARD_DIR . "/" . RES_DIR . $resto . PHP_EXT . '#' . $no . '" target="_blank" />' . $resto . '</a>';
                $warnSticky = '';
                if ($sticky == '1')
                    $warnSticky = "<b><font color=\"FF101A\">(Sticky)</font></b>";
                $temp .=  "<tr class=$class><td><input type=checkbox name=\"$no\" value=delete>$warnSticky</td>";
                $temp .=  "<td>$no</td><td>$resdo</td><td>$now</td><td>$sub</td>";
                $temp .=  "<td>$name</b></td><td><span title='Double-click to preview full comment' ondblclick='swap(\"trunc$no\", \"full$no\")' id='trunc$no'>$trunccom</span><span ondblclick='swap(\"full$no\", \"trunc$no\")' id='full$no' style='display:none;'>$com</span></td>";
                $temp .=  "<td class='postimg' >$clip</td><td>" . calculate_age($time) . "</td><td><input type=\"button\" text-align=\"center\" onclick=\"location.href='" . PHP_ASELF_ABS . "?mode=more&no=" . $no . "';\" value=\"Post Info\" /></td>\n";
                $temp .=  "</tr>";
            }
            $mysql->free_result($result);

            $temp .=  "<link rel='stylesheet' type='text/css' href='" . CSS_PATH . "/stylesheets/img.css' />";
            //foot($dat);
            $all = (int) ($all / 1024);
            $temp .=  "<div align='center'/>[ " . S_IMGSPACEUSAGE . $all . "</b> KB ]</div>";
            $temp .= "</body></html>";

            echo $temp;
    }
    
    function moreInfo($no) {
        global $mysql;
        
        if (!$result = $mysql->query("SELECT * FROM " . SQLLOG . " WHERE no='" . $no . "'"))
            echo S_SQLFAIL;
        
        $row = $mysql->fetch_row($result);
        list($no, $now, $name, $email, $sub, $com, $host, $pwd, $ext, $w, $h, $tn_w, $tn_h, $tim, $time, $md5, $fsize, $fname, $sticky, $permasage, $locked, $root, $resto, $board, ) = $row;
        $temp = head();
        $temp .= "<table border='0' cellpadding='0' cellspacing='0'  />";
        $temp .= "<tr>[<a href='" . PHP_ASELF . "' />Return</a>]</tr><br><hr><br>";
        if ($sticky || $locked || $permasage) {
            if ($sticky)
                $special .= "<b><font color=\"FF101A\"> [Stickied]</font></b>";
            if ($locked)
                $special .= "<b><font color=\"770099\">[Locked]</font></b>";
            if ($permasage)
                $special .= "<b><font color=\"2E2EFE\">[Permasaged]</font></b>";
            $temp .= "<tr><td class='postblock'>Special:</td><td class='row2'>This thread is $special</td></tr>"; //lmoa
        }
        if (!valid('moderator')) //Hide IPs from janitors
            $host = '###.###.###.###';       
        if ($host == '')
            $host = "No IP in dataabase";
        $temp .= "<tr><td class='postblock'>Name:</td><td class='row1'>$name</td></tr>
      <tr><td class='postblock'>tempe:</td><td class='row2' />$now</td></tr>
      <tr><td class='postblock'>IP:</td><td class='row1' /><b>$host</b></td></tr><br>
      <tr><td class='postblock'>Comment:</td><td class='row2' />$com</td></tr>
      <tr><td class='postblock'>MD5:</td><td class='row1' />$md5</td></tr>
      <tr><td class='postblock'>File</td>";
        if ($w && $h) {
            $hasimg = 1;
            $temp .= "<td><img width='" . MAX_W . "' height='" . MAX_H . "' src='" . DATA_SERVER . BOARD_DIR . "/" . IMG_DIR . $tim . $ext . "'/></td></tr>
            <tr><td class='postblock'>Thumbnail:</td><td><img width='" . $tn_w . "' height='" . $tn_h . "' src='" . DATA_SERVER . BOARD_DIR . "/" . THUMB_DIR . $tim . "s.jpg" . "'/></td></tr>
            <tr><td class='postblock'>Links:</td><td>[<a href='" . DATA_SERVER . BOARD_DIR . "/" . IMG_DIR . $tim . $ext . "' target='_blank' />Image src</a>][<a href='" . tempA_SERVER . BOARD_DIR . "/" . THUMB_DIR . $tim . "s.jpg' target='_blank' />Thumb src</a>]
            [<a href='" . DATA_SERVER . BOARD_DIR . "/" . RES_DIR . $no . PHP_EXT . "#" . $no . "' target='_blank' /><b>View in thread</b></a>]</td></tr>";
        } else
            $temp .= "<td>No file</td></tr>";
        if (!$resto) {
            $temp .= "<form action='admin.php' />
            <tr><td class='postblock'>Action</td><td><input type='hidden' name='mode' value='modipost' /><select name='action' />
            <option value='sticky' />Sticky</option>
            <option value='eventsticky' />Event sticky</option>
            <option value='unsticky' />Unsticky</option>
            <option value='lock' />Lock</option>
            <option value='unlock' />Unlock</option>
            <option value='permasage' />Autosage</option>
            <option value='nopermasage' />De-autosage</option>
            </select></td><td><input type='hidden' name='no' value='$no' /><input type='submit' value='Submit'></td></tr></table></form>";
        } else
            $temp .= "</table></form>";
        $temp .= "<tr>[<a href='" . PHP_ASELF . "' />Return</a>]</tr><br>";
        
        echo $temp;
    }
    
}

?>
