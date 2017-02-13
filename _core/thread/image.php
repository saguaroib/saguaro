<?php
/*

    Formats images for OPs and replies.

    Shouldn't be used without a parent (post.php).

*/

class Image {
    public $inIndex = false; //Really want to start extending as of 30 years ago.

    function format($no, $resto, $input) {
        global $spoiler;
        @extract($input);

        $imgdir   = IMG_DIR;
        $thumbdir = DATA_SERVER . BOARD_DIR . "/" . THUMB_DIR;
        $cssimg   = CSS_PATH;

        // Picture file name
        $img        = $path . $localname;
        $displaysrc = DATA_SERVER . BOARD_DIR . "/" . $imgdir . $localname;
        $linksrc    = ((USE_SRC_CGI == 1) ? (str_replace(".cgi", "", $imgdir) . $localname) : $displaysrc);
        if (defined('INTERSTITIAL_LINK'))
            $linksrc = str_replace(INTERSTITIAL_LINK, "", $linksrc);
        $src = IMG_DIR . $localname;
        if ($filename == 'image.jpg')
            $filename = time();
        $longname  = $filename;
        if (!$filename)
            $longname = $localname; //Legacy support for boards that didn't store an $fname in the table pre saguaro 0.99.0
        $shortname = (strlen($filename) > 40) ? substr($filename, 0, 40) . "(...)" . $extension : $longname;
        // img tag creation
        $imgsrc    = "";
        if ($extension !=='.') {
            // turn the 32-byte ascii md5 into a 24-byte base64 md5
            $shortmd5 = base64_encode(pack("H*", $hash));
            if ($filesize >= 1048576) {
                $size = round(($filesize / 1048576), 2) . " M";
            } else if ($filesize >= 1024) {
                $size = round($filesize / 1024) . " K";
            } else {
                $size = $filesize . " ";
            }

            if (!$thumb_width && !$thumb_height && $extension == ".gif") {
                $thumb_width = $width;
                $thumb_height = $height;
            }

            $local = THUMB_DIR . $localthumb;
            $thumb = $thumbdir . $localthumb;

            if ($spoiler) {
                $size   = "Spoiler Image, $size";
                $imageFile = "<img src='" . CSS_PATH . "/imgs/spoiler.png' alt='{$size}B' md5='{$shortmd5}'>";
            } else {
                if (!$filedeleted && ($thumb_width && $thumb_height)) {
                    $imageFile = "<img class='postimg' src='" . $thumbdir . $localthumbname . "' {$style} alt='{$size}B' data-md5='{$shortmd5}'>";
                }
            }

            if ($filedeleted) {
                return  "<img class='deleted' src='$cssimg/imgs/filedeleted.gif' alt='File deleted.'>";
            } else {
                $dimensions = ($ext == ".pdf") ? "PDF" : "{$width}x{$height}";
                $name = ($this->inIndex) ? $shortname : $longname;
                
                //if ($resto) {
                    $temp = "<div class='fileImg' id='fim{$no}'>";
                    $temp .= "<a class='fileThumb' href='$displaysrc' target='_blank'>$imageFile</a><br><div class='fileText' id='fT{$no}'><a href='$linksrc' target='_blank'>$name</a> <br>{$size}B, $dimensions</div></div>";
                /*} else {
                    $temp = "<div class='fileImg' id='fim{$no}'><div class='fileText' id='fT{$no}'>" . S_PICNAME . "<a href='$linksrc' target='_blank'>$name</a> ({$size}B, $dimensions)</div>";
                    $temp .= "<a class='fileThumb' href='$displaysrc' target='_blank'>$imageFile</a></div>";
                }*/
                clearstatcache();
                return $temp;
            }

            return "<span class='tn_thread' title='Image Unavailable'>Image unavailable</span></a>";
        }
    }
}