<?php
include("config.php");

require_once(CORE_DIR . "/mysql/mysql.php");
$mysql = new SaguaroMySQL;
$mysql->init();

$host = $_SERVER['REMOTE_ADDR'];

require_once(CORE_DIR . "/admin/bans.php");

$dis  = new Banish;
$deny = ($dis->checkBan($host)) ? 0 : 1; //no ban : is banned

$status = "are not banned";
if ($deny) {
    
    $row    = $mysql->fetch_assoc("SELECT * FROM " . SQLBANLOG . " WHERE ip='" . $host . "' AND active <> 0 LIMIT 1");
    $length = ((($row['expires'] - $row['placedon']) / 60) / 60) / 24; //MATH SON
    
    switch ($row['type']) {
        case '1':
            $status = 'have been warned on: <b>/' . $row['board'] . '/ - ' . TITLE . '</b>';
            $mysql->query("UPDATE " . SQLBANLOG . " SET active='0' WHERE ip='$host' AND active='1' LIMIT 1");
            break;
        case '2':
            $status = 'have been banned from: <b>/' . $row['board'] . '/ - ' . TITLE . '</b>';
            if (time() > $row['expires'])
                $mysql->query("UPDATE " . SQLBANLOG . " SET active='0' WHERE ip='$host' AND active='1' LIMIT 1");
            $row['expires'] = date('F d, Y H:i', $row['expires']) . " days";
            break;
        case '3':
            $status = 'have been banned from <b>all boards</b>';
            if (time() > $row['expires'])
                $mysql->query("UPDATE " . SQLBANLOG . " SET active='0' WHERE ip='$host' AND active='1' LIMIT 1");
            $row['expires'] = date('F d, Y H:i', $row['expires']) . " days";
            break;
        case '4':
            $status = 'have been <b>permanently banned from all boards<b>';
            $length = '<b>forever</b>';
            break;
        default:
            $status      = 'are not banned';
            $row['type'] = 0;
            break;
    }
    $row['placedon'] = date('F d, Y H:i', $row['placedon']);
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>You ' . $status . '</title>
<link href="' . CSS_PATH . '/stylesheets/bancss.css" rel="stylesheet" type="text/css" /></head><body>
<div class="container">
<div class="header"></a><h1>You <b>' . $status . '.</b></h1></div>';

$footer = '<div class="footer"><h2><center>[<a href="' . PHP_SELF2 . '"/>Return</a>]</center></h2></div></div></body></html>';

if ($row['active'] < 1) {
    //not banned, display the footer, hope the user goes away and doesn't try to talk to me
    echo '<p>You have no active bans on record.</b>' . $footer;
} else if ($row['type'] < 2) {
    echo '<p>You ' . $status . ' for the following reason: </p><br /><p><b>' . $row['reason'] . '</b></p><br /><hr />
                <p><a href="//' . SITE_ROOT . '/' . RULES . '#' . $row['board'] . '" />Please review the board rules</a> and be aware that further rule violations can result in an extended ban.</p><br />
                <h3>This warn was issued for the IP address ' . $host . '</h3>' . $footer;
} else
    echo '<p>You <b>' . $status . '</b> for the following reason: </p><br /><p><b>' . $row['reason'] . '</b></p><br /><hr />
                <p>This ban will last <b>' . $length . ' </b>. It was placed on <b>' . $row['placedon'] . '</b> and will expire: <b>' . $row['expires'] . '</b><br/><h3>This ban was issued for the IP address ' . $host . '</h3>' . $footer;

?>
