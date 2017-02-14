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

        $posterID = (DISP_ID) ? "<span class='posteruid id_{$id}'>(ID: <span class='hand' title='Highlight posts by this ID'>{$id}</span>)</span> " : '';
        $trip = (($tripcode != '' && ALLOW_TRIP) || true) ? "<span class='postertrip'>{$tripcode}</span> " : '';

        $name = ($capcode) ? $this->capcode($capcode, $name, $tripcode) : "<span class='name'>$name{$trip}</span>";

        $temp .= "<div class='postInfo desktop' id='pi{$no}'><input type='checkbox' name='$no' value='delete'><span class='subject'>$sub</span> <span class='nameBlock'>{$name} {$posterID}</span><span class='dateTime' data-utc='{$time}'>{$now}</span>";

        if ($sticky) $stickyicon .= ' <img src="' . CSS_PATH . '/imgs/sticky.gif" alt="Sticky"> ';
        if ($closed) $stickyicon .= ' <img src="' . CSS_PATH . '/imgs/locked.gif" alt="Closed to replies"> ';
        if ($permasage && SHOW_PERMASAGE) $stickyicon .= ' <img src="' . CSS_PATH . '/imgs/bumplocked.gif" alt="This thread will not bump.">'; 

        if (!$this->inIndex) {
            $temp .= "<a href='#p{$no}' class='permalink' title='Link to this thread' >  No.</a><a href='javascript:insert(\"$no\")' title='Quote'>$no</a> $stickyicon </div>";
            $temp .= "<input type='hidden' name='anchor' value='$no'>";  //Anchor for in-thread deletion redirect
        } else {
            $temp .= "  <a href='{$link}{$no}" . PHP_EXT . "#{$no}' name='$no' class='permalink' title='Link to this thread' resto='{$no}'>  No.</a><a href='{$link}{$no}" . PHP_EXT . "#{$no}' title='Quote'>$no</a> $stickyicon [<a href='" . RES_DIR  . $no . PHP_EXT . "'>" . S_REPLY . "</a>]</div>";
        }

        $com = $this->auto_link($com, $no, 0);
        $com = $this->abbr($com, MAX_LINES_SHOWN, $no, $no); //lol

        $image = new Image;
        $image->inIndex = $this->inIndex;

        if ($media != null) {
            $files = json_decode($media, true);
            $xls = (count($files) > 1) ? "file multi-op" : "file no-multi";
            $temp .= "<div class='{$xls}'  id='f{$no}'>";
            foreach ($files as $file) {
                $temp .= $image->format($no, 0, $file);
            }
            $temp .= "</div>";
        }        
        
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
        $posterID = (DISP_ID) ? " <span class='posteruid id_{$id}'>(ID: <span class='hand' title='Highlight posts by this ID'>{$id}</span>)</span> " : '';
        $trip = (($tripcode != '' && ALLOW_TRIP) || true) ? "<span class='postertrip'>{$tripcode}</span> " : '';

        $temp .= "<div class='postContainer replyContainer' id='pc$no'/>";
        $temp .= "<div class='sideArrows' id='sa$no'>&gt;&gt;</div><div id='p$no' class='post reply'>";

        $name = ($capcode) ? $this->capcode($capcode, $name, $tripcode) : "<span class='name'>$name{$trip}</span>";

        $temp .= "<div class='postInfo desktop' id='pi{$no}'><input type='checkbox' name='$no' value='delete'><span class='subject'>$sub</span> <span class='nameBlock'>{$name} {$posterID}</span><span class='dateTime' data-utc='{$time}'>{$now}</span>";

        if (!$this->inIndex) {
            $temp .= "<a href='#p{$no}' class='permalink' title='Link to this thread' >  No.</a><a href='javascript:insert(\"$no\")' title='Quote'>$no</a> $stickyicon </div>";
        } else {
            $temp .= "  <a href='" . RES_DIR . $resto . PHP_EXT . "#" . $no . "' name='$no' class='permalink' title='Link to this thread' resto='{$resto}'>  No.</a><a href='{$link}{$resto}" . PHP_EXT . "#{$no}' title='Quote'>$no</a></div>";
        }

        $image = new Image;
        $image->inIndex = $this->inIndex;

        if ($media != null) {
            $files = json_decode($media, true);
            $xls = (count($files) > 1) ? "file multi-reply" : "file no-multi-reply";
            $temp .= "<div class='{$xls}'  id='f{$no}'>";
            foreach ($files as $file) {
                $temp .= $image->format($no, $resto, $file);
            }
            $temp .= "</div>";
        }
        
        $com = $this->abbr($com, MAX_LINES_SHOWN, $no, $resto); //yeah sure whatever
        $com = $this->auto_link($com, $resno);

        $temp .= "<blockquote class='postMessage' id='m$no'>$com</blockquote>";

        $temp .= "</div></div>";

        return $temp;
    }

    function abbr($str, $max_lines, $no = 0, $resto = 0) {
        if ($this->inIndex) {
            $com = $str;

            list($com, $abbreviated) = $this->abbreviate($str, $max_lines);
            $num = ($resto) ? $resto : $no; //I'm probably shitting something up here, i don't know
            if (isset($abbreviated) && $abbreviated)
                $com .= "<br><br><span class='abbr'>Comment too long. Click <a href='" . RES_DIR . $num . PHP_EXT . "#$no'>here</a> to view the full text.</span>";

            return $com;
        } else {
            return $str;
        }
    }

    function abbreviate($str, $max_lines) {
        $max_lines = (defined('MAX_LINES_SHOWN')) ? MAX_LINES_SHOWN : (defined('MAX_LINES')) ? MAX_LINES : ($max_lines) ? $max_lines : 20; //Pay no attention to the ternary.
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
        require_once(CORE_DIR . "/general/text_process/autolink.php");
        $link = new AutoLink;
        return $link->format($com);
    }

    public function capcode($capcode, $name, $tripcode) {
        switch ($capcode) {
            case 'admin':
                $name = "<span class='name cap admin' title='This user is an Administrator'>$name <span class='postertrip'>{$tripcode}</span> ## Admin</span>";
                break;
            case 'moderator':
                $name = "<span class='name cap moderator' title='This user is a Moderator'>$name <span class='postertrip'>{$tripcode}</span> ## Mod</span>";
                break;
            case 'janitor':
                $name = "<span class='name cap jani' title='This user is a Janitor'>$name <span class='postertrip'>{$tripcode}</span> ## Janitor</span>";
                break;
            case 'manager':
                $name = "<span class='name cap manager' title='This user is a Manager'>$name <span class='postertrip'>{$tripcode}</span> ## Manager</span>";
                break;
            case 'developer':
                $name = "<span class='name cap developer' title='This user is a Developer'>$name <span class='postertrip'>{$tripcode}</span> ## Developer</span>";
                break;
            default:
                $name = "<span class='name'  title='This user is a MYSTERY'>$name <span class='postertrip'>{$tripcode} ## Mystery</span></span>";
                break;
        }
        
        return $name;
    }
}