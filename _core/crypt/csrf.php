<?php
/*
    METAPHORICALLY the most clever and secure implementation on the entire internet.
*/

class CSRF {
    
    public function init() {
        if (isset($_COOKIE['saguaro_csrf'])) {
            return true;
        } else {
            $token = bin2hex(openssl_random_pseudo_bytes(18));
            setcookie("saguaro_csrf", $token);//, time() + 14400, SITE_ROOT, false, true);
        }
    }

    public function validate() {
        if ($this->init()) {
            if (isset($_POST['csrf']) && $_POST['csrf'] == $_COOKIE['saguaro_csrf']) {
                return true;
            }
        }
        return false;
    }

    public function field() {
        if (!$this->init()) error(S_RELOGIN);
        $temp .= "<input type='hidden' name='csrf' value='" . $_COOKIE['saguaro_csrf'] . "'/>";
        
        return $temp;
    }
}