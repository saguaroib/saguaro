<?php
/*

    Formats images for OPs and replies.

    Shouldn't be used without a parent (post.php).

*/

class Image {
    public $inIndex = false; //Really want to start extending as of 30 years ago.

    function format($input) {
        global $spoiler;
        extract($input);

        $imgdir   = IMG_DIR;
        $thumbdir = DATA_SERVER . BOARD_DIR . "/" . THUMB_DIR;
        $cssimg   = CSS_PATH;

        // Picture file name
        $img        = $path . $tim . $ext;
        $displaysrc = DATA_SERVER . BOARD_DIR . "/" . $imgdir . $tim . $ext;
        $linksrc    = ((USE_SRC_CGI == 1) ? (str_replace(".cgi", "", $imgdir) . $tim . $ext) : $displaysrc);
        if (defined('INTERSTITIAL_LINK'))
            $linksrc = str_replace(INTERSTITIAL_LINK, "", $linksrc);
        $src = IMG_DIR . $tim . $ext;
        if ($fname == 'image')
            $fname = time();
        $longname  = $fname;
        if (!$fname)
            $longname = $tim.$ext; //Legacy support for boards that didn't store an $fname in the table pre saguaro 0.99.0
        $shortname = (strlen($fname) > 40) ? substr($fname, 0, 40) . "(...)" . $ext : $longname;
        // img tag creation
        $imgsrc    = "";
        if ($ext) {
            // turn the 32-byte ascii md5 into a 24-byte base64 md5
            $shortmd5 = base64_encode(pack("H*", $md5));
            if ($fsize >= 1048576) {
                $size = round(($fsize / 1048576), 2) . " M";
            } else if ($fsize >= 1024) {
                $size = round($fsize / 1024) . " K";
            } else {
                $size = $fsize . " ";
            }

            if (!$tn_w && !$tn_h && $ext == ".gif") {
                $tn_w = $w;
                $tn_h = $h;
            }

            if ($spoiler) {
                $size   = "Spoiler Image, $size";
                $imgsrc = "<br><a href='$displaysrc' target='_blank'><img src='" . CSS_PATH . "/imgs/spoiler.png' border='0' align='left' hspace='20' alt='{$size}B' md5='$shortmd5'></a>";
            } elseif ($tn_w && $tn_h) { //when there is size...
                if (@is_file(THUMB_DIR . $tim . 's.jpg')) {
                    $imgsrc = "<br><a href='" . $displaysrc . "' target='_blank'><img class='postimg' src='" . $thumbdir . $tim . 's.jpg' . "' style='margin: 0px 20px;' width='$tn_w' height='$tn_h'' alt='" . $size . "B' md5='$shortmd5'></a>";
                } else {
                    $imgsrc = "<a href='$displaysrc' target='_blank'><span class='tn_thread' title='{$size}B'>Thumbnail unavailable</span></a>";
                }
            } else {
                if (@is_file(THUMB_DIR . $tim . 's.jpg')) {
                    $imgsrc = "<br><a href='$displaysrc' target='_blank'><img class='postimg' src='" . $thumbdir . $tim . 's.jpg' . "' style='margin: 0px 20px;' alt='{$size}B' md5='$shortmd5'></a>";
                } else {
                    $imgsrc = "<a href='$displaysrc' target='_blank'><span class='tn_thread' title='{$size}B'>Thumbnail unavailable</span></a>";
                }
            }

            if (!is_file($src)) {
                return  "<img src='$cssimg/imgs/filedeleted.gif' alt='File deleted.'>";
            } else {
                $dimensions = ($ext == ".pdf") ? "PDF" : "{$w}x{$h}";
                $name = ($this->inIndex) ? $shortname : $longname;
                $temp = "<div class='file'><span class='filesize'>" . S_PICNAME . "<a href='$linksrc' target='_blank'>$name</a> ({$size}B, $dimensions)</span>";

                if (!$this->inIndex) //, <span title='" . $longname . "'>" . $shortname . "</span>)
                    $temp .= "$imgsrc</div>";
                else
                    $temp .= "</div><div class='fileThumb'>$imgsrc</div>";

                return $temp;
            }

            return "<span class='tn_thread' title='Image Unavailable'>Image unavailable</span></a>";
        }
    }
}

?>
