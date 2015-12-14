<?php

class Modify {
    function mod($no, $action = 'none') {
        global $mysql;
        
        switch ($action) {
            case 'eventsticky':
                $sqlValue = "sticky";
                $rootnum  = "2027-07-07 00:00:00";
                $sqlBool  = "'2', root='" . $rootnum . "'";
                $verb     = "Stuck (event mode) ";
                break;
            case 'sticky':
                $sqlValue = "sticky";
                $rootnum  = "2027-07-07 00:00:00";
                $sqlBool  = "'1', root='" . $rootnum . "'";
                $verb     = "Stuck";
                break;
            case 'unsticky':
                $sqlValue = "sticky";
                $rootnum  = date('Y-m-d G:i:s');
                $sqlBool  = "'0', root='" . $rootnum . "'";
                $verb     = "Unstuck";
                break;
            case 'lock':
                $sqlValue = "locked";
                $sqlBool  = "'1', root=root ";
                $verb     = "Locked";
                break;
            case 'unlock':
                $sqlValue = "locked";
                $sqlBool  = "'0', root=root ";
                $verb     = "Unlocked";
                break;
            case 'permasage':
                $sqlValue = "permasage";
                $sqlBool  = "'1', root=root ";
                $verb     = "Autosaging";
                break;
            case 'nopermasage':
                $sqlValue = "permasage";
                $sqlBool  = "'0', root=root ";
                $verb     = "Normally bumping";
                break;
            case 'delete':
                delete_post($resno, $pwd, $imgonly = 0, $automatic = 1, $children = 1, $die = 1);
                break;
            case 'deleteallbyip':
                delete_post($resno, $pwd, $imgonly = 0, $automatic = 1, $children = 1, $die = 1, $allbyip = 1);
                break;
            case 'deleteimgonly':
                delete_post($resno, $pwd, $imgonly = 1, $automatic = 1, $children = 0, $die = 1);
                break;
            default:
                break;
        }

        $mysql->query('UPDATE ' . SQLLOG . " SET  $sqlValue=$sqlBool WHERE no='" . ((int) $no) . "' LIMIT 1");
        
        $temp = head($dat);
        $temp .= $verb . " thread $no. Redirecting...<META HTTP-EQUIV=\"refresh\" content=\"1;URL=" . PHP_ASELF_ABS . "\">";
        
        return $temp;
    }
}
    
?>
