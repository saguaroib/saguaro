<?php
$image_processed = false;
$has_image = $upfile && file_exists($upfile);
//$has_embed = !empty($_POST['embed']);

if ($has_image) {
    //$has_embed = false; //If a user attaches and image AND an embed, the image has priority. Don't proccess the embed.
    // check image limit
    if ($resto) {

        $res = (int) $mysql->result("SELECT COUNT(*) FROM " . SQLLOG . " WHERE resto='$resto' AND fsize>0 AND board='$board'");
        
        if ($res > MAX_IMGRES)
            error("Max limit of " . MAX_IMGRES . " image replies has been reached.", $upfile);
        //$mysql->free_result($res);
    }

    //upload processing
    $dest = tempnam(substr($path, 0, -1), "img");
    //$dest = $path.$tim.'.tmp';
    if (OEKAKI_BOARD == 1 && $_POST['oe_chk']) {
        rename($upfile, $dest);
        chmod($dest, 0644);
        if ($pchfile)
            rename($pchfile, "$dest.pch");
    } else
        move_uploaded_file($upfile, $dest);

    clearstatcache(); // otherwise $dest looks like 0 bytes!

    $upfile_name = $sanitize->CleanStr($upfile_name, 0);
    $fsize       = filesize($dest);

    if (!is_file($dest))
        error(S_UPFAIL, $dest);
    if (!$fsize /*|| /*$fsize > MAX_KB * 1024*/)
        error(S_TOOBIG, $dest);

    preg_match('/(\.\w+)$/', $upfile_name, $ext);
    $ext = $ext[0]; //Obtain extension.

    // PDF processing
    if (ENABLE_PDF == 1 && strcasecmp('.pdf', substr($upfile_name, -4)) == 0) {
        $ext = '.pdf';
        $W   = $H = 1;
        $md5 = md5_file($dest);
        // run through ghostscript to check for validity
        if (pclose(popen("/usr/local/bin/gs -q -dSAFER -dNOPAUSE -dBATCH -sDEVICE=nullpage $dest", 'w'))) {
            error(S_UPFAIL, $dest);
        }
    } else if ($ext == ".webm") {
        global $W, $H;
        require('process/video.php');
        $processor = new VideoProcessor;
        $processor->process($dest, $upfile_name);
    } else {
        $maxw = (!$resto) ? MAX_W : MAXR_W;
        $maxh = (!$resto) ? MAX_H : MAXR_H;
        require_once("process/image.php");
    }

    $md5 = md5_file($dest);
    $mes = $upfile_name . ' ' . S_UPGOOD;
    $image_processed = true;
}

/*if ($has_embed && ALLOW_EMBED && !$image_processed) {
    if (preg_match("/(.*?)youtube.com\/watch\?v=(.*)/Usi", $_POST['embed'])) {
        $embed = htmlentities($_POST['embed']);
        $TN_W = (!$resto) ? MAX_W : MAXR_W;
        $TN_H = (!$resto) ? MAX_H : MAXR_H;
    } else {
        error(S_BADEMBED, $upfile);
    }
}*/