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
        $link = "//" . SITE_ROOT_BD . "/" . RES_DIR . "";
        if ($email) $name = "<a href='mailto:$email' class='linkmail'>$name</a>";
        $temp = "<div class='thread' id='t$no'/><div class='postContainer opContainer' id='pc$no'/><div class='post op' id='p$no'/>";

        if (strpos($sub, "SPOILER<>") === 0) {
            $sub = substr($sub, strlen("SPOILER<>")); //trim out SPOILER<>
            $spoiler = true;
		}
        
        $image = new Image;
        $image->inIndex = $this->inIndex;
        $temp .= $image->format($this->data, $spoiler);

        $temp .= "<div class='postInfo desktop' id='pi{$no}'><input type='checkbox' name='$no' value='delete'><span class='subject'>$sub</span> <span class='name'>$name</span> <span class='dateTime' data-utc='$time' >$now</span>";

        if ($sticky) $stickyicon .= ' <img src="' . CSS_PATH . '/imgs/sticky.gif" alt="This thread will not move."> ';
        if ($locked) $stickyicon .= ' <img src="' . CSS_PATH . '/imgs/locked.gif" alt="Closed to replies."> ';
        if ($permasage && SHOW_PERMASAGE) $stickyicon .= ' <img src="' . CSS_PATH . '/imgs/bumplocked.gif" alt="This thread will not bump.">'; 

        if (!$this->inIndex) {
            $temp .= "<a href='{$link}{$no}#{$no}' name='$no' class='permalink' title='Permalink thread' >  No.</a><a href='javascript:insert(\"$no\")' title='Quote'>$no</a> $stickyicon </div>";
            $temp .= "<input type='hidden' name='anchor' value='$no'>";  //Anchor for in-thread deletion redirect
        } else {
            $temp .= "  <a href='{$link}{$no}#{$no}' name='$no' class='permalink' title='Permalink thread' resto='{$no}'>  No.</a><a href='{$link}{$no}#{$no}' title='Quote'>$no</a> $stickyicon [<a href='" . RES_DIR . $no . "'>" . S_REPLY . "</a>]</div>";
        }

        $com = $this->autoLink($com, $no, 0);
        $com = $this->abbr($com, MAX_LINES_SHOWN, $no, $no); //lol

        $temp .= "<blockquote class='postMessage' id='m$no' >$com</blockquote>";
        $temp .= "</div></div>";
        return $temp;
    }   
    
    function format() {
        extract($this->data);
        $link = "//" . SITE_ROOT_BD . "/" . RES_DIR . "";

        if ($email) $name = "<a href='mailto:$email' class='linkmail'>$name</a>";
        if (strpos($sub, "SPOILER<>") === 0) {
            $sub = substr($sub, strlen("SPOILER<>")); //trim out SPOILER<>
            $spoiler = true;
		}
        $temp .= "<div class='postContainer replyContainer' id='pc$no'/>";
        $temp .= "<div class='sideArrows' id='sa$no'>&gt;&gt;</div><div id='p$no' class='post reply'>";
        
        $temp .= "<div class='postInfo desktop' id='pi{$no}'><input type='checkbox' name='$no' value='delete'><span class='subject'>$sub</span> <span class='name'>$name</span> <span class='dateTime' >$now</span> ";

        if (!$this->inIndex) {
            $temp .= "<a href='{$link}{$resto}#{$no}' name='$no' class='permalink' title='Permalink thread' >  No.</a><a href='javascript:insert(\"$no\")' title='Quote'>$no</a> $stickyicon </div>";
        } else {
            $temp .= "  <a href='" . RES_DIR . $resto . "#" . $no . "' name='$no' class='permalink' title='Permalink thread' resto='{$resto}'>  No.</a><a href='{$link}{$resto}#{$no}' title='Quote'>$no</a></div>";
        }

        $image = new Image;
        $image->inIndex = $this->inIndex;
        $temp .= $image->format($this->data, $spoiler);

        $com = $this->autoLink($com, $no, $resto);
        $com = $this->abbr($com, MAX_LINES_SHOWN, $no, $resto); //yeah sure whatever

        $temp .= "<blockquote class='postMessage' id='m$no'>$com</blockquote>";

        $temp .= "</div></div>";

        return $temp;
    }

    function abbr($str, $max_lines, $no = 0, $resto = 0) {
        if ($this->inIndex) {
            $com = $str;

            list($com, $abbreviated) = $this->abbreviate($str, $max_lines);
            $num = ($resto) ? $resto : $no; //I'm probably shitting something up here
            if (isset($abbreviated) && $abbreviated)
                $com .= "<br><br><span class='abbr'>Comment too long. Click <a href='" . RES_DIR . $num . "#$no'>here</a> to view the full text.</span>";

            return $com;
        } else {
            return $str;
        }
    }
    
    function autoLink($com, $no, $resto) {
        require_once("autolinker.php");
        $com = auto_link($com, $no, $resto);
        return $com;
    }

    function abbreviate($str, $max_lines) {
        $max_lines = (defined('MAX_LINES_SHOWN')) ? MAX_LINES_SHOWN : (defined('MAX_LINES')) ? MAX_LINES : ($max_lines) ? $max_lines : 20; //Pay no attention to the ternary.
        $lines = explode("<br>", $str); //This should probably be <br>

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
}

?>
