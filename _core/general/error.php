<?php

class Error {
    /*function __construct($mes, $dest = '', $fancy = 0) {
        $this->format($mes, $dest, $fancy);
    }*/

    function format($mes, $dest = '', $fancy = 0) {
        global $path;
        //debug_print_backtrace(); //Useful for figuring out where the call is coming from and why.

        if (is_file($dest))
            unlink($dest);


        if (!$fancy) {
            require_once(CORE_DIR . "/page/head.php");
            $head = new Head; $head = $head->generate();
            $upfile_name = $_FILES["upfile"]["name"];

            echo $head;
            echo "<br><br><hr><br><br><div style='text-align:center;font-size:24px;font-color:#blue'>$mes<br><br><a href='" . PHP_SELF2_ABS . "'>" . S_RELOAD . "</a></div><br><br><hr>";
            die("</body></html>");
        }
    }
}

?>