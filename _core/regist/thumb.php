<?php

/*

    Used exclusively by regist().

*/

//OP thumbnail creation
function thumb( $path, $tim, $ext ) {
    global $resto;

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
        $thumb->config = ['width' => $width, 'height' => $height]; //Very stupid way but it works for now.
        $thumb->run($fname, $outpath);
    } else {
        require_once("thumb/image.php");

    }

    return $outpath;
}

?>