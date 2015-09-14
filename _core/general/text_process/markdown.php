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

*/

require_once("text_process.php");

class Markdown extends TextProcessor {
    public $processors = [
        "/\*\*(.*?)\*\*/Usi" => "<strong>\\1</strong>", //Bold
        "/__(.*?)__/Usi" => "<span style='text-decoration: underline'>\\1</span>", //Underline
        "/\*(.*?)\*/Usi" => "<span style='font-style:italic'>\\1</span>", //Italic
        "/~~(.*?)~~/Usi" => "<span style='text-decoration: line-through'>\\1</span>", //Strikethrough.
        "/\[yt\]\((?:(?:https?:\/\/)?(?:www\.)?youtu(?:be)?\.(?:com|be)\/(?:watch\?v=)?)?(\w+?)\)/Usi" => "<iframe width='560' height='315' src='https://www.youtube.com/embed/\\1' frameborder='0' allowfullscreen></iframe>", //YouTube
        "/\|\|(.*?)\|\|/Usi" => "<span class='spoiler'>\\1</span>" //Spoilers
    ];
}

?>