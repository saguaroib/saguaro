<?php

/*

    CAPTCHA.

    If accessed directly, the captcha image will be generated and stores the generated code in the session.

    $captcha = new Captcha;
    if ($captcha->isValid()) { }

    TO-DO: Not this, at all. Switch to recaptcha or something.

*/

class Captcha {
    function isValid() {
        return $this->validate();
    }

    private function validate() {
        if (is_null($_SESSION['captcha_key']))
            return false;

        return (strtoupper($_POST['num']) === $_SESSION['captcha_key']) ? true : false;
        unset($_SESSION['captcha_key']);
    }

    function generate() {
        //Generate captcha and set in session.
        $start = rand(0,25);
        $length = 5; //Length of captcha. Does not prevent from overflowing.
        $captcha = strtoupper(substr(md5(microtime()),$start,$length));

        session_start();
        $_SESSION['captcha_key'] = $captcha;

        //Configuration.
        $lines = rand(2,4); //Amount of lines to draw.
        $circles = rand(1,3); //Amount of circles to draw.
        $padding = rand(1,8); //Initial horizontal offset.

        //Helper functions.
        if (!function_exists('rx')) {
            function rx() { return rand(0,100); }
            function ry() { return rand(0,30); }
        }

        //Create image.
        $image = @imagecreatetruecolor(100, 30) or die("Cannot Initialize new GD image stream");

        //Draw half the lines now.
        $red = floor($lines * 0.75);
        $lines -= $red;
        $this->drawLines($image, $red);

        //Write out each letter individually.
        $index = 0; $x = $padding;
        while ($index < strlen($captcha)) {
            $bg_color = imagecolorallocate($image, rand(153,255), rand(153,255), rand(153,255));
            $text_color = imagecolorallocate($image, rand(153,255), rand(153,255), rand(153,255));

            //Calculate position.
            $f = rand(4,5);
            $x = $x + imagefontwidth($f) + rand(3,8);
            $y = rand(4,(30 - imagefontheight($f) - 4));
            imagestring($image, $f, $x+rand(-1,1), $y+rand(-1,1), substr($captcha,$index,1), $bg_color);
            imagestring($image, $f, $x, $y, substr($captcha,$index,1), $text_color);
            $index++;
        }

        //Draw the rest of the lines.
        $this->drawLines($image, $lines);

        //PHP >= 4.3
        //$image = imagerotate($image, rand(-17,17), 0);

        //Output image directly:
        //header("Content-type: image/jpeg");
        //imagejpeg($image);

        //Return image data as base64 encoded:
        ob_start();
        imagejpeg($image);
        $img = base64_encode(ob_get_contents());
        ob_end_clean();

        return 'data:image/jpeg;base64,' . $img;
    }

    private function drawLines($image, $amount) {
        $amount = ($amount) ? $amount : 1;

        while ($amount) {
            imagesetthickness($image, rand(1,2));
            $line_color = imagecolorallocate($image,rand(0,152),rand(0,152),rand(0,152));
            imageline($image, rx(), ry(), rx(), ry(), $line_color);
            $amount--;
        }
    }
}

//If accessed directly, generate the captcha like normal (produce JPEG):
/*if ($_SERVER['SCRIPT_FILENAME'] == __FILE__) {
    $a = new Captcha;
    $a->generate();
}*/

?>