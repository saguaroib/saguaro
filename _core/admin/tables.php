<?php

class Table {
    
    function display($type = 0, $resource = 0) {
        global $mysql;
            function calculate_age($timestamp, $comparison = '') {
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

            if ($resource)
                $mysql->escape_string($resource);
                
            if ($type === 'res') {
                $banner = "<div class='managerBanner'>" . S_DELRES . $resource . "</div>";
                $query = $mysql->query("SELECT * FROM " . SQLLOG . " WHERE resto='$resource' OR no='$resource' OR host='$resource' ORDER BY time ASC");
                $mode = 'res';
            }
            
            if ($type === 'all') {
                $banner = "<div class='managerBanner'>" . S_DELALL . "</div>";
                $query = $mysql->query("SELECT * FROM " . SQLLOG . " ORDER BY no DESC");
                $mode = 'all';
            }
            
            if ($type === 'ip') {
                $banner = "<div class='managerBanner'>" . S_DELIP . $resource . "</div>";
                $hostno = $mysql->result("SELECT host FROM " . SQLLOG . " WHERE no='$resource' ");
                $query = $mysql->query("SELECT * FROM " . SQLLOG . " WHERE host='$hostno' ORDER BY NO DESC");
                $mode = 'ip';
            }
            
            if ($type === 'ops') {
                $banner = "<div class='managerBanner'>" . S_DELOPS . "</div>";
                $query = $mysql->query("SELECT * FROM " . SQLLOG . " WHERE resto='0' ORDER BY time DESC");
                $mode = 'ops';
            }            

            // Deletion screen display
            $temp .= "<div class='managerBanner'>" . S_MANAMODE . "</div>" . $banner;
            $temp .= '<br><form action="' . PHP_ASELF . '" method="get" id="delForm"><input type="hidden" name="mode" value="res">
            <input type="text" name="no" placeholder="Post # or IP" required><input type="submit" value="Search">
            <input type="button" text-align="center" onclick="location.href=\'' . PHP_ASELF_ABS . '?mode=ops\';" value="Only opening posts">
            <input type="button" text-align="center" onclick="location.href=\'' . PHP_ASELF_ABS . '?mode=all\';" value="View all"></form>';
            $temp .= "<form action='" . PHP_ASELF . "?mode=del' method='post' id='delForm'><input type=hidden name=admin value=del checked>";
            /*$temp .=  "<input type=hidden name=mode value=admin>";
            $temp .=  "<input type=hidden name=admin value=del>";
            $temp .=  "<input type=hidden name=pass value='$pass'>";
            $temp .=  "<div class='delbuttons'><input type=submit value='" . S_ITDELETES . "'>";
            $temp .=  "<input type=reset value='" . S_MDRESET . "'>";
            $temp .=  "[<input type=checkbox name=onlyimgdel value=on>" . S_MDONLYPIC . "]</div><br>";*/
            $temp .= "<br>";
            $temp .=  "<table cellpadding='0' cellspacing='0' class='postlists' style='border-collapse:collapse;' cellspacing='0' cellpadding='0'>";
            $temp .=  "<tr class='postTable head'>" . S_MDTABLE1;
            $temp .=  S_MDTABLE2;
            $temp .=  "</tr>";

            
            if (!$query) 
                error(S_SQLFAIL);
                
            $j = 0;
            while ($row = $mysql->fetch_row($query)) {
                $j++;
                $path = realpath("./") . '/' . IMG_DIR;
                $img_flag = FALSE;
                list($no, $now, $name, $email, $sub, $com, $host, $pwd, $ext, $w, $h, $tn_w, $tn_h, $tim, $time, $md5, $fsize, $fname, $sticky, $permasage, $locked, $root, $resto) = $row;
                // Format
                $now = ereg_replace('.{2}/(.*)$', '\1', $now);
                $now = ereg_replace('\(.*\)', ' ', $now);
                $name = (strlen($name) > 10) ? substr($name, 0, 9) . "..." : $name;
                $name = ($email) ? "<a href=\"mailto:$email\">$name</a>" : $name;
                $sub = (strlen($sub) > 10) ? substr($sub, 0, 9) . "..." : $sub;
                $com = str_replace("<br />", " ", $com);
                $com = htmlspecialchars($com);
                $trunccom = substr($com, 0, 18) . "...";
                $fname =  (strlen($fname) > 10) ? substr($fname, 0, 40) : $fname;
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
                    $clip = S_NOFILE;
                    $size = 0;
                    $md5  = "";
                }
                
                if (!valid('moderator'))
                    $host ='###.###.###.###';
                
                $class = ($j % 2) ? "row1" : "row2"; //BG color
                $altClass = ($j % 2) ? "row2" : "row1"; //lol
                $resdo = ($resto) ? 'Reply to thread' : 'Opening post';
                $ssno = ($resto) ? $resto : $no;
                $linknum = /*($resto) ?*/ '<a href="' . PHP_SELF_ABS . "?res=" . $no . '" target="_blank" />' . $no . '</a>';// : '<b><a href="' . PHP_SELF_ABS . "?res=" . $no . '" target="_blank" />' . $no . '</a></b>';
                $sno = ($sticky) ? "<b><font color=\"FF101A\">$linknum</font></b>" : $linknum;
                $threadmode = ($resto) ? $resto : $no;    
                $delim = ($size) ? "<td colspan='2'>&nbsp;</td><td colspan='1'>[<b><a href='?mode=adel&no=$no&imgonly=1&refer=$mode'>Delete image?</a>]</b></td><td colspan='3'>&nbsp;</td>" : "<td colspan='6'>&nbsp;</td>";
                
                //Actual panel html
                //$temp .=  "<tr class='$class'><td><input type=checkbox name=\"$no\" value=delete></td>"; //<input value='x' alt='Delete post' onclick=\"location.href='?mode=adel&no=$no';\" type='button'>
                $temp .=  "<tr class='$class'><td><input value='x' alt='Delete post' onclick=\"location.href='?mode=adel&no=$no&refer=$mode';\" type='button'></td>";          
                $temp .=  "<td colspan='1'>$sno</td><td>$now</td><td>$sub</td>";
                $temp .=  "<td>$name</b></td><td><span title='Double-click to preview full comment' ondblclick='swap(\"trunc$no\", \"full$no\")' id='trunc$no'>$trunccom</span><span ondblclick='swap(\"full$no\", \"trunc$no\")' id='full$no' style='display:none;'>$com</span></td>";
                $temp .=  "<td class='postimg' >$clip</td><td>$host</td><td>" . calculate_age($time) . "</td><td><input type='button' value='More' onclick='more(\"" . $no . "a\",\"" . $no . "b\");'></td>";
                $temp .=  "</tr><tr id='" . $no . "a' class='$class' style='display:none;'><td colspan='2'>&nbsp;</td><td colspan='2' align='left'><b>$resdo</b></td>$delim";
                $temp .=  "</tr><tr id='" . $no . "b' class='$class' style='display:none;'><td colspan='2'>&nbsp;</td>
                <td colspan='2'><a href='" . PHP_SELF_ABS . "?res=$ssno'>$ssno</a><td colspan='2'>&nbsp;</td></td>
                <td colspan='4' align='center'><input value='View all by this IP' onclick=\"location.href='?mode=ip&no=$no';\" type='button'>&nbsp;&nbsp;&nbsp;&nbsp;<input value='View in threadmode' onclick=\"location.href='?mode=res&no=$threadmode';\" type='button'>&nbsp;&nbsp;&nbsp;&nbsp;<input value='Delete everything by this IP' onclick=\"popup('admin=delall&no=$no');\" type='button'>&nbsp;&nbsp;&nbsp;&nbsp;<input value='Ban user' onclick=\"popup('admin=ban&no=$no');\" type='button'>&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' onclick=\"location.href='" . PHP_ASELF_ABS . "?mode=more&no=" . $no . "';\" value=\"More info\" /></td>";                
            }//
            //$mysql->free_result($result);
            $temp .=  "<link rel='stylesheet' type='text/css' href='" . CSS_PATH . "/stylesheets/img.css' />";
            $all = (int) ($all / 1024);
            //$temp .=  "<div align='center'/>[ " . S_IMGSPACEUSAGE . $all . "</b> KB ]</div>";
            $temp .= "</body></html>";

            echo $temp;
    }
    
    function moreInfo($no) {
        global $mysql;
        
        if (!$result = $mysql->query("SELECT * FROM " . SQLLOG . " WHERE no='" . $no . "'"))
            echo S_SQLFAIL;
        
        $row = $mysql->fetch_row($result);
        list($no, $now, $name, $email, $sub, $com, $host, $pwd, $ext, $w, $h, $tn_w, $tn_h, $tim, $time, $md5, $fsize, $fname, $sticky, $permasage, $locked, $root, $resto, $board, ) = $row;
        $temp = head(0);
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
        $alart = $mysql->num_rows("SELECT COUNT(*) FROM " . SQLBANLOG . " WHERE ip='" . $host . "'");
        if ( $alart > 0)
            $alert = "<b><font color=\"FF101A\"> $alart ban(s) on record for $host!</font></b>";
        else
            $alert = "No bans on record for IP $host";
        $temp .= "<br><table border='0' cellpadding='0' cellspacing='0' /><form action='admin.php?mode=ban' method='POST' />
        <input type='hidden' name='no' value='$no' />
        <input type='hidden' name='ip' value='$host' />
        <center><th class='postblock'><b>Ban panel</b></th></center>
        <tr><td class='postblock'>IP History: </td><td>$alert</td></tr>
        <tr><td class='postblock'>Unban in:</td><td><input type='number' min='0' size='4' name='banlength'  /> days</td></tr>
        <center><tr><td class='postblock'>Ban type:</td><td></center>
            <select name='banType' />
            <option value='warn' />Warning only</option>
            <option value='thisboard' />This board - /" . BOARD_DIR . "/ </option>
            <option value='global' />All boards</option>
            <option value='perma' />Permanent - All boards</option>
            </select>
        </td></tr>
        <tr><td class='postblock'>Public reason:</td><td><textarea rows='2' cols='25' name='pubreason' /></textarea></td></tr>
        <tr><td class='postblock'>Staff notes:</td><td><input type='text' name='staffnote' /></td></tr>
        <tr><td class='postblock'>Append user's comment:</td><td><input type='text' name='custmess' placeholder='Leave blank for USER WAS BAN etc.' /> [ Show message<input type='checkbox' name='showbanmess' /> ] </td></tr>
        <tr><td class='postblock'>After-ban options:</td><td>
            <select name='afterban' />
            <option value='none' />None</option>
            <option value='delpost' />Delete this post</option>
            <option value='delallbyip' />Delete all by this IP</option>
            <option value='delimgonly' />Delete image only</option>
            </select>
        </td></tr>";
        if (valid('admin'))
            $temp .= "
            <tr><td class='postblock'>Add to Blacklist:</td><td>[ Comment<input type='checkbox' name='blacklistcom' /> ] [ Image MD5<input type='checkbox' name='blacklistimage' /> ] </td></tr>";
        $temp .= "<center><tr><td><input type='submit' value='Ban'/></td></tr></center></table></form><br><hr>";
        $temp .= "<tr>[<a href='" . PHP_ASELF . "' />Return</a>]</tr><br>";
        
        echo $temp;
    }
    
}

?>
