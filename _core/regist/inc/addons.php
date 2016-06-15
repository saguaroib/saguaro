<?php

list($name) = explode("#", $name);
$name = $sanitize->CleanStr($name);

if (preg_match("/\#+$/", $names)) {
    $names = preg_replace("/\#+$/", "", $names);
}
if (preg_match("/\#/", $names)) {
    
    $names = str_replace("&#", "&&", htmlspecialchars($names)); # otherwise HTML numeric entities screw up explode()!
    list($nametemp, $trip, $sectrip) = str_replace("&&", "&#", explode("#", $names, 3));
    $names = $nametemp;
    $name .= "</span>";
    
    if ($trip != "") {
        if (FORTUNE_TRIP && $trip == "fortune") {
            $fortunes   = array(
                "Bad Luck",
                "Average Luck",
                "Good Luck",
                "Excellent Luck",
                "Reply hazy, try again",
                "Godly Luck",
                "Very Bad Luck",
                "Outlook good",
                "Better not tell you now",
                "You will meet a dark handsome stranger",
                "&#65399;&#65408;&#9473;&#9473;&#9473;&#9473;&#9473;&#9473;(&#65439;&#8704;&#65439;)&#9473;&#9473;&#9473;&#9473;&#9473;&#9473; !!!!",
                "&#65288;&#12288;´_&#12445;`&#65289;&#65420;&#65392;&#65437; ",
                "Good news will come to you by mail"
            );
            $fortunenum = rand(0, sizeof($fortunes) - 1);
            $fortcol    = "#" . sprintf("%02x%02x%02x", 127 + 127 * sin(2 * M_PI * $fortunenum / sizeof($fortunes)), 127 + 127 * sin(2 * M_PI * $fortunenum / sizeof($fortunes) + 2 / 3 * M_PI), 127 + 127 * sin(2 * M_PI * $fortunenum / sizeof($fortunes) + 4 / 3 * M_PI));
            $com        = "<font color=$fortcol><b>Your fortune: " . $fortunes[$fortunenum] . "</b></font><br /><br />" . $com;
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
            $name .= " <span class=\"postertrip\">!" . $trip;
        }
    }
    
    if ($sectrip != "") {
        $salt = "LOLLOLOLOLOLOLOLOLOLOLOLOLOLOLOL"; #this is ONLY used if the host doesn't have openssl
        #I don't know a better way to get random data
        if (file_exists(SALTFILE)) { #already generated a key
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
            $name .= " <span class=\"postertrip\">";
        $name .= "!!" . $sha;
    }
}

if ($admin && isset($_POST['showCap'])) {
    $name = strip_tags($name);
    if (valid("admin")):
        $name = '<span class="cap admin" >' . $name . ' ## Admin </span>';
    elseif (valid("manager")):
        $name = '<span class="cap manager" >' . $name . ' ## Manager  </span>';
    elseif (valid("moderator")):
        $name = '<span class="cap moderator" >' . $name . ' ## Mod </span>';
    else:
        if (JANITOR_CAPCODES && valid("janitor"))
            $name = "<span class='cap jani'>" . $name . " ## Janitor</span>";
    endif;
}
    
if (DICE_ROLL) {
    if ($email) {
        if (preg_match("/dice[ +](\\d+)[ d+](\\d+)(([ +-]+?)(-?\\d+))?/", $email, $match)) {
            $dicetxt     = "rolled ";
            $dicenum     = min(25, $match[1]);
            $diceside    = $match[2];
            $diceaddexpr = $match[3];
            $dicesign    = $match[4];
            $diceadd     = intval($match[5]);
            
            for ($i = 0; $i < $dicenum; $i++) {
                $dicerand = mt_rand(1, $diceside);
                if ($i)
                    $dicetxt .= ", ";
                $dicetxt .= $dicerand;
                $dicesum += $dicerand;
            }
            
            if ($diceaddexpr) {
                if (strpos($dicesign, "-") > 0)
                    $diceadd *= -1;
                $dicetxt .= ($diceadd >= 0 ? " + " : " - ") . abs($diceadd);
                $dicesum += $diceadd;
            }
            
            $dicetxt .= " = $dicesum<br /><br />";
            $com = "<b>$dicetxt</b>" . $com;
        }
    }
}
$emails = $email;