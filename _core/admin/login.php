<?php

class Login {

    function doLogin($usernm, $passwd) {
        global $mysql, $csrf;

        $usernm = $mysql->escape_string($usernm);
        $check = $mysql->fetch_assoc("SELECT username,password,salt FROM " . SQLMODSLOG . " WHERE username='$usernm'");

        if ($check === false) {
            //Username does not exist.
            error(S_WRONGPASS);
            die("</body></html>");
        } else {

            //Username exists, hash given password and compare.
            require_once(CORE_DIR . '/crypt/legacy.php');
            $crypt = new SaguaroCryptLegacy;

            if (!$crypt->compare_hash($passwd, $check['password'], $check['salt'])) {
                error(S_WRONGPASS);
                die("</body></html>");
            } else {
                $csrf->init();
                //setcookie(name,value,expire,path,domain,secure,httponly);
                //$boards = explode(",", valid('boards'));
                setcookie('saguaro_auser', $check['username']);//, time() + 14400, '/',false, true);//, '/', SITE_ROOT_BD, false, true);
                setcookie('saguaro_apass', $check['password']);//, time() + 14400, '/', false, true);//, '/', SITE_ROOT_BD, false, true);
			/*	foreach ($boards as $board) 
                    $set = valid('status', $board);
                    if ($set) {
                        setcookie('loadThis', $set, 0); //Admin js file
                    }
                }*/
            }
        }

        return true;
    }

    function auth() {
        
            require_once(CORE_DIR . "/page/page.php");		//Load page class
            $page = new Page;
        
            if (isset($_POST['usernm']) && isset($_POST['passwd'])) {
                if (SECURE_LOGIN) {
                    require_once(CORE_DIR . '/general/captcha.php');
                    $captcha = new Captcha;

                    if ($captcha->isValid() !== true)
                        error(S_CAPFAIL);
                }

                $this->doLogin($_POST['usernm'], $_POST['passwd']);
                echo "<META HTTP-EQUIV='refresh' content='0;URL=" . PHP_ASELF_ABS . " '>";
            }

            if (!valid('janitor')) { 
                $temp = "<div align='center' vertical-align='middle'>";
                $temp .= "<style type='text/css'>input[type='text'] { border: .5px solid black; padding:2px;} input[type='password'] { border: 1px solid black; padding:2px;}</style>";
                $temp .= '<form action="' . PHP_ASELF . '" method="post"><table>' .
                        '<tr><td class="postblock">Username</td><td><input type="text" name="usernm"  style="width:100%" /></td></tr>'.
                        '<tr><td class="postblock">Password</td><td><input type="password" name="passwd" style="width:100%" /></td></tr>';

                if (SECURE_LOGIN)
                        $temp .= "<tr><td><img src='" . CORE_DIR_PUBLIC . "/general/captcha.php' /></td><td><input type='text' name='num' size='20' placeholder='Captcha'></td></tr>";

                $temp .= "<tr><td colspan='2'><input type='submit' value='" . S_MANASUB . "'></td></tr></table>" .
                        "<br></form></div>";

                echo $page->generate($temp);
                die("</body></html>");
            }
            
            return true;
    }
}
?>
