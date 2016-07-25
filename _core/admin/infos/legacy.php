<?php
/*

    Legacy/original "More Info" panel.

    Should be wrapped in a function or class, but meh.

*/

global $mysql;

$no = $mysql->escape_string($no);
$query = "SELECT * FROM " . SQLLOG . " WHERE no='" . $no . "'";
$row = $mysql->fetch_assoc($query);

if (!$row) echo S_SQLFAIL;

//Cleaner looking to do it this way lol
//$ext, $w, $h, $tn_w, $tn_h, $tim, $md5, $fsize, $fname
//list($no, $now, $name, $email, $sub, $com, $host, $pwd, $time, $sticky, $permasage, $locked, $root, $resto, $board, ) = $row;


$temp = "<table border='0' cellpadding='0' cellspacing='0'  />";
$temp .= "<tr>[<a href='" . PHP_ASELF . "' />Return</a>]</tr><br><hr><br>";
if ($row['sticky'] || $row['locked'] || $row['permasage']) {
    if ($row['sticky'])
        $special .= "<b><font color='FF101A'> [Stickied]</font></b>";
    if ($row['locked'])
        $special .= "<b><font color='770099'>[Locked]</font></b>";
    if ($row['permasage'])
        $special .= "<b><font color='2E2EFE'>[Permasaged]</font></b>";
    $temp .= "<tr><td class='postblock'>Special:</td><td class='row2'>This thread is $special</td></tr>"; //lmoa
}
$hashedip = substr(md5($row['host']), 12,20);
$temp .= "<tr><td class='postblock'>Name:</td><td class='row1'>" . $row['name'] . "</td></tr>
<tr><td class='postblock'>Time:</td><td class='row2' />" . $row['now'] ."</td></tr>
<tr><td class='postblock'>IP:</td><td class='row1' /><b>$hashedip</b></td></tr><br>
<tr><td class='postblock'>Comment:</td><td class='row2' />" . $row['com'] ."</td></tr>
<tr><td class='postblock'>MD5:</td><td class='row1' />$md5</td></tr>
<tr><td class='postblock'>File</td>";
if ($w && $h) {
    $temp .= "<td><img width='" . MAX_W . "' height='" . MAX_H . "' src='" . DATA_SERVER . BOARD_DIR . "/" . IMG_DIR . $tim . $ext . "'/></td></tr>
    <tr><td class='postblock'>Thumbnail:</td><td><img width='" . $tn_w . "' height='" . $tn_h . "' src='" . DATA_SERVER . BOARD_DIR . "/" . THUMB_DIR . $tim . "s.jpg" . "'/></td></tr>
    <tr><td class='postblock'>Links:</td><td>[<a href='" . DATA_SERVER . BOARD_DIR . "/" . IMG_DIR . $tim . $ext . "' target='_blank' />Image src</a>][<a href='" . DATA_SERVER . BOARD_DIR . "/" . THUMB_DIR . $tim . "s.jpg' target='_blank' />Thumb src</a>]
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

$alart = $mysql->result("SELECT COUNT(*) FROM " . SQLBANLOG . " WHERE host='" . $host . "'", 0, 0);
$alert = ($alart) ? "<b><font color=\"FF101A\"> $alart ban(s) on record for $hashedip!</font></b>" : "No bans on record for IP $hashedip";

$temp .= "<br><table border='0' cellpadding='0' cellspacing='0' /><form action='admin.php?mode=ban' method='POST' />
<input type='hidden' name='no' value='$no' />
<input type='hidden' name='ip' value='$hashedip' />
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
    $temp .= "<tr><td class='postblock'>Add to Blacklist:</td><td>[ Comment<input type='checkbox' name='blacklistcom' /> ] [ Image MD5<input type='checkbox' name='blacklistimage' /> ] </td></tr>";

$temp .= "<center><tr><td><input type='submit' value='Ban'/></td></tr></center></table></form><br><hr>";
$temp .= "<tr>[<a href='" . PHP_ASELF . "' />Return</a>]</tr><br>";

?>