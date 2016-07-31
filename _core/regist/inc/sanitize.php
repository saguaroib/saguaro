<?php

class Sanitize {
    function process($name, $com, $sub, $email, $resto, $url, $dest, $moderator = 0) {
        $email = $this->CleanStr($email, 0); //Don't allow moderators to fuck with this
        $email = preg_replace("[\r\n]", "", $email);
        $sub   = $this->CleanStr($sub, 0); //Or this
        $sub   = preg_replace("[\r\n]", "", $sub);
        $url   = $this->CleanStr($url, 0); //Or this
        $url   = preg_replace("[\r\n]", "", $url);
        $resto = $this->CleanStr($resto, 0); //Or this
        $resto = preg_replace("[\r\n]", "", $resto);
        $com   = $this->CleanStr($com, $moderator); //But they can with this.

        if (!$name || preg_match("/^[ |&#12288;|]*$/", $name))
            $name = "";
        if (!$com || preg_match("/^[ |&#12288;|\t]*$/", $com))
            $com = "";
        if (!$sub || preg_match("/^[ |&#12288;|]*$/", $sub))
            $sub = "";

        $name = str_replace(S_MANAGEMENT, '"' . S_MANAGEMENT . '"', $name);
        $name = str_replace(S_DELETION, '"' . S_DELETION . '"', $name);

        if (strlen($com) > S_POSTLENGTH)
            return error(S_TOOLONG, $dest);
        if (strlen($name) > 100)
            return error(S_TOOLONG, $dest);
        if (strlen($email) > 100)
            return error(S_TOOLONG, $dest);
        if (strlen($sub) > 100)
            return error(S_TOOLONG, $dest);
        if (strlen($resto) > 10)
            return error(S_UNUSUAL, $dest);
        if (strlen($url) > 10)
            return error(S_UNUSUAL, $dest);

        // Standardize new character lines
        $com = str_replace("\r\n", "\n", $com);
        $com = str_replace("\r", "\n", $com);

        // Continuous lines
        $com = preg_replace("/\n((&#12288;|)*\n){3,}/", "\n", $com);

        if (!$moderator && substr_count($com, "\n") > MAX_LINES)
            return error("Error: Too many lines.", $dest);

        $com = nl2br($com); //br is substituted before newline char
        $com = str_replace("\n", "", $com); //\n is erased

        $name  = preg_replace("[\r\n]", "", $name);
        $com   = $this->wordwrap2($com, 100, "<br />");

        return [
        'name' => $name,
        'comment' => $com,
        'subject' => $sub,
        'email' => $email
        ];
    }

    /* text plastic surgery */
    function CleanStr($str, $moderator = 0) {
        $str = trim($str); //blankspace removal
        if (get_magic_quotes_gpc()) { //magic quotes is deleted (?)
            $str = stripslashes($str);
        }
        if (!$moderator) { //If not moderator+, disable html tags
            $str = htmlspecialchars($str); //remove html special chars
            $str = str_replace("&amp;", "&", $str); //remove ampersands
            $str = str_replace("&gt;", ">", $str); //For backlinking
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

?>
