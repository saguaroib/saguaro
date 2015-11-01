<?php

class Login {
    function error($in) {
        //Currently error() isn't loaded anywhere in Admin's pipeline so we'll use this for now.
        exit($in);
    }

    function doLogin($usernm, $passwd) {
        global $mysql;

        $usernm = mysql_real_escape_string($usernm);
        $check = $mysql->fetch_assoc("SELECT user,password,public_salt FROM " . SQLMODSLOG . " WHERE user='$usernm'");

        if ($check === false) {
            //Username does not exist.
            $this->storeBad($usernm, $passwd);
            $this->error(S_WRONGPASS);
        } else {
            //Username exists, hash given password and compare.
            require_once(CORE_DIR . '/crypt/legacy.php');
            $crypt = new SaguaroCryptLegacy;

            if (!$crypt->compare_hash($passwd, $check['password'], $check['public_salt'])) {
                $this->storeBad($usernm, $passwd);
                $this->error(S_WRONGPASS);
            } else {
                setcookie('saguaro_auser', $check['user'], 0);
                setcookie('saguaro_apass', $check['password'], 0);
            }
        }

        return "<META HTTP-EQUIV='refresh' content='0;URL=" . PHP_ASELF_ABS . " '>";
    }

    private function storeBad($user, $pass) {
        global $mysql;

        $user = mysql_real_escape_string($user);
        $pass = mysql_real_escape_string($pass);
        $ip = $_SERVER['REMOTE_ADDR'];

        $mysql->query("INSERT INTO loginattempts (userattempt,passattempt,board,ip,attemptno) values('$user','$pass','" . BOARD_DIR . "','$ip','1')");
    }

    function auth($pass) {
        /*    if ($pass && $pass != PANEL_PASS)
        error(S_WRONGPASS);*/

        require_once(CORE_DIR . "/admin/report.php");

        $getReport = new Report;
        $active = $getReport->get_all_reports_board();

        if (valid('janitor_board')) {
            echo  head();
            echo "<div class='panelOps' />[<a href=\"" . PHP_SELF2 . "\">" . S_RETURNS . "</a>]";
            echo "[<a href=\"" . PHP_SELF . "\">" . S_LOGUPD . "</a>]";
            if (valid('moderator')) {
                echo "[<a href='" . PHP_ASELF_ABS . "?mode=rebuild' >Rebuild</a>]";
                echo "[<a href='" . PHP_ASELF_ABS . "?mode=rebuildall' >Rebuild all</a>]";
                echo "[<a href='" . PHP_ASELF_ABS . "?mode=reports' >" . $active . "</a>]";
            }
            echo "[<a href='" . PHP_ASELF . "?mode=logout'>" . S_LOGOUT . "</a>]";
            echo "<div class='managerBanner' >" . S_MANAMODE . "</div>
           </div>";
            //echo "<form action='" . PHP_SELF . "' method='post' id='contrib' >";
        } else { // Admin.php login
            $temp = "" .
                "<div align='center' vertical-align='middle'>" .
                //echo "<input type='hidden' name=mode value=login>";
            $temp = '<form action="' . PHP_ASELF . '" method="post"><table>' .
                    '<tr><td>Username</td><td><input type="text" name="usernm"  style="width:100%" /></td></tr>'.
                    '<tr><td>Password</td><td><input type="password" name="passwd" style="width:100%" /></td></tr>';

            if (SECURE_LOGIN) {
                if (RECAPTCHA) {
                    $temp .= "<tr><td colspan='2'><script src='//www.google.com/recaptcha/api.js'></script><div class='g-recaptcha' data-sitekey='" . RECAPTCHA_SITEKEY ."'></td></tr>";
                } else {
                    $temp .= "<tr><td><img src='" . CORE_DIR_PUBLIC . "/general/captcha.php' /></td><td><input type='text' name='num' size='20' placeholder='Captcha'></td></tr>";
                }
            }

            $temp .= "<tr><td colspan='2'><input type='submit' value='" . S_MANASUB . "'></td></tr></table>" .
                    "<br></form></div>";

            echo $temp;

            if (isset($_POST['usernm']) && isset($_POST['passwd'])) {
                if (SECURE_LOGIN) {
                    require_once(CORE_DIR . '/general/captcha.php');
                    $captcha = new Captcha;

                    if ($captcha->isValid() !== true)
                        $this->error(S_CAPFAIL);
                }

                $this->doLogin($_POST['usernm'], $_POST['passwd']);
                echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_ASELF_ABS . "\">";
            }
            die("</body></html>");
        }
        return $temp;
    }
}
?>