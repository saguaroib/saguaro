<?php

global $my_log, $mysql;

$res = (int) $res;

if (!$redir = $mysql->fetch_row("select no,resto from " . SQLLOG . " where no=" . $res)) {
    echo S_SQLFAIL;
}

list($no, $resto) = $redir;

if (!$no) {
    global $mysql;
    
    $maxq = $mysql->query("SELECT max(no) from " . SQLLOG);
    list($max) = $mysql->fetch_row($maxq);
    if (!$max || ($res > $max))
        header("HTTP/1.0 404 Not Found");
    else // res < max, so it must be deleted!
        header("HTTP/1.0 410 Gone");
    error(S_NOTHREADERR, $dest);
}

$redirect = DATA_SERVER . BOARD_DIR . "/" . RES_DIR . "" . (($resto == 0) ? $no : $resto) . '#' . $no;

echo "<META HTTP-EQUIV='refresh' content='0;URL=$redirect'>";

if ($resto == "0") {
    $my_log->update_cache();
    $my_log->update($res);
}

?>