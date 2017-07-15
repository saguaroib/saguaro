<?php
/*

    Formats a post based on the input given.

    Shouldn't be used without a parent (thread.php).

*/

require_once("image.php");

class CatalogPost {
    function format($input,$stats) {
        if (empty($input)) {
            return;
        } else {
            @extract($input);
        }
        $temp = "<div class='catalog_item' id='ci{$input['no']}'>"; 

        $image = new CatalogImage;
        $temp .= $image->format($no, $resto, $media);

        $temp .= "<span class='catalog-stats' title='Reply count / Image count'>R: " . $stats["replies"] . " / I: " . $stats["images"] . "</span><br>";
        $sub = ($input["sub"]) ? "<b>{$input["sub"]}</b>: " : null;
        $temp .= "<span class='catalog-com' id='cc{$input['no']}'>{$sub}{$input["com"]}</span><br>";

        $temp .= "</div>";

        return $temp;
    }
}