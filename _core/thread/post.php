<?php
/*

    Formats a post based on the input given.

    Shouldn't be used without a parent (thread.php).

*/

include("image.php");

class Post {
    public $data = [];
    public $inIndex = false; //Until I feel like extending.

    function formatOP() {
        @extract($this->data);
        $temp = "<div class='thread' id='t$no'/><div class='post op' id='p$no'/><div class='postContainer opContainer' id='pc$no'/>";

        $image = new Image;
        $image->inIndex = $this->inIndex;
        $temp .= $image->format($this->data);
        
        $temp .= "<div class='postInfoM mobile' id='pim$no'><span class='subject'>$sub</span> <span class='name'>$name</span> <span class='dateTime'>$now  <a href='#$no' class='quotejs'>No.</a><a href='javascript:insert(\">>$no\")' class='quotejs'>$no</a></span></div>";
        $temp .= "<div class='postInfo desktop'><input type='checkbox' name='$no' value='delete'><span class='subject'>$sub</span> <span class='name'>$name</span> <span class='dateTime'>$now</span>";

        $stickyicon = ($sticky) ? ' <img src="' . CSS_PATH . '/imgs/sticky.gif" alt="sticky"> ' : "";

        if ($locked) $stickyicon .= ' <img src="' . CSS_PATH . '/imgs/locked.gif" alt="closed"> ';

        if (!$this->inIndex) {
            $temp .= "<a href='#$no' class='quotejs' >No.</a><a href='javascript:insert(\">>$no\")' class='quotejs'>$no</a> $stickyicon </div>";
        } else {
            $temp .= "  <a href='" . RES_DIR . $no . PHP_EXT . "#" . $no . "' class='quotejs'>No.</a><a href='" . RES_DIR . $no . PHP_EXT . "#" . $no . "' class='quotejs'>$no</a> $stickyicon [<a href='" . RES_DIR . $no . PHP_EXT . "'>" . S_REPLY . "</a>]</div>";
            $temp .= "<div class='postLink mobile' > $stickyicon &nbsp; <a href='" . RES_DIR . $no . PHP_EXT . "'>" . S_REPLY . "</a></div>";
        
        }

        $com = $this->abbr($com, MAX_LINES_SHOWN);
        $com = $this->auto_link($com, $no);

        //$temp .= "<br>";
        $temp .= ($com == "") ? "<blockquote style='display:none;' class='postMessage' id='m$no' >$com</blockquote>" : "<blockquote class='postMessage' id='m$no' >$com</blockquote>";
        $temp .= "</div></div>";
        return $temp;
    }   
    
    function format() {
        extract($this->data);

        if ($email) $name = "<a href='mailto:$email' class='linkmail'>$name</a>";
        if (strpos($sub, "SPOILER<>") === 0) {
            $sub = substr($sub, strlen("SPOILER<>")); //trim out SPOILER<>
            $spoiler = 1;
        } else {
            $spoiler = 0;
        }
        $temp .= "<div class='postContainer replyContainer' id='pc$no'/>";
        $temp .= "<div class='sideArrows' id='sa$no'>&gt;&gt;</div><div id='$no' class='post reply'>";
        
        $temp .= "<div class='postInfoM mobile' id='pim$no'><span class='subject'>$sub</span> <span class='name'>$name</span> <span class='dateTime'>$now  <a href='#$no' class='quotejs'>No.</a><a href='javascript:insert(\">>$no\")' class='quotejs'>$no</a><span></div>";
        $temp .= "<div class='postInfo desktop'><input type='checkbox' name='$no' value='delete'><span class='subject'>$sub</span> <span class='name'>$name</span> <span class='dateTime'>$now</span> ";

        if (!$this->inIndex) {
            $temp .= "<a href='#$no' class='quotejs'>No.</a><a href='javascript:insert(\">>$no\")' class='quotejs'>$no</a></span></div>";
        } else {
            $temp .= "<a href='" . RES_DIR . $resto . PHP_EXT . "#$no' class='quotejs'>No.</a><a href='" . RES_DIR . $resto . PHP_EXT . "#$no' class='quotejs'>$no</a></div>";
        }

        $image = new Image;
        $image->inIndex = $this->inIndex;
        $temp .= $image->format($this->data);

        $com = $this->abbr($com, MAX_LINES_SHOWN);
        $com = $this->auto_link($com, $resno);

        $temp .= "<blockquote class='postMessage' id='m$no'>$com</blockquote>";

        $temp .= "</div></div>";

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
