<?php

/*

    Used exclusively by regist().

*/

//OP thumbnail creation
function thumb($input, $child) {
    if (!function_exists("ImageCreate") || !function_exists("ImageCreateFromJPEG"))
        return;

    //$fname = $path . $tim . $ext;
    $ext = pathinfo($input, PATHINFO_EXTENSION);
    $outpath = THUMB_DIR . pathinfo($input, PATHINFO_FILENAME) . 's.jpg';

    //Determine thumbnail resolution.
    $width = (!$child) ? MAX_W : MAXR_W;
    $height = (!$child) ? MAX_H : MAXR_H;

    if ($ext == "webm") {
        require_once("video.php");
        $thumb = new VideoThumbnail;
        $thumb->run($input, $outpath, $width, $height);
    } else {
        require_once("image.php");
    }

    return [
        'location' => $outpath,
        'filename' => basename($outpath),
        'width' => $width,
        'height' => $height
    ];
}

?>