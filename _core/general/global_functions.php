<?php

// check whether the current user can perform $action (on $no, for some actions)
// board-level access is cached in $valid_cache.
function valid($action = 'moderator', $no = 0) {
    require_once(CORE_DIR . "/admin/valid.php");
    $validate = new Valid;
    return $validate->verify($action, $no);
}

function logme($mes) {
    if (defined("PROFILING") && PROFILING) {
        require_once(CORE_DIR . "/profiling/logging.php");
        $log = new Profiling;
        $log->log($mes);
    }
    return;
}

function get_cached($file) {
    static $cache = []; 
    if (!isset($cache[$file])) {
        global $mysql;
        $cache[$file] = $mysql->result("SELECT message FROM " . SQLBACKEND . "." . SQLRESOURCES . " WHERE type='{$file}' LIMIT 1");
    }
    return $cache[$file];
}

function error($mes, $dest = null, $fancy = 0) {
    require_once(CORE_DIR . "/general/error.php");
    $error = new Error();
    $error->format($mes, $dest, $fancy);
}