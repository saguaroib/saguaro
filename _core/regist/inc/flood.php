<?php
if ($com) {
    // Check for duplicate comments
    $query  = "SELECT COUNT(no) FROM " . SQLLOG . " WHERE com='" . $mysql->escape_string($com) . "' AND host='$host' AND time>" . ($time - RENZOKU_DUPE) ."";
    
    if ($mysql->result($query) > 0)
        error(S_RENZOKU, $dest);
}

if (!$has_image) {
    // Check for flood limit on replies
    $query  = "SELECT COUNT(no) FROM " . SQLLOG . " WHERE time>" . ($time - RENZOKU) . " AND host='$host' AND resto>0 AND board='$board'";
    if ($mysql->result($query))
        error(S_RENZOKU, $dest);
}

if (!$resto) {
    // Check flood limit on new threads
    $query  = "SELECT COUNT(no) FROM " . SQLLOG . " WHERE time>" . ($time - RENZOKU3) . " AND host='$host' AND resto=0 AND board='$board'"; //root>0 == non-sticky
    if ($mysql->result($query) > THREADS_PER_USER)
        error(S_RENZOKU3, $dest);
}

// Upload processing
if ($has_image) {
    if (!$may_flood) {
        $query  = "SELECT COUNT(no) FROM " . SQLLOG . " WHERE time>" . ($time - RENZOKU2) . " AND host='$host' AND resto>0 AND board='$board'";
        if ($mysql->result($query))
            error(S_RENZOKU2, $dest);
    }
    
    //Duplicate image check
    $row = $mysql->fetch_assoc("SELECT no,resto FROM " . SQLLOG . " WHERE md5='$md5' AND board='$board'");
    if ($row['no']) {
        if (!$row['resto'])
            $row['resto'] = $row['no'];
        error('<a href="' . DATA_SERVER . BOARD_DIR . "/" . RES_DIR . $row['resto'] . PHP_EXT . '#' . $row['no'] . '">' . S_DUPE . '</a>', $dest);
    }
}