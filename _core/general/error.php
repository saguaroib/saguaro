<?php

class Error {
    /*function __construct($mes, $dest = '', $fancy = 0) {
        $this->format($mes, $dest, $fancy);
    }*/

    function format($mes, $dest = '', $fancy = 0) {
        global $path;
        
        if (DEBUG_MODE) { 
            echo "<pre>";
            debug_print_backtrace(); //Useful for figuring out where the call is coming from and why.
            echo "</pre>";
        }

        $this->delete_uploaded_files();

        if (!$fancy) {
            require_once(CORE_DIR . "/page/head.php");
            $head = new Head; $head = $head->generate();
            $upfile_name = $_FILES["upfile"]["name"];

            echo $head;
            echo "<br><br><hr><br><br><div style='text-align:center;font-size:24px;font-color:#blue'>$mes<br><br>[<a href='//" . SITE_ROOT_BD . "'>" . S_RELOAD . "</a>]</div><br><br><hr>";
            die("</body></html>");
        }
    }
    
    private function delete_uploaded_files() {
        global $upfile_name, $path, $upfile, $dest;
        if ($dest || $upfile) {
            @unlink($dest);
            @unlink($upfile);

        }
    }
}

?>