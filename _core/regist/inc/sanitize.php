<?php

class Sanitize {
    function process($post, $moderator = 0) {
        $post['name'] = $this->CleanStr($post['name'], 0);
        $post['name'] = preg_replace("[\r\n]", "", $post['name']);
        $post['email'] = $this->CleanStr($post['email'], 0);
        $post['email'] = preg_replace("[\r\n]", "", $post['email']);
        $post['subject']   = $this->CleanStr($post['subject'], 0);
        $post['subject']   = preg_replace("[\r\n]", "", $post['subject']);
        /*$url   = $this->CleanStr($url, 0); //Or this
        $url   = preg_replace("[\r\n]", "", $url);*/
        $post['parent'] = $this->CleanStr($post['parent'], 0);
        $post['parent'] = preg_replace("[\r\n]", "", $post['parent']);
        $post['comment']   = $this->CleanStr($post['comment'], 1);

        if (!$post['name'] || preg_match("/^[ |&#12288;|]*$/", $post['name']))
            $post['name'] = "";
        if (!$post['comment'] || preg_match("/^[ |&#12288;|\t]*$/", $post['comment']))
            $post['comment'] = "";
        if (!$post['subject'] || preg_match("/^[ |&#12288;|]*$/", $post['subject']))
            $post['subject'] = "";

        $post['name'] = str_replace(S_MANAGEMENT, '"' . S_MANAGEMENT . '"', $post['name']);
        $post['name'] = str_replace(S_DELETION, '"' . S_DELETION . '"', $post['name']);

        if (strlen($post['comment']) > S_POSTLENGTH)    return error(S_TOOLONG);
        if (strlen($post['name']) > 100)                return error(S_TOOLONG);
        if (strlen($post['email']) > 100)               return error(S_TOOLONG);
        if (strlen($post['subject']) > 100)             return error(S_TOOLONG);
        if (strlen($post['parent']) > 10)               return error(S_UNUSUAL);
        //if (strlen($url) > 10)                        return error(S_UNUSUAL);

        // Standardize new character lines
        $post['comment'] = str_replace("\r\n", "\n", $post['comment']);
        $post['comment'] = str_replace("\r", "\n", $post['comment']);

        // Continuous lines
        $post['comment'] = preg_replace("/\n((&#12288;|)*\n){3,}/", "\n", $post['comment']);

        if (!$moderator && substr_count($post['comment'], "\n") > MAX_LINES)
            return error("Error: Too many lines.", $dest);

        $post['comment'] = nl2br($post['comment']); //br is substituted before newline char
        $post['comment'] = str_replace("\n", "", $post['comment']); //\n is erased

        $post['name']  = preg_replace("[\r\n]", "", $post['name']);
        $post['comment']   = $this->wordwrap2($post['comment'], 100, "<br />");

        return [
        'name' => $post['name'],
        'comment' => $post['comment'],
        'subject' => $post['subject'],
        'email' => $post['email']
        ];
    }

    function CleanStr($str, $skip_bidi = 0) {
        /* text plastic surgery */
        // you can call with skip_bidi=1 if cleaning a paragraph element (like $com)
        $str = trim($str); //blankspace removal
        if (get_magic_quotes_gpc()) { //magic quotes is deleted (?)
            $str = stripslashes($str);
        }
        $str =  iconv("utf-8", "utf-8//ignore", $str);
        $str = htmlspecialchars($str);

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

    // word-wrap without touching things inside of tags
    function wordwrap2($str, $cols, $cut) {
        // if there's no runs of $cols non-space characters, wordwrap is a no-op
        if (strlen($str) < $cols || !preg_match('/[^ <>]{' . $cols . '}/', $str)) {
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
}