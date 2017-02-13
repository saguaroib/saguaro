<?php

/*

    Image info processor for Regist.

*/

class ProcessImage {
    static function run($dest) {
        $size = getimagesize($dest);
        if (!is_array($size)) return ['passCheck' => false, 'message' => S_NOREC];

        $W = $size[0];
        $H = $size[1];
        switch ($size[2]) {
            case 1:
                $ext = ".gif";
                break;
            case 2:
                $ext = ".jpg";
                break;
            case 3:
                $ext = ".png";
                break;
            default:
                //4 swf, 5 psd, 6 bmp, 7 tiff, 8 tiff, 9 jpc, 10 jp2, 11 jpx, 13 swf
                return ['passCheck' => false, 'message' => S_UPFAIL];
                break;
        }

        if (GIF_ONLY == 1 && $size[2] != 1) error(S_UPFAIL, $dest);
        if (defined('MIN_W') && MIN_W > $W) error(S_UPFAIL, $dest);
        if (defined('MIN_H') && MIN_H > $H) error(S_UPFAIL, $dest);
        $maxdimension = (defined('MAX_DIMENSION')) ? MAX_DIMENSION : 5000;
        if ($W > $maxdimension || $H > $maxdimension) {
            return ['passCheck' => false, 'message' => S_TOOBIGRES];
        }

        $info = [
            'passCheck' => true,
            'width' => (int) $W,
            'height' => (int) $H
        ];

        return $info;
    }
}