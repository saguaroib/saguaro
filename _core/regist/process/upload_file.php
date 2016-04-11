<?php

/*

    ProcessFile for Regist

    Extracts information from the desired file.

*/

class ProcessFile {
    private $last = "";

    function run($file) {
        global $path;
        if ($this->check($file) !== true) { //Return early and do nothing if check fails.
            return ['passCheck' => false, 'message' => $this->last];
        }
        $info = [];

        //upload processing
        $extension = pathinfo($file["name"], PATHINFO_EXTENSION); //Basic file extension, up for improvements.
        $dest = $path . $file["tim"] . "-" . $file["index"] . "." . $extension; //Just name it right the first time.
        //$dest = $path.$tim.'.tmp';
        /*if (OEKAKI_BOARD == 1 && $_POST['oe_chk']) {
            rename($upfile, $dest);
            chmod($dest, 0644);
            if ($pchfile)
                rename($pchfile, "$dest.pch");
        } else*/

        move_uploaded_file($file['temp'], $dest);
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
            require_once('video.php');
            $processor = new VideoProcessor;
            $info = $processor->process($dest);
        } else {
            require_once('image.php');
            $process = new ProcessImage;
            $info = $process->run($dest);
        }

        $post = [
            'passCheck' => true,
            'localname' => basename($dest),
            'location' => $dest,
            'md5' => md5_file($dest),
            'filesize' => $fsize,
            'original_name' => $file["name"],
            'original_extension' => $extension
        ];
        $info = array_merge($info,$post);
        //$mes = $upfile_name . ' ' . S_UPGOOD;

        return $info;
    }

    function check($file) {
        //Very generic upload checks. Specific checks are done in the media processors.
        //Currently errors are suppressed since these run in a loop and we don't want to stop on any given one.
        //Actual handling of this check should be done in Regist after the status is returned.
        if ($file['error'] > 0) {
            if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE])) {
                $this->last = S_TOOBIG; //error(S_TOOBIG, $upfile);
                return false;
            }
            if (in_array($file['error'], [UPLOAD_ERR_PARTIAL, UPLOAD_ERR_CANT_WRITE])) {
                $this->last = S_UPFAIL; //error(S_UPFAIL, $upfile);
                return false;
            }
        }

        if (/*$upfile_name && */$file["size"] == 0) {
            $this->last = S_TOOBIGORNONE; //error(S_TOOBIGORNONE, $upfile);
            return false;
        }

        return true;
    }
}

?>