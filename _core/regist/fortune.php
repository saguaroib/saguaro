<?php

/*

    Possibly integrate fortune-mod if the environment supports it.

*/

class Fortune {
    public $fortunes = [
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
        "Good news will come to you by mail",
        "Hope you're insured",
        "Great things await",
        "Don't leave the house today."
    ];

    private function shuffle() {
        //Cleaner shuffle but lacks reusability.
        shuffle($this->fortunes);

        return $this->fortunes[0];
    }

    private function format() {
        //My eyes.
        $size = sizeof($this->fortunes);
        $fnum = rand(0, $size - 1);
        $fortune = $this->fortunes[$fnum];
        $color = "#" . sprintf("%02x%02x%02x",
                127 + 127 * sin(2 * M_PI * $fnum / $size),
                127 + 127 * sin(2 * M_PI * $fnum / $size + 2 / 3 * M_PI),
                127 + 127 * sin(2 * M_PI * $fnum / $size) + 4 / 3 * M_PI));

        $out = "<span class='fortune' style='color:$color; font-weight:bold;'>
                    $fortune
                </span>";

        return $out;
    }

    function giveFortune(/*$mod*/) {
        //Integrate shell fortune/fortune-mod?

        return $this->format();
    }
}

?>
