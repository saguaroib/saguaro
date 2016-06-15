<?php

/*

    Upload check for Regist

    Validates all received information, and halts if there's a problem.

*/

class UploadCheck {
    private $last = "";

    function run() {
        clearstatcache();
        if (is_file("pad.lock")) {
            if (is_file("../lockmsg.txt")) //Notice to display when posting is locked
                echo file_get_contents("../lockmsg.txt");
            else 
                error("Posting disabled, try again later!", $upfile);
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") error(S_UNJUST, $upfile); //Ensure the data was sent via POST.
        if ($this->captcha() !== true) error($this->last, $upfile); //Captcha check.
        if ($this->proxy() !== true) error($this->last, $upfile); //Proxy check.
        if ($this->uploadedFile() !== true) error($this->last, $upfile); //File check.
        
        //These checks access the SQL server so we should prioritize these last and then order based on how intensive they are.
        if ($this->banned() !== true) error($this->last, $upfile); //Ban check.
        if ($this->locked() !== true) error($this->last, $upfile); //Lock check.
    }

    function captcha() {
        if (BOTCHECK === true && !valid('moderator')) {
            require_once(CORE_DIR . '/general/captcha.php');
            $captcha = new Captcha;

            if ($captcha->isValid() !== true)
                $this->last = S_CAPFAIL;
                return false;
        }
        return true;
    }

    function proxy() {
        //Basic proxy check.
        if (PROXY_CHECK && preg_match("/^(mail|ns|dns|ftp|prox|pc|[^\.]\.[^\.]$)/", $host) > 0 || preg_match("/(ne|ad|bbtec|aol|uu|(asahi-net|rim)\.or)\.(com|net|jp)$/", $host) > 0) {
            if (@fsockopen($_SERVER["REMOTE_ADDR"], 80, $a, $b, 2) == 1) {
                $this->last = S_PROXY80;
                //error(S_PROXY80, $dest);
                return false;
            } elseif (@fsockopen($_SERVER["REMOTE_ADDR"], 8080, $a, $b, 2) == 1) {
                $this->last = S_PROXY8080;
                //error(S_PROXY8080, $dest);
                return false;
            }
        }
        return true;
    }

    function uploadedFile() {
        
            if ($_FILES["upfile"]["error"] > 0) {
                if ($_FILES["upfile"]["error"] == UPLOAD_ERR_INI_SIZE || $_FILES["upfile"]["error"] == UPLOAD_ERR_FORM_SIZE) {
                    $this->last = S_TOOBIG;
                    //error(S_TOOBIG, $upfile);
                    return false;
                }
                if ($_FILES["upfile"]["error"] == UPLOAD_ERR_PARTIAL || $_FILES["upfile"]["error"] == UPLOAD_ERR_CANT_WRITE) {
                    $this->last = S_UPFAIL;
                    //error(S_UPFAIL, $upfile);
                    return false;
                }
            }
            if ($upfile_name && $_FILES["upfile"]["size"] == 0) {
                $this->last = S_TOOBIGORNONE;
                //error(S_TOOBIGORNONE, $upfile);
                return false;
            }

        return true;
    }

    function banned() {
        global $host;
        require_once(CORE_DIR . "/admin/bans/check.php");
        $ban = new BanishCheck;
        if ($ban->isBanned($host, $redirect = true)) {
            $this->last = S_BADHOST;
            error("You are banned?", $upfile);
            return false;
        }
        return true;
    }

    function locked() {
        //Check if replying to locked thread
        $resto = (int) $resto;
        if ($resto) {
            global $my_log;
            $resto = (int) $resto;
            if ($my_log->cache[$resto]['locked'] && !$moderator) {
                $this->last = S_THREADLOCKED;
                //error(S_THREADLOCKED, $upfile);
                return false;
            }
        }
        return true;
    }
}