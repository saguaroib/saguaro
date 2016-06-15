<?php

/*

    BBCode-esque.

    Programmable, push a key pair of ["regex" => "sub"] to $processors.

    $bbcode = new BBCode();
    echo $bbcode->format("[b]test[/b]");

*/

require_once("text_process.php");

class BBCode extends TextProcessor {
    public $processors = [
        "/\[b\](.*?)\[\/b\]/Usi" => "<span style='font-weight:bold'>\\1</span>", //Bold
        "/\[u\](.*?)\[\/u\]/Usi" => "<span style='text-decoration: underline'>\\1</span>", //Underline
        "/\[i\](.*?)\[\/i\]/Usi" => "<span style='font-style:italic'>\\1</span>", //Italic
        "/\[s\](.*?)\[\/s\]/Usi" => "<span style='text-decoration: line-through'>\\1</span>", //Strikethrough.
        "/\[size=([1-9])\d*?\](.*?)\[\/size\]/Usi" => "<span style='font-size:\\1ex'>\\2</span>", //Size
        "/\[aa\](.*?)\[\/aa\]/Usi" => "<span style='font-family: Mona,\"MS PGothic\"'>\\1</span>", //ASCII Art
        "/\[youtube\](.*?)youtube.com\/watch\?v=(.*)\[\/youtube\]/Usi" => "<object width='425' height='344'><param name='movie' value='http://www.youtube.com/v/\\2&hl=de&fs=1\'</param><param name='allowFullScreen' value='true'></param><embed src='http://www.youtube.com/v/\\2&hl=de&fs=1' type='application/x-shockwave-flash' allowfullscreen='true' width='425' height='344'></embed></object>", //Youtube
        "/\[nico\](.*?)nicovideo.jp\/watch\/(.*?)\[\/nico\]/Usi" => "<script src='http://ext.nicovideo.jp/thumb_watch/\\2\' width='255' height='255'></script>", //niconico
        "/\==(.*?)\\==/Usi" => "<b><font size='4' color='#AF0A0F'>\\1</h4></font></b>", //Redtext
        "/\[spoiler\](.*?)\[\/spoiler\]/" => "<span class='spoiler'>\\1</span>" //Spoilers
    ];
}

?>