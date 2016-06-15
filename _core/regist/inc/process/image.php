<?php
$size = getimagesize($dest);
if (!is_array($size))
    error(S_NOREC, $dest);

//chmod($dest,0666);
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
        //error( S_UPFAIL, $dest );
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
if (GIF_ONLY == 1 && $size[2] != 1)
    error(S_UPFAIL, $dest);

if (defined('MIN_W') && MIN_W > $W)
    error(S_UPFAIL, $dest);
if (defined('MIN_H') && MIN_H > $H)
    error(S_UPFAIL, $dest);
if (defined('MAX_DIMENSION')) {
    $maxdimension = MAX_DIMENSION;
} else {
    $maxdimension = 5000;
}
if ($W > $maxdimension || $H > $maxdimension) {
    error(S_TOOBIGRES, $dest);
} elseif ($W > $maxw || $H > $maxh) {
    $W2 = $maxw / $W;
    $H2 = $maxh / $H;
    ($W2 < $H2) ? $key = $W2 : $key = $H2;
    $TN_W = ceil($W * $key);
    $TN_H = ceil($H * $key);
}