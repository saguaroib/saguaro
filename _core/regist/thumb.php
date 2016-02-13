<?php

/*

    Used exclusively by regist().

*/

//OP thumbnail creation
function thumb($path, $tim, $ext, $child) {
    global $resto;
    $sub = ($child) ? $child : ($resto > 0) ? true : false;

    if ( !function_exists( "ImageCreate" ) || !function_exists( "ImageCreateFromJPEG" ) )
        return;

    $fname = $path . $tim . $ext;
    $thumb_dir = THUMB_DIR; //thumbnail directory
    $outpath = $thumb_dir . $tim . 's.jpg';
    
    //Determine thumbnail resolution.
    $width = (!$sub) ? MAX_W : MAXR_W;
    $height = (!$sub) ? MAX_H : MAXR_H;

    if ($ext == ".webm") {
        require_once("thumb/video.php");
        $thumb = new VideoThumbnail;
        $thumb->run($fname, $outpath, $width, $height);
    } else {
        require_once("thumb/image.php");

    }

    return $outpath;
}

?>