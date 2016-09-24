<?php

/*

    ProcessFile for Regist

    Extracts information from the desired file.

*/

class ProcessFile {
    private $last = "";

    function run($file) {
        if ($this->check($file) !== true) { return $this->error($file); } //Return early and do nothing if check fails.
        if ($this->signature($file) !== true) { return $this->error($file); } //Check for embedded signatures.

        global $path;
        $info = [];

        //upload processing
        $extension = pathinfo($file["name"], PATHINFO_EXTENSION); //Basic file extension, up for improvements.
        $dest = $file['temp'];
        //$dest = $path.$tim.'.tmp';
        /*if (OEKAKI_BOARD == 1 && $_POST['oe_chk']) {
            rename($upfile, $dest);
            chmod($dest, 0644);
            if ($pchfile)
                rename($pchfile, "$dest.pch");
        } else*/

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
            $info = $processor->process(['name' => $file["name"], 'temp' => $dest]);
        } else {
            require_once('image.php');
            $process = new ProcessImage;
            $info = $process->run($dest);
        }

        $dest = $path . $file["tim"] . "-" . $file["index"] . "." . $extension; //Premature.
        $post = [
            'passCheck' => true,
            'localname' => basename($dest),
            'location' => $dest,
            'md5' => md5_file($file['temp']),
            'filesize' => $fsize,
            'original_name' => $file["name"],
            'original_extension' => $extension
        ];
        $info = array_merge($post,$info);

        //$mes = $upfile_name . ' ' . S_UPGOOD;

        //Really weird. Several optimal ways, couldn't decide on the perfect way to trickle up this late.
        $unique = $this->checkHash($info['md5']);
        if (!$unique) {
            $info['passCheck'] = false;
            $info['message'] = S_SAMEPIC.' ('.$info['original_name'].')';
        }

        //Move the file out of temp if it passed checks.
        if ($info['passCheck'] !== false) {
            move_uploaded_file($file['temp'], $dest);
            clearstatcache(); // otherwise $dest looks like 0 bytes!
        }

        return $info;
    }

    function error($file) {
        return ['passCheck' => false, 'message' => $this->last . " (" . $file['name'] . ")"];
    }

    function checkHash($hash) {
        //Returns true (pass/unique/disabled) or false (fail/dupe).
        global $mysql;
        if (DUPE_CHECK) {
            if (!preg_match('/^[a-z0-9]+$/i', $hash)) { return false; }
            $dupe = !(bool) $mysql->num_rows($mysql->query("SELECT 1 FROM " . SQLMEDIA . " WHERE hash='{$hash}' LIMIT 1"));
            return $dupe;
        } else {
            return true;
        }
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

    function signature($file) {
        //Checks the entire file for additional, potentially unwanted or malicious file signatures which may have been embedded.
        //This is most likely really slow, and should eventually be made configurable to use or not.

        //Open file for reading, read it into local scope, then close the handle.
        $input = $file['temp'];
        $handle = fopen($input, "r");
        $read = fread($handle, $file['size']);
        fclose($handle);

        /*
            List of signatures check for and error out if found.
            https://wikipedia.org/wiki/List_of_file_signatures
            Keep in mind this checks through the entire file, not just from the start.
            Therefore, $bad_signatures should could contain as uniquely identifiable (long) signatures as possible.
            Shorter signatures may return false positives and therefore should be avoided with this method.
        */
        $bad_signatures = [
            'ZIP' => 'PK(\x03\x04|\x05\x06|\x07|\x08)',
            'RAR' => 'Rar!\x1A\x07',
            '7ZIP' => '\x37\x7A\xBC\xAF\x27\x1C',
            'TAR' => 'ustar(\x00\x30\x30|\x20\x20\x00)'
        ];

        foreach ($bad_signatures as $type => $signature) {
            preg_match("/$signature/", $read, $matches);
            if (count($matches) > 0) {
                $this->last = "Potentially malicious file detected ($type), rejected.";
                return false;
            }
        }

        return true;
    }
}

?>
