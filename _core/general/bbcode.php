<?php

/*

    BBCode-esque.

    Programmable, push a key pair of ["regex" => "sub"] to $processors.

    $bbcode = new BBCode();
    echo $bbcode->format("[b]test[/b]");

*/

class BBCode {
    public $processors = [
        "/\[b\](.*)\[\/b\]/Usi" => "<span style='font-weight:bold'>\\1</span>", //Bold
        "/\[u\](.*)\[\/u\]/Usi" => "<span style='text-decoration: underline>\\1</span>", //Underline
        "/\[i\](.*)\[\/i\]/Usi" => "<span style='font-style:italic'>\\1</span>", //Italic
        "/\[spoiler\](.*)\[\/spoiler\]/Usi" => "<span class='spoiler'>\\1</span>", //Spoiler
        "/\[color=(\#[0-9A-F]{6}|[a-z]+)\](.*)\[\/color\]/Usi" => "<span style='color:\\1\'>\\2</span>", //Color
        "/\[s\](.*)\[\/s\]/Usi" => "<span style='text-decoration: line-through'>\\1</span>", //Strikethrough.
        "/\[size=(.*)\](.*)\[\/size\]/Usi" => "<span style='font-size:\\1ex'>\\2</span>", //Size
        "/\[aa\](.*)\[\/aa\]/Usi" => "<span style='font-family: Mona,'MS PGothic'>\\1</span>", //ASCII Art
        "/\[youtube\](.*)youtube.com\/watch\?v=(.*)\[\/youtube\]/Usi" => "<object width='425' height='344'><param name='movie' value='http://www.youtube.com/v/\\2&hl=de&fs=1\"></param><param name=\"allowFullScreen\" value=\"true\"></param><embed src='http://www.youtube.com/v/\\2&hl=de&fs=1' type='application/x-shockwave-flash' allowfullscreen='true' width='425' height='344'></embed></object>", //Youtube
        "/\[nico\](.*)nicovideo.jp\/watch\/(.*)\[\/nico\]/Usi" => "<script src='http://ext.nicovideo.jp/thumb_watch/\\2\' width='255' height='255'></script>" //niconico
    ];

    private function process($input) {
        $out = $input;

        foreach ($this->processors as $regex => $resub) {
            $out = preg_replace($regex, $resub, $out);
        }

        return $out;
    }

    function format($input) {
        return $this->process($input);
    }
}

?>