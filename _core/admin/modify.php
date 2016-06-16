<?php

class Modify {
    function mod() {
        global $mysql, $csrf;
        
        $no = (is_numeric($_GET['no'])) ? (int) $_GET['no'] : error("Invalid post");
        
		if (!valid('moderator')) error(S_NOPERM);
        if (!$csrf->validate()) error(S_RELOGIN);

        switch ($_GET['action']) {
            case 'eventsticky':
                $sqlValue = "sticky";
                $rootnum  = "2027-07-07 00:00:00";
                $sqlBool  = "'2', modified='" . $rootnum . "'";
                $verb     = "Stuck (cylical) ";
                break;
            case 'sticky':
                $sqlValue = "sticky";
                $rootnum  = "2027-07-07 00:00:00";
                $sqlBool  = "'1', modified='" . $rootnum . "'";
                $verb     = "Stuck";
                break;
            case 'unsticky':
                $sqlValue = "sticky";
                $rootnum  = date('Y-m-d G:i:s');
                $sqlBool  = "'0', modified='" . $rootnum . "'";
                $verb     = "Unstuck";
                break;
            case 'lock':
                $sqlValue = "locked";
                $sqlBool  = "'1', modified=modified ";
                $verb     = "Locked";
                break;
            case 'unlock':
                $sqlValue = "locked";
                $sqlBool  = "'0', modified=modified ";
                $verb     = "Unlocked";
                break;
            case 'permasage':
                $sqlValue = "permasage";
                $sqlBool  = "'1', modified=modified ";
                $verb     = "Autosaging";
                break;
            case 'nopermasage':
                $sqlValue = "permasage";
                $sqlBool  = "'0', modified=modified ";
                $verb     = "Normally bumping";
                break;
            default:
				echo "Error!";
                break;
        }

        $mysql->query('UPDATE ' . SQLLOG . " SET  $sqlValue=$sqlBool WHERE no='{$no}' LIMIT 1");

        $temp = "{$thread} thread {$no}";
        $temp = json_encode($temp);
        return $temp;
    }
}
    
?>
