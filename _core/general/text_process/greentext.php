<?php

/*

    Efficiency.

    A lot stricter (and more accurate) than the old one due to caring about individual lines.
    However, currently does not work due to usage of "<br />" without an accompanying "\n", see tests.
    Regist would need to be changed to include these \n or run these before converting them to "<br />".

    Probably best to run after autolinker (which is currently impossible since autolinker is ran when posts are generated).

    $test = new GreenText;
    echo $test->format("test\n&gt;memearrows");       //Works because the \n splits correctly in the regex.
    echo $test->format("test<br>\n&gt;memearrows");   //Works because the \n splits correctly in the regex.
    echo $test->format("test<br />\n&gt;memearrows"); //Works because the \n splits correctly in the regex.
    echo $test->format("test<br>&gt;memearrows");     //Doesn't work because PHP doesn't treat <br> as a newline to split by.
    echo $test->format("test<br />&gt;memearrows");   //Doesn't work because PHP doesn't treat <br /> as a newline to split by.

*/

require_once("text_process.php");

class GreenText extends TextProcessor {
    public $processors = [
        "/^(?:\>|\>)(.*?)$/Umi" => "<span class='quote'>&gt;\\1</span>"
    ];
}

?>