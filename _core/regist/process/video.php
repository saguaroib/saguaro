<?php

/*

    Handless processing video for Regist.

    Currently requires handlers to be in the path.

*/

class VideoProcessor {
    private $cache = [];

    private function initCache() {
        $this->cache = [
            'width' => 0,
            'height' => 0,
            'duration' => 0.0,
            'has_video' => false,
            'has_audio' => false
        ];
    }

    function process($input) {
        $this->initCache();
        $upfile_name = $_FILES["upfile"]["name"];

        $info = $this->check($input);

        if (!$info['has_video'])
            error("\"$upfile_name\" is not a valid WebM.", $input);
        if (ALLOW_AUDIO == false && $info['has_audio'])
            error("\"$upfile_name\" contains audio!", $input);
        if ($info['duration'] > MAX_DURATION)
            error("\"$upfile_name\" is too long! ({$info['duration']} > " . MAX_DURATION . ")", $input);

        return $info;
    }

    private function check($input) {
        if ('which avprobe' || 'where avprobe') { return $this->process_avprobe($input); }
        else if ('which ffprobe' || 'where ffprobe') { return $this->process_ffprobe($input); }

        return 0;
    }

    function process_avprobe($input) {
        exec("avprobe -version", $version, $aye);
        $version = explode(" ",$version[0])[1];

        if (version_compare($version, '0.9', '>=')) {
            //At or above avprobe 0.9, which has extra options (specifically JSON).
            exec("avprobe -v 0 -show_streams -show_format \"$input\" -of json", $probe);
            $probe = json_decode(implode($probe,""), true); //Convert the JSON to an associative array.
            $out = $this->cache;

            if ($probe['format']['nb_streams'] > 2) {
                $out['has_video'] = false; //Suspicious amount of streams (typically only expect single audio+video).
                return $out;
            }

            $out['duration'] = round($probe['format']['duration'],1);

            foreach ($probe['streams'] as $stream) {
                switch ($stream['codec_type']) {
                    case 'video':
                        $out['has_video'] = true;
                        $out['width'] = $stream['width'];
                        $out['height'] = $stream['height'];
                        break;
                    case 'audio':
                        $out['has_audio'] = true;
                        break;
                }
            }

            return $out;
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

        //var_dump($out);
    }
}

?>