<?php

global $my_log, $mysql;

if (!is_numeric($_GET['res']))
    error("no");

$res = (int) $_GET['res'];

$redir = $mysql->fetch_assoc("SELECT no,resto FROM " . SQLLOG . " WHERE no='{$res}'");

$resto = (int) $redir['resto'];
$no = (int) $redir['no'];

if (!$redir['no']) {
    error(S_NOTHREADERR, $dest);
}

$redirect = DATA_SERVER . BOARD_DIR . "/" . RES_DIR . "" . (($resto == 0) ? $redir['no'] . PHP_EXT : $redir['resto']) . PHP_EXT'#' . $redir['no'];

echo "<META HTTP-EQUIV='refresh' content='0;URL={$redirect}'>";

?>