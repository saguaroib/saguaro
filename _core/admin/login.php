<?php

class Login {

    public function init() {
        global $mysql, $csrf;
        
        if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['usernm']) && isset($_POST['passwd'])) {
            $username = $mysql->escape_string($_POST['usernm']);
            $passwd = $mysql->escape_string($_POST['passwd']);

            $check = $mysql->result("SELECT COUNT(user) FROM " . SQLMODSLOG . " WHERE user='{$username}'");

            if (SECURE_LOGIN) {
                require_once(CORE_DIR . '/general/captcha.php');
                $captcha = new Captcha;

                if ($captcha->isValid() !== true)
                    error(S_CAPFAIL);
            }
            
            
            if ($check === 0) { //Username does not exist.
                error(S_WRONGPASS);
                die("</body></html>");
            } else { //Username exists, hash given password and compare.
                require_once(CORE_DIR . '/crypt/legacy.php');
                $crypt = new SaguaroCryptLegacy;
                $check = $mysql->fetch_assoc("SELECT user,password,public_salt FROM " . SQLMODSLOG . " WHERE user='{$username}'");
                if (!$crypt->compare_hash($passwd, $check['password'], $check['public_salt'])) {
                    error(S_WRONGPASS);
                    die("</body></html>");
                } else {
                    setcookie('saguaro_auser', $check['user'], time() + (10 * 365 * 24 * 60 * 60), '/');//, time() + 14400, '/',false, true);//, '/', SITE_ROOT_BD, false, true);
                    setcookie('saguaro_apass', $check['password'], time() + (10 * 365 * 24 * 60 * 60), '/');//, time() + 14400, '/', false, true);//, '/', SITE_ROOT_BD, false, true);
                    //$csrf->init();
                    //$mysql->query("UPDATE "  . SQLBACKEND . "." . SQLMODSLOG . " SET last_login='" . time() . "' WHERE username='{$check['username']}'");
                    //setcookie(name,value,expire,path,domain,secure,httponly);
                }
            }
            header("Refresh:0");
        } else {
            if (!valid('moderator')) {
                require_once(CORE_DIR . "/page/page.php");		//Load page class
                $page = new Page;
                
                $page->headVars['page']['title'] = "Moderation login";
                $page->headVars['page']['sub'] = "Manage your boards!";
                
                $temp = "<div align='center' vertical-align='middle'>";
                $temp .= "<style type='text/css'>input[type='text'] { border: .5px solid black; padding:2px;} input[type='password'] { border: 1px solid black; padding:2px;}</style>";
                $temp .= '<form method="post"><table>
                    <tr><td class="postblock">Username</td><td><input type="text" name="usernm"  style="width:100%" /></td></tr>
                    <tr><td class="postblock">Password</td><td><input type="password" name="passwd" style="width:100%" /></td></tr>';

                if (SECURE_LOGIN) {
                    if (RECAPTCHA) {
                        $temp .= "<tr><td colspan='2'><script src='//www.google.com/recaptcha/api.js'></script><div class='g-recaptcha' data-sitekey='" . RECAPTCHA_SITEKEY ."'></td></tr>";
                    } else {
                        $temp .= "<tr><td><img src='" . CORE_DIR_PUBLIC . "/general/captcha.php' /></td><td><input type='text' name='num' size='20' placeholder='Captcha'></td></tr>";
                    }
                }

                $temp .= "<tr><td colspan='1'></td><td><input type='submit' value='Enter panel'></td></tr></table>" .
                    "</form></div>";

                echo $page->generate($temp);
                die("</body></html>");
            }
        }
    }
}