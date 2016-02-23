<?php

/*

    The great reright. It's not write now.
    
*/

//start new tripcode crap
$names = iconv("UTF-8", "CP932//IGNORE", $name); // convert to Windows Japanese #&#65355;&#65345;&#65357;&#65353;

list($name) = explode("#", $name);
$name = $sanitize->CleanStr($name);

if (preg_match("/\#+$/", $names)) {
    $names = preg_replace("/\#+$/", "", $names);
}
if (preg_match("/\#/", $names)) {
    $names = str_replace("&#", "&&", htmlspecialchars($names)); // otherwise HTML numeric entities screw up explode()!
    list($nametemp, $trip, $sectrip) = str_replace("&&", "&#", explode("#", $names, 3));
    $names = $nametemp;
    $name .= "</span>";
    
    if ($trip != "") {
        if (FORTUNE_TRIP == 1 && $trip == "fortune") {
            require_once("fortune.php");
            $fortune = new Fortune; $fortune = $fortune->giveFortune();
            $com        =  $com ."<br /><br /><font color=$fortcol><b>Your fortune: " . $fortune . "</b></font>";
            $trip       = "";
            if ($sectrip == "") {
                if ($name == "</span>" && $sectrip == "")
                    $name = S_ANONAME;
                else
                    $name = str_replace("</span>", "", $name);
            }
        } else if ($trip == "fortune") {
            //remove fortune even if FORTUNE_TRIP is off
            $trip = "";
            if ($sectrip == "") {
                if ($name == "</span>" && $sectrip == "")
                    $name = S_ANONAME;
                else
                    $name = str_replace("</span>", "", $name);
            }
            
        } else {
            
            $salt = strtr(preg_replace("/[^\.-z]/", ".", substr($trip . "H.", 1, 2)), ":;<=>?@[\\]^_`", "ABCDEFGabcdef");
            $trip = substr(crypt($trip, $salt), -10);
            $name .= " <span class='name postertrip'>!" . $trip;
        }
    }
    
    
    if ($sectrip != "") {
        $salt = "LOLLOLOLOLOLOLOLOLOLOLOLOLOLOLOL"; //this is ONLY used if the host doesn't have openssl
        //I don't know a better way to get random data
        if (file_exists(SALTFILE)) { //already generated a key
            $salt = file_get_contents(SALTFILE);
        } else {
            system("openssl rand 448 > '" . SALTFILE . "'", $err);
            if ($err === 0) {
                chmod(SALTFILE, 0400);
                $salt = file_get_contents(SALTFILE);
            }
        }
        $sha = base64_encode(pack("H*", sha1($sectrip . $salt)));
        $sha = substr($sha, 0, 11);
        if ($trip == "")
            $name .= " <span class='name postertrip'>";
        $name .= "!!" . $sha;
    }
}

?>