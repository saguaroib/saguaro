<?php
/*

    Needs to be re-written to be class-based.

    For the first step we'll atleast make it modular.

*/

if ($_SERVER['SCRIPT_FILENAME'] !== __FILE__) { //Only run if the file is not being executed directly.
    function log_cache($invalidate = 0) {
        require_once('cache.php');
    }

    function updatelog($resno = 0, $rebuild = 0) {
        require_once('update.php');
    }
}

?>
