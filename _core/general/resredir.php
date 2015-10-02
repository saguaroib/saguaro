<?php

global $my_log;

$res = (int) $res;

if (!$redir = mysql_call("select no,resto from " . SQLLOG . " where no=" . $res)) {
    echo S_SQLFAIL;
}

list($no, $resto) = mysql_fetch_row($redir);

if (!$no) {
    $maxq = mysql_call("select max(no) from " . SQLLOG . "");
    list($max) = mysql_fetch_row($maxq);
    if (!$max || ($res > $max))
        header("HTTP/1.0 404 Not Found");
    else // res < max, so it must be deleted!
        header("HTTP/1.0 410 Gone");
    error(S_NOTHREADERR, $dest);
}

$redirect = DATA_SERVER . BOARD_DIR . "/res/" . (($resto == 0) ? $no : $resto) . PHP_EXT . '#' . $no;

echo "<META HTTP-EQUIV='refresh' content='0;URL=$redirect'>";

if ($resto == "0") {
    $my_log->update_cache();
    $my_log->update($res);
}

?>