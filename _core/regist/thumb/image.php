<?php

/*

    Class that handles generating image thumbnails for various formats.

*/

class ImageThumbnail {
    private $stats = [];
    private $memory_limit_increased = false;

    function run($input, $output, $width = 250, $height = 250) {
        $this->stats = $this->process($input,$width,$height);
        $this->bumpMemory(); //Bump memory limit if we need it.

        //Generate working.
        $scratch = $this->generateScratch($input);
        if ($scratch == false) return;
        //Generate working target.
        $target = $this->generateTarget();

        ImageCopyResampled($target, $scratch, 0, 0, 0, 0, $this->stats['to_w'], $this->stats['to_h'], $this->stats['width'], $this->stats['height']);
        //$target = imagescale($target, $width, $height); //PHP 5.5+

        //Write the file.
        $ext = pathinfo($output, PATHINFO_EXTENSION);
        if ($ext == "gif" || $ext == "png") {
            ImagePNG($target, $output, 6);
        } else {
            ImageJPEG($target, $output, 60);
        }

        //General clean up.
        if ($this->memory_limit_increased) ini_restore('memory_limit'); //Restore memory limit if we bumped it.
        ImageDestroy($scratch); ImageDestroy($target);
        if (isset($pdfjpeg)) { unlink($pdfjpeg); } // if PDF was thumbnailed delete the orig jpeg

        return [
            'width' => $this->stats['to_w'],
            'height' => $this->stats['to_h'],
        ];
    }

    private function bumpMemory($input) {
        $this->memory_limit_increased = false;
        $input = $this->stats;
        $pixels = $input['width'] * $input['height'];

        if ($pixels > 3000000) {
            $this->memory_limit_increased = true;
            ini_set('memory_limit', memory_get_usage() + $pixels * 10); // for huge images
        }
        //
    }

    private function process($source,$width,$height) {
        $info = GetImageSize($source);
        $w = $info[0];
        $h = $info[1];

        //Determine maximum resolution.
        if ($w > $width || $h[1] > $height || $info[2] == 1) {
            $ratio = min(($width / $w), ($height / $h));
            $w = ceil($w * $ratio);//+1
            $h = ceil($h * $ratio);//+1
            /*if ($size[2]==1) {
            $out_w = $size[0];
            $out_h = $size[1];
            }*/ //what was this for again?
        }

        return [
            'ext' => pathinfo($source, PATHINFO_EXTENSION),
            'width' => $info[0],
            'height' => $info[1],
            'type' => $info[2],
            'to_w' => $w,
            'to_h' => $h
        ];
    }

    private function generateScratch($input) {
        switch ($this->stats['type']) {
            case 1: //GIF
                if (!function_exists("ImageCreateFromGIF")) return false;
                return ImageCreateFromGIF($input);
            case 2: //JPEG
                return ImageCreateFromJPEG($input);
                break;
            case 3: //PNG
                if (!function_exists("ImageCreateFromPNG")) return false;
                return ImageCreateFromPNG($input);
                break;
            default:
                return false;
        }
    }

    private function generateTarget() {
        $out_w = $this->stats['to_w'];
        $out_h = $this->stats['to_h'];

        $target = (function_exists("ImageCreateTrueColor")) ? ImageCreateTrueColor($out_w, $out_h) : ImageCreate($out_w, $out_h);
        ImageAlphaBlending($target, false);
        ImageSaveAlpha($target, true);

        return $target;
    }
}

?>