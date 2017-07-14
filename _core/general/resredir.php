<?php

global $my_log, $mysql;

$res = (int) $res;
$redirect = $mysql->fetch_assoc("SELECT no,resto FROM " . SQLLOG . " WHERE no=:res", [":res" => $res], ['i'])[0];

if (!$redirect['no']) {
    global $mysql;
    
    $maxq = $mysql->result("SELECT MAX(no) FROM " . SQLLOG);
    list($max) = $mysql->fetch_row($maxq);
    if (!$max || ($res > $max))
        header("HTTP/1.0 404 Not Found");
    else // res < max, so it must be deleted!
        header("HTTP/1.0 410 Gone");
    error(S_NOTHREADERR, $dest);
}

$redirectect = DATA_SERVER . BOARD_DIR . "/" . RES_DIR . "/" . (($redirect['resto'] == 0) ? $redirect['no'] : $redirect['resto']) . PHP_EXT . '#' . $redirect['no'];

echo "<META HTTP-EQUIV='refresh' content='0;URL=$redirectect'>";

if ($redirect['resto'] == "0") {
    $my_log->update_cache();
    $my_log->update($res);
}