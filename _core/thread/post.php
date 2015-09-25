<?php
/*

    Formats a post based on the input given.

    Shouldn't be used without a parent (thread.php).

*/

include("image.php");

class Post {
    public $data = [];
    public $inIndex = false; //Until I feel like extending.

    function format() {
        extract($this->data);

        if ($email) $name = "<a href='mailto:$email' class='linkmail'>$name</a>";
        if (strpos($sub, "SPOILER<>") === 0) {
            $sub = substr($sub, strlen("SPOILER<>")); //trim out SPOILER<>
            $spoiler = 1;
        } else {
            $spoiler = 0;
        }

        $temp = "<a name='$no'></a>\n";
        $temp .= "<table><tr><td nowrap class='doubledash'>&gt;&gt;</td><td id='$no' class='reply'>\n";
        $temp .= "<input type=checkbox name='$no' value=delete><span class='replytitle'>$sub</span> \n";
        $temp .= "<span class='commentpostername'>$name</span> $now <span id='norep$no'>";

        $com = $this->abbr($com, MAX_LINES_SHOWN);
        $com = $this->auto_link($com, $resno);

        if (!$this->inIndex) {
            $temp .= "<a href='#$no' class='quotejs'>No.</a><a href='javascript:insert(\">>$no\")' class='quotejs'>$no</a></span>";
        } else {
            $temp .= "<a href='" . RES_DIR . $resto . PHP_EXT . "#$no' class='quotejs'>No.</a><a href='" . RES_DIR . $resto . PHP_EXT . "#q$no' class='quotejs'>$no</a></span>";
        }

        $temp .= "<br>";

        $image = new Image;
        $image->inIndex = $this->inIndex;
        $temp .= $image->format($this->data);

        $temp .= "<blockquote>$com</blockquote>";

        $temp .= "</td></tr></table>\n";

        return $temp;
    }

    function formatOP() {
        extract($this->data);

        $image = new Image;
        $image->inIndex = $this->inIndex;
        $temp = $image->format($this->data);

        $temp .= "<a name='$resno'></a>\n<input type=checkbox name='$no' value=delete><span class='filetitle'>$sub</span> \n";
        $temp .= "<span class='postername'>$name</span> $now <span id='nothread$no'>";

        $stickyicon = ($sticky) ? ' <img src="' . CSS_PATH . '/sticky.gif" alt="sticky"> ' : "";

        if ($locked) $stickyicon .= ' <img src="' . CSS_PATH . '/locked.gif" alt="closed"> ';

        if (!$this->inIndex) {
            $temp .= "<a href='#$no' class='quotejs'>No.</a><a href='javascript:insert(\"$no\")' class='quotejs'>$no</a> $stickyicon &nbsp; ";
        } else {
            $temp .= "<a href='" . RES_DIR . $no . PHP_EXT . "#" . $no . "' class='quotejs'>No.</a><a href='" . RES_DIR . $no . PHP_EXT . "#q" . $no . "' class='quotejs'>$no</a> $stickyicon &nbsp; [<a href='" . RES_DIR . $no . PHP_EXT . "'>" . S_REPLY . "</a>]";
        }

        $com = $this->abbr($com, MAX_LINES_SHOWN);
        $com = $this->auto_link($com, $no);

        $temp .= "<br>";
        $temp .= "</span>\n<blockquote>$com</blockquote>";

        return $temp;
    }

    function abbr($str, $max_lines) {
        if ($this->inIndex) {
            $com = $str;

            list($com, $abbreviated) = $this->abbreviate($str, $max_lines);

            if (isset($abbreviated) && $abbreviated)
                $com .= "<br><br><span class='abbr'>Comment too long. Click <a href='" . RES_DIR . ($resto ? $resto : $no) . PHP_EXT . "#$no'>here</a> to view the full text.</span>";

            return $com;
        } else {
            return $str;
        }
    }

    function abbreviate($str, $max_lines) {
        $max_lines = (defined('MAX_LINES_SHOWN')) ? MAX_LINES_SHOWN : (defined('BR_CHECK')) ? BR_CHECK : ($max_lines) ? $max_lines : 20; //Pay no attention to the ternary.
        $lines = explode("<br />", $str); //This should probably be <br>

        if (count($lines) > $max_lines) {
            $abbr = 1;
            $lines = array_slice($lines, 0, $max_lines);
            $str = implode("<br />", $lines);
        } else {
            $abbr = 0;
        }

        //close spans after abbreviating
        //XXX will not work with more html - use abbreviate_html from shiichan
        $str .= str_repeat("</span>", substr_count($str, "<span") - substr_count($str, "</span"));

        return array(
             $str,
            $abbr
        );
    }

    function auto_link($com, $resno) {
        require_once("autolink.php");
        return auto_link($com, $resno);
    }
}

?>
