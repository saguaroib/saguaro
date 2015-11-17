<?php
include("config.php");

require_once(CORE_DIR . "/mysql/mysql.php");
$mysql = new SaguaroMySQL;
$mysql->init();

$host   = $_SERVER['REMOTE_ADDR'];
$deny = 0;
require_once(CORE_DIR . "/admin/banish.php");

$dis = new Banish;
if ($dis->checkBan($host) )
	$deny = 1;

if ($deny) {

	$result = $mysql->query("SELECT * FROM " . SQLBANLOG . " WHERE ip='" . $host . "' AND active <> 0 LIMIT 1");
	while($row = $mysql->fetch_assoc($result)) {
		$placed = $row['placedon'];
		$board  = $row['board'];
		$type     = $row['type'];
		$reason = $row['reason'];
		$expires = $row['expires'];
	}

	$length = ( ( ($expires - $placed ) / 60 ) / 60 ) / 24; //MATH SON

    switch ($type) {
        case '1':
            $status = 'have been warned on: <b>/' . BOARD_DIR . '/ - ' . TITLE . '</b>';
			$mysql->query("UPDATE " . SQLBANLOG . " SET active='0' WHERE ip='$host' AND active='1' LIMIT 1");
			$warned = 1;
            break;
        case '2':
            $status    = 'have been banned from: <b>/' . BOARD_DIR . '/ - ' . TITLE . '</b>';
			if ( time() > $expires )
				$mysql->query("UPDATE " . SQLBANLOG . " SET active='0' WHERE ip='$host' AND active='1' LIMIT 1");
            $expires   = date('F d, Y H:i', $expires) . " days";
            break;
        case '3':
            $status  = 'have been banned from <b>all boards</b>';
			if ( time() > $expires )
				$mysql->query("UPDATE " . SQLBANLOG . " SET active='0' WHERE ip='$host' AND active='1' LIMIT 1");
            $expires   = date('F d, Y H:i', $expires) . " days";
            break;
        case '4':
            $status  = 'have been <b>permanently banned from all boards<b>';
            $length = '<b>forever</b>';
            break;
        default:
            $status = 'are not banned';
            $type   = 0;
            break;
	}
	$placed = date('F d, Y H:i', $placed);
} else {
    $type   = 0;
    $status = 'are not banned';
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>You ' . $status . '</title>
<link href="' . CSS_PATH . '/stylesheets/bancss.css" rel="stylesheet" type="text/css" /></head><body>
<div class="container">
<div class="header"></a><h1>You <b>' . $status . '.</b></h1></div>';

$footer = '<div class="footer"><h2><center>[<a href="' . PHP_SELF . '"/>Return</a>]</center></h2></div></div></body></html>';

if (!$type) {
    //not banned, display the footer, hope the user goes away and doesn't try to talk to me
    echo '<p>You are not banned from posting on board: <b>/' . BOARD_DIR . '/ - ' . TITLE . '</b>' . $footer;
} else if ($warned) {
    echo '<p>You ' . $status . ' for the following reason: </p><br /><p><b>' . $reason . '</b></p><br /><hr />
                <p><a href="//' . SITE_ROOT . '/' . RULES . '#' . BOARD_DIR . '" />Please review the board rules</a> and be aware that further rule violations can result in an extended ban.</p><br />
                <h3>This warn was issued for the IP address ' . $host . '</h3>' . $footer;
} else
    echo '<p>You <b>' . $status . '</b> for the following reason: </p><br /><p><b>' . $reason . '</b></p><br /><hr />
                <p>This ban will last <b>' . $length . ' </b>. It was placed on <b>' . $placed . '</b> and will expire: <b>' . $expires . '</b><br/><h3>This ban was issued for the IP address ' . $host . '</h3>' . $footer;

?>
