<?php

/*

    Handless processing video for Regist.

    Currently requires handlers to be in the path.

*/

class VideoProcessor {
    function process($input, $upfile_name) {

        $info = $this->check($input);

        if (!$info['has_video'])
            error("\"$upfile_name\ is not a valid WebM.", $dest);
        if (ALLOW_AUDIO == false && $info['has_audio'])
            error("\"$upfile_name\" contains audio!", $dest);
        if (($info['duration'] > MAX_DURATION) && (MAX_DURATION > 0))
            error("\"$upfile_name\" is too long! ({$info['duration']} > " . MAX_DURATION . ")", $dest);
        
        global $W, $H;
        $W = $info['width'];
        $H = $info['height'];
    }

    private function check ($input) {
        if ('which avprobe' || 'where avprobe') { return $this->process_avprobe($input); }
        else if ('which ffprobe' || 'where ffprobe') { return $this->process_ffprobe($input); }

        return 0;
    }

    function process_avprobe($input) {
        exec("avprobe -version", $version, $aye);
        $version = explode(" ",$version[0])[1];

        if (version_compare($version, '0.9', '>=')) {
            //At or above avprobe 0.9, which has extra options (specifically JSON).
            //Eventually.
        } else {
            //Below avprobe 0.9.
            exec("avprobe -show_format -show_streams $input", $probe);
            $probe = implode("\n", $probe); //For regex multi-line.

            preg_match("/^width=(\d+)$/msi", $probe, $width);
            preg_match("/^height=(\d+)$/msi", $probe, $height);
            preg_match("/^duration=([\d\.]+)$/msi", $probe, $dur);
            $has_video = preg_match("/^codec_type=video$/msi", $probe);
            $has_audio = preg_match("/^codec_type=audio$/msi", $probe);

            return ['width' => $width[1], 'height' => $height[1], 'duration' => round($dur[1],1), 'has_video' => $has_video, 'has_audio' => $has_audio];
        }
    }

    function process_ffprobe($input) {
        //exec("ffprobe -print_format json -show_format -show_streams $input", $out, $aye);

        var_dump($out);
    }
}

?>