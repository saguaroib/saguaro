<?
    $com = preg_replace("/\[b\](.*)\[\/b\]/Usi", "<b>\\1</b>", $com);
    $com = preg_replace("/\[u\](.*)\[\/u\]/Usi", "<u>\\1</u>", $com);
    $com = preg_replace("/\[i\](.*)\[\/i\]/Usi", "<i>\\1</i>", $com);
    $com = preg_replace("/\[spoiler\](.*)\[\/spoiler\]/Usi", "<span title=\"spoiler\" style=\"color: #000000; background-color: #000000;\" class=\"spoiler\" onmouseover=\"this.style.color='#FFFFFF';\" onmouseout=\"this.style.color=this.style.backgroundColor='#000000'\">\\1</span>", $com);
    $com = preg_replace("/\[color=(\#[0-9A-F]{6}|[a-z]+)\](.*)\[\/color\]/Usi", "<span style=\"color:\\1\">\\2</span>", $com);      
	$com = preg_replace("/\[youtube\](.*)youtube.com\/watch\?v=(.*)\[\/youtube\]/Usi", "<object width=\"425\" height=\"344\"><param name=\"movie\" value=\"http://www.youtube.com/v/\\2&hl=de&fs=1\"></param><param name=\"allowFullScreen\" value=\"true\"></param><embed src=\"http://www.youtube.com/v/\\2&hl=de&fs=1\" type=\"application/x-shockwave-flash\" allowfullscreen=\"true\" width=\"425\" height=\"344\"></embed></object>", $com);
   	$com = preg_replace("/\[s\](.*)\[\/s\]/Usi", "<span style=\"text-decoration: line-through\">\\1</span>", $com);
    $com = preg_replace("/\[size=(.*)\](.*)\[\/size\]/Usi", "<span style=\"font-size:\\1ex\">\\2</span>", $com);
    $com = preg_replace("/\[quote](.*)\[\/quote\]/Usi", "<div>Quote:</div><div style=\"border:solid 1px;\">\\1</div>", $com);
	//new!!
	$com = preg_replace("/\[aa\](.*)\[\/aa\]/Usi", "<span style=\"font-family: Mona,'MS PGothic'\">\\1</span>", $com);
	$com = preg_replace("/\[nico\](.*)nicovideo.jp\/watch\/(.*)\[\/nico\]/Usi", "<script src=\"http://ext.nicovideo.jp/thumb_watch/\\2\" width=\"255\" height=\"255\"></script>", $com);

	
?>