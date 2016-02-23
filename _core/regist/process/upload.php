<?php

/*

    Upload check for Regist

    Validates all received information, and halts if there's a problem.

*/

class UploadCheck {
    private $last = "";

    function run() {
        $upfile = $_FILES["upfile"]["tmp_name"];

        if ($_SERVER["REQUEST_METHOD"] !== "POST") error(S_UNJUST, $upfile); //Ensure the data was sent via POST.
        if ($this->captcha() !== true) error($this->last, $upfile); //Captcha check.
        if ($this->proxy() !== true) error($this->last, $upfile); //Proxy check.
        if ($this->uploadedFile() !== true) error($this->last, $upfile); //File check.

        //These checks access the SQL server so we should prioritize these last and then order based on how intensive they are.
        if ($this->banned() !== true) error($this->last, $upfile, 1); //Ban check.
        if ($this->locked() !== true) error($this->last, $upfile); //Lock check.
        if ($this->media() !== true) error($this->last, $upfile); //Media check.
        if ($this->cooldown($upfile) !== true) error($this->last, $upfile); //Flood/cooldown checks.
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
        $host = $_SERVER['REMOTE_ADDR'];
        if (PROXY_CHECK && preg_match("/^(mail|ns|dns|ftp|prox|pc|[^\.]\.[^\.]$)/", $host) > 0 || preg_match("/(ne|ad|bbtec|aol|uu|(asahi-net|rim)\.or)\.(com|net|jp)$/", $host) > 0) {
            if (@fsockopen($host, 80, $a, $b, 2) == 1) {
                $this->last = S_PROXY80;
                //error(S_PROXY80, $dest);
                return false;
            } elseif (@fsockopen($host, 8080, $a, $b, 2) == 1) {
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
        require_once(CORE_DIR . "/admin/bans.php");
        $checkban = new Banish;
        if ($checkban->isBanned($_SERVER["REMOTE_ADDR"])) {
            $this->last = S_BADHOST;
            //error(S_BADHOST, $upfile);
            return false;
        }
        return true;
    }

    function locked() {
        //Check if replying to locked thread
        $resto = (int) $resto;
        if ($resto) {
            global $mysql;
            $resto = (int) $resto;
            $result = $mysql->fetch_array("SELECT * FROM " . SQLLOG . " WHERE no=$resto");
            if ($result["locked"] == '1' && !valid('moderator')) {
                $this->last = S_THREADLOCKED;
                //error(S_THREADLOCKED, $upfile);
                return false;
            }
        }
        return true;
    }

    function media() {
        $resto = (int) $resto;
        if ($resto) {
            global $mysql;
            if (!$result = $mysql->query("select COUNT(*) from " . SQLLOG . " where resto=$resto and fsize!=0")) {
                echo S_SQLFAIL;
            }

            $countimgres = $mysql->result($result, 0, 0);
            if ($countimgres > MAX_IMGRES) {
                $mysql->free_result($result);
                $this->last = 'Media bump limit reached.';
                //error("Max limit of " . MAX_IMGRES . " image replies has been reached.", $upfile);
                return false;
            }
        }
        return true;
    }

    function cooldown($upfile) {
		$canFlood = (valid('moderator')) ? true : false;
        if ($canFlood) return true;

		global $mysql;

        $resto = (int) $_POST['resto'];
        $host = $_SERVER['REMOTE_ADDR'];
        $time = time();

        //Pull all recent rows (to the highest timeout) from the SQL table.
        $min = $time - max(RENZOKU,RENZOKU2,RENZOKU3);
        $query = "SELECT time,resto FROM `".SQLLOG."` WHERE host='".$mysql->escape_string($host)."' AND time>=$min";
        $query = $mysql->query($query);
        $result = $mysql->result($query);
        $recent = $mysql->num_rows($query);

        if ($recent > 0) { //We have at least one post that violates the cooldown periods.
            //We could theoretically stop here if we don't care to give a SPECIFIC error message or care about the differences.
            $this->last = S_RENZOKU;

            //Check each row.
            while ($row = $mysql->fetch_assoc($query)) {
                //Replies (in general).
                if ($resto > 0 && $row['time'] > ($time - RENZOKU))
                    $this->last = 'reply';
                    return false;

                //Image replies.
                if ($upfile && $row['time'] > ($time - RENZOKU2))
                    $this->last = 'image '.S_RENZOKU2;
                    return false;

                //Thread creation. If no parent specified, and a pulled row is an OP (no parent): exit.
                if (!$resto || $resto == 0 && $row['resto'] == 0 && $row['time'] > ($time - RENZOKU)) {
                    //$this->last = S_RENZOKU3;
                    $this->last = 'thread';
                    return false;
                }
            }
        }

        return true;
    }
    
/*    function cooldown($upfile) {
        global $mysql;

        $resto = (int) $resto;
        $host = $_SERVER['REMOTE_ADDR'];
        $time = time();

        $canFlood = (valid('moderator')) ? true : false;

        if (!$canFlood) {

            //Reply cooldown
            if (!$upfile) {
                $query  = "SELECT COUNT(no)>0 FROM " . SQLLOG . " WHERE time>" . ($time - RENZOKU) . " " . "AND host='" . $mysql->escape_string($host) . "' AND resto>0";
                $result = $mysql->query($query);
                if ($mysql->result($result, 0, 0)) {
                    $this->last = S_RENZOKU;
                    $mysql->free_result($result);
                    return false;
                }
            }

            //Thread creation cooldown
            if (!$resto) {
                $query  = "SELECT COUNT(no)>0 FROM " . SQLLOG . " WHERE time>" . ($time - RENZOKU3) . " " . "AND host='" . $mysql->escape_string($host) . "' AND root>0"; //root>0 == non-sticky
                $result = $mysql->query($query);
                if ($mysql->result($result, 0, 0)) {
                    $this->last = S_RENZOKU3;
                    $mysql->free_result($result);
                    return false;
                }
            }

            //Image cooldown
            if ($upfile) {
                $query  = "SELECT COUNT(no)>0 FROM " . SQLLOG . " WHERE time>" . ($time - RENZOKU2) . " " . "AND host='" . $mysql->escape_string($host) . "' AND resto>0";
                $result = $mysql->query($query);
                if ($mysql->result($result, 0, 0)) {
                    $this->last = S_RENZOKU2;
                    $mysql->free_result($result);
                    return false;
                }
            }
        }
        return true;
    }*/
}

?>