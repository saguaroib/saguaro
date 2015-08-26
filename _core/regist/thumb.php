<?php

/*

    Used exclusively by regist().

*/

//OP thumbnail creation
function thumb( $path, $tim, $ext ) {
    if ( !function_exists( "ImageCreate" ) || !function_exists( "ImageCreateFromJPEG" ) )
        return;
    $fname     = $path . $tim . $ext;
    $thumb_dir = THUMB_DIR; //thumbnail directory
    $outpath   = $thumb_dir . $tim . 's.jpg';
    if ( !$resto ) {
        $width  = MAX_W; //output width
        $height = MAX_H; //output height
    } else {
        $width  = MAXR_W; //output width (imgreply)
        $height = MAXR_H; //output height (imgreply)
    }
    // width, height, and type are aquired
    $size                   = GetImageSize( $fname );
    $memory_limit_increased = false;
    if ( $size[0] * $size[1] > 3000000 ) {
        $memory_limit_increased = true;
        ini_set( 'memory_limit', memory_get_usage() + $size[0] * $size[1] * 10 ); // for huge images
    }
    switch ( $size[2] ) {
        case 1:
            if ( function_exists( "ImageCreateFromGIF" ) ) {
                $im_in = ImageCreateFromGIF( $fname );
                if ( $im_in ) {
                    break;
                }
            }
        /*
        //Legacy gif processing, requires gif2png to be present in the board dir. You can find the file here: http://freecode.com/projects/gif2png
        
        if ( !is_executable( realpath( "gif2png" ) ) || !function_exists( "ImageCreateFromPNG" ) )
        return;
        @exec( realpath( "gif2png" ) . " $fname", $a );
        if ( !file_exists( $path . $tim . '.png' ) )
        return;
        $im_in = ImageCreateFromPNG( $path . $tim . '.png' );
        unlink( $path . $tim . '.png' );
        if ( !$im_in )
        return;
        break;*/
        case 2:
            $im_in = ImageCreateFromJPEG( $fname );
            if ( !$im_in ) {
                return;
            }
            break;
        case 3:
            if ( !function_exists( "ImageCreateFromPNG" ) )
                return;
            $im_in = ImageCreateFromPNG( $fname );
            if ( !$im_in ) {
                return;
            }
            break;
        default:
            return;
    }
    // Resizing
    if ( $size[0] > $width || $size[1] > $height || $size[2] == 1 ) {
        $key_w = $width / $size[0];
        $key_h = $height / $size[1];
        ( $key_w < $key_h ) ? $keys = $key_w : $keys = $key_h;
        $out_w = ceil( $size[0] * $keys ) + 1;
        $out_h = ceil( $size[1] * $keys ) + 1;
        /*if ($size[2]==1) {
        $out_w = $size[0];
        $out_h = $size[1];
        } //what was this for again? */
    } else {
        $out_w = $size[0];
        $out_h = $size[1];
    }
    // the thumbnail is created
    if ( function_exists( "ImageCreateTrueColor" ) ) {
        $im_out = ImageCreateTrueColor( $out_w, $out_h );
    } else {
        $im_out = ImageCreate( $out_w, $out_h );
    }
    ImageAlphaBlending( $im_out, false );
    ImageSaveAlpha( $im_out, true );
    // copy resized original
    ImageCopyResampled( $im_out, $im_in, 0, 0, 0, 0, $out_w, $out_h, $size[0], $size[1] );
    // thumbnail saved
    if ( $ext == ".gif" || $ext == ".png" )
        ImagePNG( $im_out, $outpath, 6 );
    else
        ImageJPEG( $im_out, $outpath, 60 );
    //chmod($thumb_dir.$tim.'s.jpg',0666);
    // created image is destroyed
    ImageDestroy( $im_in );
    ImageDestroy( $im_out );
    if ( isset( $pdfjpeg ) ) {
        unlink( $pdfjpeg );
    } // if PDF was thumbnailed delete the orig jpeg
    if ( $memory_limit_increased )
        ini_restore( 'memory_limit' );
    
    return $outpath;
}

?>