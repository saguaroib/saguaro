<?php
//TO-DO: Not this, at all. Switch to recaptcha or something. - RePod.
//Increasing difficulty for humans with no real stats for bots.
session_start();

//Configuration.
$lines = rand(1,4); //Amount of lines to have.
$padding = rand(1,8); //Initial horizontal offset.
$length = 4; //Length of captcha. Does not prevent from overflowing.

function rx() {
	return rand(0,100);
}
function ry() {
	return rand(0,30);
}


//Generate captcha and set in session.
$start = rand(0,25);
$captcha = strtoupper(substr(md5(microtime()),$start,$length));
$_SESSION['capkey'] = $captcha;

//Create image.
$image = @imagecreatetruecolor(100, 30) or die("Cannot Initialize new GD image stream");

while ($lines) {
	imagesetthickness($image, rand(1,2));
	$line_color = imagecolorallocate($image,rand(153,255),rand(153,255),rand(153,255));
	imageline($image,rx(),ry(),rx(),ry(),$line_color);
	$lines--;
}

//Write out each letter individually.
$index = 0; $x = $padding;
while ($index < strlen($captcha)) {
	$text_color = imagecolorallocate($image, rand(153,255), rand(153,255), rand(153,255));
	
	//Calculate position.
	$f = rand(3,5);
	$x = $x + imagefontwidth($f) + rand(2,6);
	$y = rand(4,(30 - imagefontheight($f) - 4));
	imagestring($image, $f, $x+1, $y+1, substr($captcha,$index,1), $text_color);
	imagestring($image, $f, $x, $y, substr($captcha,$index,1), $text_color);
	$index++;
}


//Output image.
header("Content-type: image/jpeg");
imagejpeg($image);//Output image to browser 

?>
