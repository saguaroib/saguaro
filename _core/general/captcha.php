<?php

/*

    CAPTCHA.
    
    TO-DO: Not this, at all. Switch to recaptcha or something. - RePod.
    Increasing difficulty for humans with no real stats for bots.
    
    If accessed directly, the captcha image will be generated and stores the generated code in the session.
    
    A lot more secure than before as validation is handled by the class entirely (and cannot be modified).

*/

class Captcha {
    function isValid() {
        return $this->validate();
    }
    
    private function validate() {
        return (strtoupper($_REQUEST['num']) === $_SESSION['captcha_key']) ? true : false;
    }
    
    function generate() {
        //Generate captcha and set in session.
        $start = rand(0,25);
        $length = 5; //Length of captcha. Does not prevent from overflowing.
        $captcha = strtoupper(substr(md5(microtime()),$start,$length));
        
        session_start();
        $_SESSION['captcha_key'] = $captcha;

        //Configuration.
        $lines = rand(2,4); //Amount of lines to have.
        $padding = rand(1,8); //Initial horizontal offset.

        //Helper functions
        function rx() {
            return rand(0,100);
        }
        function ry() {
            return rand(0,30);
        }

        //Create image.
        $image = @imagecreatetruecolor(100, 30) or die("Cannot Initialize new GD image stream");

        //Write out each letter individually.
        $index = 0; $x = $padding;
        while ($index < strlen($captcha)) {
            $text_color = imagecolorallocate($image, rand(153,255), rand(153,255), rand(153,255));
            $bg_color = imagecolorallocate($image, rand(153,255), rand(153,255), rand(153,255));
            
            //Calculate position.
            $f = rand(4,5);
            $x = $x + imagefontwidth($f) + rand(2,6);
            $y = rand(4,(30 - imagefontheight($f) - 4));
            imagestring($image, $f, $x+rand(-1,1), $y+rand(-1,1), substr($captcha,$index,1), $bg_color);
            imagestring($image, $f, $x, $y, substr($captcha,$index,1), $text_color);
            $index++;
        }
        
        while ($lines) {
            imagesetthickness($image, rand(1,2));
            $line_color = imagecolorallocate($image,rand(0,152),rand(0,152),rand(0,152));
            imageline($image,rx(),ry(),rx(),ry(),$line_color);
            $lines--;
        }


        //Output image.
        header("Content-type: image/jpeg");
        imagejpeg($image);//Output image to browser 
    }
}

//If accessed directly, generate the captcha like normal.
if ($_SERVER['SCRIPT_FILENAME'] == __FILE__) {
    $a = new Captcha;
    $a->generate();
}

?>