<?php
/*

    Formats a post based on the input given.

    Shouldn't be used without a parent (thread.php).

*/

include("image.php");

class Post {
    public $data = [];

    function format() {
        extract($this->data);

        if ($email) $name = "<a href='mailto:$email' class='linkmail'>$name</a>";
        if (strpos($sub, "SPOILER<>") === 0 ) {
            $sub = substr( $sub, strlen( "SPOILER<>" ) ); //trim out SPOILER<>
            $spoiler = 1;
        } else {
            $spoiler = 0;
        }

        $temp = "<a name='$no'></a>\n";
        $temp .= "<table><tr><td nowrap class='doubledash'>&gt;&gt;</td><td id='p$no' class='reply'>\n";
        $temp .= "<input type=checkbox name='$no' value=delete><span class='replytitle'>$sub</span> \n";
        $temp .= "<span class='commentpostername'>$name</span> $now <span id='norep$no'>";

        if ( $resno ) {
            $temp .= '<a href="#$no" class="quotejs">No.</a><a href="javascript:insert(\"$no\")" class="quotejs">$no</a></span>';
        } else {
            $temp .= "<a href='" . RES_DIR . $resto . PHP_EXT . "#$no' class='quotejs'>No.</a><a href='" . RES_DIR . $resto . PHP_EXT . "#q$no' class='quotejs'>$no</a></span>";
        }

        $temp .= "<br>";

        $image = new Image;
        $temp .= $image->format($this->data);

        $temp .= "<blockquote>$com</blockquote>";

        $temp .= "</td></tr></table>\n";

        return $temp;
    }

    function formatOP() {
        extract($this->data);

        $image = new Image();
        $temp = $image->format($this->data);

        $temp .= "<a name=\"$resno\"></a>\n<input type=checkbox name=\"$no\" value=delete><span class=\"filetitle\">$sub</span> \n";
        $temp .= "<span class=\"postername\">$name</span> $now <span id=\"nothread$no\">";

        $stickyicon = ($sticky) ? ' <img src="' . CSS_PATH . '/sticky.gif" alt="sticky"> ' : "";

        if ($locked) $stickyicon .= ' <img src="' . CSS_PATH . '/locked.gif" alt="closed"> ';

        if ( $resno ) {
            $temp .= "<a href=\"#$no\" class=\"quotejs\">No.</a><a href=\"javascript:insert('$no')\" class=\"quotejs\">$no</a> $stickyicon &nbsp; ";
        } else {
            $temp .= "<a href=\"" . RES_DIR . $no . PHP_EXT . "#" . $no . "\" class=\"quotejs\">No.</a><a href=\"" . RES_DIR . $no . PHP_EXT . "#q" . $no . "\" class=\"quotejs\">$no</a> $stickyicon &nbsp; [<a href=\"" . RES_DIR . $no . PHP_EXT . "\">" . S_REPLY . "</a>]";
        }

        $temp .= "<br>";
        $temp .= "</span>\n<blockquote>$com</blockquote>";

        return $temp;
    }
}

?>