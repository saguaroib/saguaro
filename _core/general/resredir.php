<?php

global $my_log, $mysql;

$res = (int) $res;

if (!$redir = $mysql->fetch_row("SELECT no,resto FROM " . SQLLOG . " WHERE no=" . $res . " AND board='" . BOARD_DIR . "' LIMIT 1")) {
    echo S_SQLFAIL;
}

list($no, $resto) = $redir;

if (!$no) {
    global $mysql;
    
    $max = $mysql->result("SELECT MAX(no) FROM " . SQLLOG . " WHERE board='" . BOARD_DIR . "' LIMIT 1");
    if (!$max || ($res > $max))
        header("HTTP/1.0 404 Not Found");
    else // res < max, so it must be deleted!
        header("HTTP/1.0 410 Gone");
    error(S_NOTHREADERR, $dest);
}

$redirect = DATA_SERVER . BOARD_DIR . "/" . RES_DIR . "/" . (($resto == 0) ? $no : $resto) . PHP_EXT . '#' . $no;

echo "<META HTTP-EQUIV='refresh' content='0;URL=$redirect'>";

if ($resto == "0") { //wat
    $my_log->update_cache();
    $my_log->update($res);
}

?>