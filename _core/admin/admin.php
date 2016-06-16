<?php

/*
    Work in progress. Currently not used anywhere.
    Don't touch for now.
*/


class AdminRoot {
    
    public function init() {
        
        require(CORE_DIR . "/lang/language.php");

        require_once(CORE_DIR . "/crypt/csrf.php");
        $csrf = new CSRF;
        
        require_once(CORE_DIR . "/admin/login.php");	//First line of security. Die script if user isn't logged in
        $login = new Login;
        $login->auth();
        
        /*if (!valid("janitor"))
            error(S_NOPERM);*/

        switch($_GET['admin']) {
            case 'ban':
                require_once(CORE_DIR . "/admin/bans/forms.php");
                $bans = new BanishForms;
                echo $bans->init();
                break;
            case 'settings':
                require_once(CORE_DIR . "/boards/settings.php");
                $board = new SBoard;
                $html = $board->init();
                echo $page->generate($html, true);
                break;
            case 'more':
                $html = $table->moreInfo($_GET['no']);
                echo $page->generate($html, true, false);
                break;
            case 'rebuild':
                break;
            case 'modify':
                require_once(CORE_DIR . "/admin/modify.php");
                $modify = new Modify;
                echo $modify->mod();
                break;
            case 'logout':
                setcookie('saguaro_apass', '0', 1);
                setcookie('saguaro_auser', '0', 1);
                setcookie('loadThis', 'null', 1);
                echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF2_ABS . "\">";
                break;
            case 'test':
                echo "hello!";
                break;
            default:
                $html = $table->landing();
                echo $page->generate($html, true, false);
                break;
        }
    }
}