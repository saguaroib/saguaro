<?php

class Modify {
    function mod($no, $action = 'none') {
        global $mysql;
        
		if (!valid('moderator')) error(S_NOPERM);
		
        $no = $mysql->escape_string($no);
        
        switch ($action) {
            case 'eventsticky':
                $sqlValue = "sticky";
                $rootnum  = "2027-07-07 00:00:00";
                $sqlBool  = "'2', modified='" . $rootnum . "'";
                $verb     = "Stuck (event mode) ";
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
				header("Location: index.html");
                break;
        }

        $mysql->query('UPDATE ' . SQLLOG . " SET  $sqlValue=$sqlBool WHERE no='" . ((int) $no) . "' LIMIT 1");
        
        $temp = head($dat);
        $temp .= $verb . " thread $no. Redirecting...<META HTTP-EQUIV=\"refresh\" content=\"3;URL=" . PHP_ASELF_ABS . "\">";
        
        return $temp;
    }
}
    
?>
