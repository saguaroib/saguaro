<?php

/*

    Tripcode class.
    Not capcodes!

    $trip = new Tripcode;
    echo $trip->format("Ayelm&ao#xd");
*/

class Tripcode {
    function format($input) {
        //Convert the character encoding to Code page 932/ANSI/Windows-31J #&#65355;&#65345;&#65357;&#65353;
        $input = iconv("UTF-8", "CP932//IGNORE", $input);
        $input = preg_replace("/\#+$/", "", $input); //Remove all trailing #.

        list($name, $trip, $secure) = explode("#",$input,3);

        if ($secure || $trip)
            $trip = ($secure) ? Tripcode::secure($secure) : Tripcode::normal($trip);

        return trim("$name $trip");
    }

    private function normal($trip) {
        $salt = strtr(preg_replace("/[^\.-z]/", ".", substr($trip . "H.", 1, 2)), ":;<=>?@[\\]^_`", "ABCDEFGabcdef");
        return "!" . substr(crypt($trip, $salt), -10);
    }

    private function secure($trip) {
        $salt = "";

        if (file_exists(SALTFILE)) {
            $salt = file_get_contents(SALTFILE);
        } else {
            //Get a random salt from the SaguaroCrypt class, which doesn't use system().
            require_once(CORE_DIR . "/crypt/legacy.php");
            $crypto = new SaguaroCryptLegacy;
            $depth = 1; //Iterations of salts to append.

            for ($i = 0; $i < $depth; $i++) {
                $salt .= $crypto->openssl_salt(512);
            }

            //Write out the salt to SALTFILE.
            $file = fopen(SALTFILE, 'a');
            fwrite($file, $salt);
            fclose($file);
            chmod(SALTFILE, 0400);
        }

        $sha = base64_encode(pack("H*", sha1($trip . $salt)));
        $sha = substr($sha, 0, 11);

        return "!!$sha";
    }

    private function fortune() {
        if (1 /*FORTUNE_TRIP*/) {
            require_once("fortune.php");
            $fortune = new Fortune;

            return $fortune->giveFortune();
        }

        return "";
    }
}

?>