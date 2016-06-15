<?php

class Sanitize {

    
    function CleanStr($str, $admin = 0, $skip_bidi = 0) {
        /* text plastic surgery */
        // you can call with skip_bidi=1 if cleaning a paragraph element (like $com)
        $str = trim($str); //blankspace removal
        if (get_magic_quotes_gpc()) { //magic quotes is deleted (?)
            $str = stripslashes($str);
        }
        $str =  iconv("utf-8", "utf-8//ignore", $str);
        if (!$admin) $str = htmlspecialchars($str);

        if ($skip_bidi == 0) {
            // fix malformed bidirectional overrides - insert as many PDFs as RLOs
            //RLO
            $str .= str_repeat("\xE2\x80\xAC", substr_count($str, "\xE2\x80\xAE" /* U+202E */ ));
            $str .= str_repeat("&#8236;", substr_count($str, "&#8238;"));
            $str .= str_repeat("&#x202c;", substr_count($str, "&#x202e;"));
            //RLE
            $str .= str_repeat("\xE2\x80\xAC", substr_count($str, "\xE2\x80\xAB" /* U+202B */ ));
            $str .= str_repeat("&#8236;", substr_count($str, "&#8235;"));
            $str .= str_repeat("&#x202c;", substr_count($str, "&#x202b;"));
        }
        return str_replace(",", "&#44;", $str); //remove commas
    }
    
   
    function wordwrap2($str, $cols, $cut) { // word-wrap without touching things inside of tags
        if (strlen($str) < $cols || !preg_match('/[^ <>]{' . $cols . '}/', $str)) { // if there's no runs of $cols non-space characters, wordwrap is a no-op
            return $str;
        }
        $sections = preg_split('/[<>]/', $str);
        $str      = '';
        for ($i = 0; $i < count($sections); $i++) {
            if ($i % 2) { // inside a tag
                $str .= '<' . $sections[$i] . '>';
            } else { // outside a tag
                $words = explode(' ', $sections[$i]);
                foreach ($words as &$word) {
                    $word  = wordwrap($word, $cols, $cut, 1);
                    // fix utf-8 sequences (XXX: is this slower than mbstring?)
                    $lines = explode($cut, $word);
                    for ($j = 1; $j < count($lines); $j++) { // all lines except the first
                        while (1) {
                            $chr = substr($lines[$j], 0, 1);
                            if ((ord($chr) & 0xC0) == 0x80) { // if chr is a UTF-8 continuation...
                                $lines[$j - 1] .= $chr; // put it on the end of the previous line
                                $lines[$j] = substr($lines[$j], 1); // take it off the current line
                                continue;
                            }
                            break; // chr was a beginning utf-8 character
                        }
                    }
                    $word = implode($cut, $lines);
                    
                }
                $str .= implode(' ', $words);
            }
        }
        return $str;
    }
    
    public function spoiler_parse($com) {
        if (!$this->find_match_and_prefix("/\[spoiler\]/", $com, 0, $m))
            return $com;
        
        $bl  = strlen("[spoiler]");
        $el  = $bl + 1;
        $st  = '<span class="spoiler" onmouseover="this.style.color=\'#FFF\';" onmouseout="this.style.color=this.style.backgroundColor=\'#000\'" style="color:#000;background:#000">';
        $et  = '</span>';
        $ret = $m[0] . $st;
        $lev = 1;
        $off = strlen($m[0]) + $bl;
        
        while (1) {
            if (!$this->find_match_and_prefix("@\[/?spoiler\]@", $com, $off, $m))
                break;
            list($txt, $tag) = $m;
            
            $ret .= $txt;
            $off += strlen($txt) + strlen($tag);
            
            if ($tag == "[spoiler]") {
                $ret .= $st;
                $lev++;
            } else if ($lev) {
                $ret .= $et;
                $lev--;
            }
        }
        
        $ret .= substr($com, $off, strlen($com) - $off);
        $ret .= str_repeat($et, $lev);
        
        return $ret;
    }
    
    private function find_match_and_prefix($regex, $str, $off, &$match) {
        if (!preg_match($regex, $str, $m, PREG_OFFSET_CAPTURE, $off))
            return false;
        
        $moff  = $m[0][1];
        $match = array(
            substr($str, $off, $moff - $off),
            $m[0][0]
        );
        
        return true;
    }
}