<?php

/*

    Markdown, superior to BBCode.

    Programmable, push a key pair of ["regex" => "sub"] to $processors.

    $markdown = new Markdown();
    echo $markdown->format("**test**");
    
    **bold**
    *italics*
    __underline__
    ~~strikethrough~~
    [yt](videoid), [yt](http://youtube.com/watch?v=videoid), [yt](https://youtu.be/videoid)
    %%spoiler%%

*/

require_once("text_process.php");

class Markdown extends TextProcessor {
    public $processors = [
        "/\*\*(.*?)\*\*/Usi" => "<strong>\\1</strong>", //Bold
        "/__(.*?)__/Usi" => "<span style='text-decoration: underline'>\\1</span>", //Underline
        "/\*(.*?)\*/Usi" => "<span style='font-style:italic'>\\1</span>", //Italic
        "/~~(.*?)~~/Usi" => "<span style='text-decoration: line-through'>\\1</span>", //Strikethrough.
        "/%%(.*?)%%/Usi" => "<span class='spoiler'>\\1</span>" //Spoilers
    ];
}

?>