<?php

/*

    Image info processor for Regist.

*/

class ProcessImage {
    function run($dest) {
        $size = getimagesize($dest);
        if (!is_array($size))
            error(S_NOREC, $dest);

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
            case 4:
                $ext = ".swf";
                error(S_UPFAIL, $dest);
                break;
            case 5:
                $ext = ".psd";
                error(S_UPFAIL, $dest);
                break;
            case 6:
                $ext = ".bmp";
                error(S_UPFAIL, $dest);
                break;
            case 7:
                $ext = ".tiff";
                error(S_UPFAIL, $dest);
                break;
            case 8:
                $ext = ".tiff";
                error(S_UPFAIL, $dest);
                break;
            case 9:
                $ext = ".jpc";
                error(S_UPFAIL, $dest);
                break;
            case 10:
                $ext = ".jp2";
                error(S_UPFAIL, $dest);
                break;
            case 11:
                $ext = ".jpx";
                error(S_UPFAIL, $dest);
                break;
            case 13:
                $ext = ".swf";
                error(S_UPFAIL, $dest);
                break;
            default:
                $ext = ".xxx";
                error(S_UPFAIL, $dest);
                break;
        }

        if (GIF_ONLY == 1 && $size[2] != 1) error(S_UPFAIL, $dest);
        if (defined('MIN_W') && MIN_W > $W) error(S_UPFAIL, $dest);
        if (defined('MIN_H') && MIN_H > $H) error(S_UPFAIL, $dest);
        $maxdimension = (defined('MAX_DIMENSION')) ? MAX_DIMENSION : 5000;
        if ($W > $maxdimension || $H > $maxdimension) {
            error(S_TOOBIGRES, $dest);
        }

        $info = [
            'width' => (int) $W,
            'height' => (int) $H
        ];

        return $info;
    }
}

?>