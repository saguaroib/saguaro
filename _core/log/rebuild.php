<?php

/*

    Proxy for Rebuild*.

*/

global $mysql;

function rebuildqueue_create_table() {
    //Moved into test.php.
}

function rebuildqueue_add($no) {
    global $mysql;

    $board = BOARD_DIR;
    $no    = (int) $no;
    for ($i = 0; $i < 2; $i++)
        if (!$mysql->query("INSERT IGNORE INTO rebuildqueue (board,no) VALUES (:board,:no)", [":board" => $board, ":no" => $no], ['si']))
            rebuildqueue_create_table();
        else
            break;
}

function rebuildqueue_remove($no) {
    global $mysql;

    $board = BOARD_DIR;
    $no    = (int) $no;
    for ($i = 0; $i < 2; $i++)
        if (!$mysql->query("DELETE FROM rebuildqueue WHERE board=:board AND no=:no", [":board" => $board, ":no" => $no], ['si']))
            rebuildqueue_create_table();
        else
            break;
}

function rebuildqueue_take_all() {
    global $mysql;

    $board = BOARD_DIR;
    $uid   = (int) mt_rand(1, mt_getrandmax());
    for ($i = 0; $i < 2; $i++)
        if (!$mysql->query("UPDATE rebuildqueue SET ownedby=:uid, ts=ts WHERE board=:board AND ownedby=0", [":uid" => $uid, ":board" => $board], ['is']))
            rebuildqueue_create_table();
        else
            break;
    $q = $mysql->query("SELECT no FROM rebuildqueue WHERE board=:board AND ownedby=:ownedby", [":uid" => $uid, ":board" => $board], ['is']);
    $posts = array();
    while ($post = $mysql->fetch_assoc($q))
        $posts[] = $post['no'];
    return $posts;
}

function rebuild($all = 0) {
    global $mysql, $my_log;

    if (!valid('moderator'))
        die('Update failed...');

    header("Pragma: no-cache");
    echo "Rebuilding " . (($all) ? "all" : "missing") . ' replies and pages... <a href="' . PHP_SELF2_ABS . '">Go back</a><br><br>';

    ob_end_flush();
    $starttime = microtime(true);
    $treeline = $mysql->fetch_assoc("SELECT no,resto FROM " . SQLLOG . " WHERE root>0 ORDER BY root DESC");

    echo "Writing...\n";
    if ($all || !defined('CACHE_TTL')) {
        foreach ($treeline as $row) {
            if ($row['resto'] == 0) {
                $my_log->update($row['no'], 1);
                echo "No.$no created.<br>\n";
            }
        }
        $my_log->update();
        echo "Index pages created.<br>\n";
    } else {
        $posts = rebuildqueue_take_all();
        foreach ($posts as $no) {
            $deferred = ($my_log->update($no, 1) ? ' (deferred)' : '');
            if ($no)
                echo "No.$no created.$deferred<br>\n";
            else
                echo "Index pages created.$deferred<br>\n";
        }
    }
    $totaltime = microtime(true) - $starttime;
    echo "<br>Time elapsed (lock excluded): $totaltime seconds", "<br>Pages created.<br><br>\nRedirecting back to board.\n<META HTTP-EQUIV=\"refresh\" content=\"10;URL=" . PHP_SELF2 . "\">";
}