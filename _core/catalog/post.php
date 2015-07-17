<?php
/*

    Formats a post based on the input given.

    Shouldn't be used without a parent (thread.php).

*/

include("image.php");

class Post {
    function format($input,$stats) {
        extract($input);
        
        $temp = "<div class='catalog_item'>"; 
        
        $image = new Image;
        $temp .= $image->format($input);
        
        $temp .= "<br>";
        $temp .= "<span class='stats'>R: " . $stats["replies"] . " / I: " . $stats["images"] . "</span><br>";
        $temp .= "<span class='subject'>" . $input["sub"] . "</span><br>";
        $temp .= "<span class='comment'>" . $input["com"] . "</span><br>";
        
        $temp .= "</div>";

        return $temp;
    }
}

?>