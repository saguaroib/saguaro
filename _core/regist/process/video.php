<?php

/*

    Handless processing video for Regist.

    Currently requires handlers to be in the path.

*/

class VideoProcessor {
    function process($input) {
        $upfile_name = $input['name'];

        $info = $this->check($input['temp']);

        //Holy duplicate lines.
        if (!$info['has_video']) {
            $info['passCheck'] = false;
            $info['message'] = "\"$upfile_name\" is not a valid WebM.";
        }
        if ($info['width'] < MIN_W || $info['height'] < MIN_H) {
            $info['passCheck'] = false;
            $info['message'] = "\"$upfile_name\"'s resolution is too small!";
        }
        if (ALLOW_AUDIO == false && $info['has_audio']) {
            $info['passCheck'] = false;
            $info['message'] = "\"$upfile_name\" contains audio!";
        }
        if ($info['duration'] > MAX_DURATION) {
            $info['passCheck'] = false;
            $info['message'] = "\"$upfile_name\" is longer than allowed! (" . MAX_DURATION . ")";
        }
        if ($info['duration'] == 0) {
            $info['passCheck'] = false;
            $info['message'] = "Generic video problem.";
        }

        return $info;
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

            return [
                'width' => (int) $width[1],
                'height' => (int) $height[1],
                'duration' => round($dur[1],1),
                'has_video' => $has_video,
                'has_audio' => $has_audio
            ];
        }
    }

    function process_ffprobe($input) {
        //exec("ffprobe -print_format json -show_format -show_streams $input", $out, $aye);

        var_dump($out);
    }
}

?>