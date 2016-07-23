<?php

class Modify {
    
    public function init() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $thread = $this->canModify(true);
            return $this->modify($thread);
        } else {
            $thread = $this->canModify();
            return $this->display($thread);
        }
    }

    private function display($thread = null) {
        global $mysql, $csrf;

        require_once(CORE_DIR . "/page/head.php");
        $head = new Head;
        $head->info['page']['title'] = "/" . BOARD_DIR . "/ - Modify thread #{$thread['no']}";
        $head->info['css']['sheet'] = (NSFW) ? array("/stylesheets/admin/nwspanel.css") : array("/stylesheets/admin/wspanel.css");
        $head->info['css']['raw'] = array("table {margin: auto;}");

        
        $dat = $head->generate($noHead = true);

        $dat .="<form action='" . PHP_SELF_ABS . "?mode=admin&admin=modify' method='POST'>";
        $dat .= $csrf->field();
        $dat .= "<input type='hidden' name='no' value='{$thread['no']}'>";
        $dat .= "<div class='centered'><table><tr>";
        
        $options = array("sticky", "lock", "permasage", "cylical", "archive");
        
        foreach ($options as $option) {
            if (valid($option)) {
                $dat .= "<th class='postblock'>" . ucfirst($option) . "</th>";
            }
        }
        
        $dat .= "</tr><tr>";
        
        foreach ($options as $option) {
            $checked = ($thread[$option]) ? 'checked' : null;
            if (valid($option)) {
                $dat .= "<td><input type='checkbox' name='{$option}' value='1' {$checked}></td>";
            }
        }
        
        $dat .= "</tr></table><input type='submit' value='Update'>";
        
        $dat .= "</div></form>";
        
        return $dat;
        
    }

    private function canModify($post = false) {
        global $mysql;
        
        if ($post) {
            if (isset($_POST['no']) && is_numeric($_POST['no'])) {
                $thread = (int) $_POST['no'];
            }
        } else {
            if (isset($_GET['no']) && is_numeric($_GET['no'])) {
                $thread = (int) $_GET['no'];
            }
        }
        
        if (!$thread) $this->error("Invalid post");

        $exists = (int) $mysql->result("SELECT COUNT(no) FROM " . SQLLOG . " WHERE no='{$thread}' AND board='" . BOARD_DIR . "'");
        
        if ($exists < 1) {
            $this->error(S_NOTHREADERR);
        }
        
        $status = $mysql->fetch_assoc("SELECT sticky,locked,permasage FROM " . SQLLOG . " WHERE no='{$thread}' AND board='" . BOARD_DIR . "'");
        
        $thread = array(
            'no' => $thread,
            'sticky' => ($status['sticky'] == 1) ? true : false,
            'lock' => ($status['locked'] >= 1) ? true : false,
            'permasage' => ($status['permasage'] == 1) ?true : false,
            'cylical' => ($status['sticky'] >= 2) ? true : false,
            'archive' => ($status['locked'] == 2) ? true : false,
        );

        return $thread;
    }

    //In house error message.
    private function error($mes) { 
        require_once(CORE_DIR . "/page/head.php");
        $head = new Head;
        $head->info['page']['title'] = $mes;
        $head = $head->generate($noHead = true);
        
        echo $head;
        echo "<br><br><div style='text-align:center;font-size:24px;font-color:#blue'>$mes<br><br>[<a href='//" . SITE_ROOT_BD . "'>" . S_RELOAD . "</a>]</div>";
        die("</body></html>");
    }
    
/*
   function mod() {
        global $mysql, $csrf;
        
        $no = (is_numeric($_POST['no'])) ? (int) $_POST['no'] : $this->error("Invalid post");

        if (!$csrf->validate()) $this->error(S_RELOGIN);

        switch ($_POST['action']) {
            case 'cylical':
                if (valid('cylical')) {
                    $proceed = true;
                    $sqlValue = "sticky";
                    $sqlBool  = "'2', root=root";
                    $verb     = "Set cylical";
                }
                break;
            case 'uncylical':
                if (valid('cylical')) {
                    $proceed = true;
                    $sqlValue = "sticky";
                    $sqlBool  = "'0', root=root";
                    $verb     = "Set cylical";
                }
                break;
            case 'sticky':
                if (valid('sticky')) {
                    $proceed = true;
                    $sqlValue = "sticky";
                    $rootnum  = "2027-07-07 00:00:00";
                    $sqlBool  = "'1', root='" . $rootnum . "'";
                    $verb     = "Stuck";
                }
                break;
            case 'unsticky':
                if (valid('sticky')) {
                    $proceed = true;
                    $sqlValue = "sticky";
                    $sqlBool  = "'0', root=now()";
                    $verb     = "Unstuck";
                }
                break;
            case 'lock':
                if (valid('lock')) {
                    $proceed = true;
                    $sqlValue = "locked";
                    $sqlBool  = "'1', root=root ";
                    $verb     = "Locked";
                }
                break;
            case 'unlock':
                if (valid('lock')) {
                    $proceed = true;
                    $sqlValue = "locked";
                    $sqlBool  = "'0', root=root ";
                    $verb     = "Unlocked";
                }
                break;
            case 'sage':
                if (valid('sage')) {
                    $proceed = true;
                    $sqlValue = "permasage";
                    $sqlBool  = "'1', root=root ";
                    $verb     = "Autosaged";
                }
                break;
            case 'unsage':
                if (valid('sage')) {
                $proceed = true;
                $sqlValue = "permasage";
                $sqlBool  = "'0', root=root ";
                $verb     = "De-autosaged";
                }
                break;
            case 'archive':
                if (valid('archive')) {
                    $proceed = true;
                    $sqlValue = "locked";
                    $sqlBool  = "'2', root=root ";
                    $verb     = "Force-archived";
                }
                break;
            case 'dearchive':
                if (valid('archive')) {
                    $proceed = true;
                    $sqlValue = "locked";
                    $sqlBool  = "'0', root=root ";
                    $verb     = "Un-archived";
                }
                break;
            default:
				$proceed = false;
                break;
        }

        if ($proceed === true) {
            $status = "ok";
            $mes = "{$verb} thread {$no}";
            $mysql->query('UPDATE ' . SQLLOG . " SET  $sqlValue=$sqlBool WHERE no='{$no}' AND board='" . BOARD_DIR ."'");
            $mysql->query("INSERT INTO " . SQLDELLOG . " (admin, type, action, time, board, postno) VALUES ('" . $mysql->escape_string($_COOKIE['saguaro_auser']) . "', '5', '{$verb} thread #{$no}', '" . time() . "', '" . BOARD_DIR . "', '{$no}')");
            $mes = "{$verb} thread {$no}";
        } else {
            $status = "no";
            $mes = "no permission";
        }
        

        
        $temp = json_encode("status" => $status, "mes" => $mes);
        return $temp;
    }*/
}
    
?>
