<?php

/*

    ProcessFile for Regist

    Extracts information from the desired file.

*/

class ProcessFile {
    function run($upfile,$tim) {
        global $path;
        $info = [];

        //upload processing
        $extension = pathinfo($_FILES["upfile"]["name"], PATHINFO_EXTENSION); //Basic file extension, up for improvements.
        $dest = $path . "$tim.$extension"; //Just name it right the first time.
        //$dest = $path.$tim.'.tmp';
        /*if (OEKAKI_BOARD == 1 && $_POST['oe_chk']) {
            rename($upfile, $dest);
            chmod($dest, 0644);
            if ($pchfile)
                rename($pchfile, "$dest.pch");
        } else*/

        move_uploaded_file($upfile, $dest);
        clearstatcache(); // otherwise $dest looks like 0 bytes!

        //$upfile_name = $sanitize->CleanStr($upfile_name, 0);
        $fsize = filesize($dest);

        if (!is_file($dest))
            error(S_UPFAIL, $dest);
        if (!$fsize /*|| /*$fsize > MAX_KB * 1024*/)
            error(S_TOOBIG, $dest);

        // PDF processing
        if (ENABLE_PDF == 1 && strcasecmp('.pdf', substr($upfile_name, -4)) == 0) {
            $ext = '.pdf';
            $W   = $H = 1;
            // run through ghostscript to check for validity
            if (pclose(popen("/usr/local/bin/gs -q -dSAFER -dNOPAUSE -dBATCH -sDEVICE=nullpage $dest", 'w'))) {
                error(S_UPFAIL, $dest);
            }
        } else if ($extension == "webm") {
            require('video.php');
            $processor = new VideoProcessor;
            $info = $processor->process($dest);
        } else {
            require_once('image.php');
            $process = new ProcessImage;
            $info = $process->run($dest);
        }

        $post = [
            'location' => $dest,
            'md5' => md5_file($dest),
            'filesize' => $fsize,
            'original_name' => $_FILES["upfile"]["name"],
            'original_extension' => $extension
        ];
        $info = array_merge($info,$post);
        //$mes = $upfile_name . ' ' . S_UPGOOD;

        return $info;
    }
}

?>