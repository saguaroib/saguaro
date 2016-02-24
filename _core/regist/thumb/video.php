<?php

/*

    Class that handles generating video thumbnails for various formats and encoders.

    Currently requires handlers to be in the path.

*/

class VideoThumbnail {
    function run($input, $output = "auto", $width = 250, $height = 250) {
        $path = "";
        if ('which avconv' || 'where avconv') { $path = $this->thumb_avconv($input,$output,$width,$height); }
        else if ('which ffmpeg' || 'where ffmpeg') { $path = $this->thumb_ffmpeg($input,$output,$width,$height); }

        $temp = (class_exists('ProcessImage')) ? ProcessImage::run($path) : []; //Obtain thumbnail stats (resolution) via ProcessImage class.
        $temp['path'] = $path;

        return $temp;
    }

    function thumb_avconv($input, $output, $width, $height) {
        $inputn = preg_replace('/\\.[^.\\s]{3,4}$/', '', $input); //Strip out extension.
        $output = ($output == "auto") ? THUMB_DIR . "/" . $inputn . ".jpg" : $output;

        //Command formatting.
        $quiet = '-v 0'; //A lot of stuff still pops up on the log.
        $cmd = "$quiet -y -i '$input' -vframes 1 -vf scale='$width:-1' $output";

        exec("avconv $cmd", $status, $return);

        if ($return > 0) {
            error("avconv encountered a problem ($return)<br>$cmd", $dest);
        } else {
            return $output;
        }
    }

    function thumb_ffmpeg($input, $output, $width, $height) {
        $inputn = preg_replace('/\\.[^.\\s]{3,4}$/', '', $input); //Strip out extension.
        $output = ($output == "auto") ? THUMB_DIR . "/" . $inputn . ".jpg" : $output;

        //Command formatting.
        $quiet = '-v 0'; //A lot of stuff still pops up on the log.
        $cmd = "$quiet -y -i '$input' -vframes 1 -vf scale='$width:-1' $output";

        exec("ffmpeg $cmd", $status, $return);

        if ($return > 0) {
            error("FFmpeg encountered a problem ($return)<br>$cmd", $dest);
        } else {
            return $output;
        }
    }
}

?>