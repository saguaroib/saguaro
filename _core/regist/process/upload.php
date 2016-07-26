<?php

/*

    Upload check for Regist

    Validates all received information, and halts if there's a problem.

*/

class UploadCheck {
    private $last = "";

    function run() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") error(S_UNJUST); //Ensure the data was sent via POST.
        if ($this->captcha() !== true) error($this->last); //Captcha check.
        if ($this->proxy() !== true) error($this->last); //Proxy check.

        //These checks access the SQL server so we should prioritize these last and then order based on how intensive they are.
        if ($this->banned() !== true) { header("Location: banned.php"); die();} //Ban check.
        if ($this->locked() !== true) error($this->last); //Lock check.
        if ($this->media() !== true) error($this->last); //Media check.
        if ($this->cooldown() !== true) error($this->last); //Flood/cooldown checks.
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
        $resto = (int) $_POST['resto'];
        if ($resto) {
            global $mysql;

            $result = $mysql->fetch_array("SELECT * FROM " . SQLLOG . " WHERE no=$resto");
            if ($result["locked"] == '1' && !valid('moderator')) {
                $this->last = S_THREADLOCKED; //error(S_THREADLOCKED);
                return false;
            }
        }
        return true;
    }

    function media() {
        $resto = (int) $_POST['resto'];
        if ($resto) {
            global $mysql;

            //The latter (default) replicates old behavior where each post counts as one file, regardless of its actual amount.
            $query = (STRICT_FILE_COUNT === true) ? '*' : 'DISTINCT parent';
            $query = "select COUNT({$query}) from ".SQLMEDIA." where resto=$resto OR parent=$resto";

            $file_count = $mysql->query($query);
            $file_count = $mysql->fetch_row($file_count)[0];

            if ($file_count > MAX_IMGRES) {
                $mysql->free_result($file_count);
                $this->last = 'Media bump limit reached.';
                //error("Max limit of " . MAX_IMGRES . " image replies has been reached.", $upfile);
                return false;
            }
        }
        return true;
    }

    function cooldown() {
        $canFlood = (valid('moderator')) ? true : false;
        if ($canFlood) return true;

        global $mysql;

        $resto = (int) $_POST['resto'];
        $host = $mysql->escape_string($_SERVER['REMOTE_ADDR']);
        $time = time();
        $has_file = ($_FILES['upfile']['error'][0] == UPLOAD_ERR_NO_FILE) ? 0 : 1;

        //Pull all recent rows (to the highest timeout) from the SQL table.
        $min = $time - max(COOLDOWN_POST,COOLDOWN_FILE,COOLDOWN_THREAD);
        echo "$time -> $min";
        $query = "SELECT time,resto FROM `".SQLLOG."` WHERE host='{$host}' AND time>=$min";
        $query = $mysql->query($query);
        $result = $mysql->result($query);
        $amount = $mysql->num_rows($query);

        if ($amount > 0) { //We have at least one post that violates the cooldown periods.
            //echo " / ".$amount; die();
            $this->last = S_RENZOKU; //We could theoretically stop here if we don't care to give a SPECIFIC error message or care about the differences.

            //Check each row.
            while ($row = $mysql->fetch_assoc($query)) {
                //Types: text post, text post (dupe), file post, thread
                $diff = ($time - $row['time']); //Time difference before valid.

                if (!$has_file && $resto > 0 && $diff <= COOLDOWN_POST) { //Text replies
                    $this->last = 'Please wait '.(COOLDOWN_POST - $diff).' seconds and try again. (Post)';
                    return false;
                } else if ($has_file && $resto > 0 && $diff <= COOLDOWN_FILE) { //File replies
                    $this->last = 'Please wait '.(COOLDOWN_FILE - $diff).' seconds and try again. (File)';
                    return false;
                } else if (!$resto || $resto == 0 && $row['resto'] == 0 && $diff <= COOLDOWN_THREAD) { //Thread creation
                    //$this->last = S_RENZOKU3;
                    $this->last = 'Please wait '.(COOLDOWN_THREAD - $diff).' seconds and try again. (Thread)';
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