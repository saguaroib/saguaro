<?php

/*

    Used exclusively by regist().

*/

//OP thumbnail creation
function thumb( $path, $tim, $ext, $resto ) {
    if ( !function_exists( "ImageCreate" ) || !function_exists( "ImageCreateFromJPEG" ) )
        return;

    $fname = $path . $tim . $ext;
    $thumb_dir = THUMB_DIR; //thumbnail directory
    $outpath = $thumb_dir . $tim . 's.jpg';
    
    //Determine thumbnail resolution.
    $width = (!$resto) ? MAX_W : MAXR_W;
    $height = (!$resto) ? MAX_H : MAXR_H;

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