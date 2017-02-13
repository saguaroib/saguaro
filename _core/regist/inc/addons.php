<?php

function parseComment($com, $email, $name) {
	//Parses comments for special features. Accepts email for #triggering some actions
	
	$com = dice($com, $email); //Dice roll
    $ret = fortune($com, $name);
	
	return [
        'com' => $ret['com'],
        'name' => $ret['name']
    ];
}

function userID($now) {
	if(DISP_ID == true){
		if($email)
			$id .= " (ID: Heaven)";
		else
			$id.=" (ID:".substr(crypt(md5($_SERVER["REMOTE_ADDR"].'id'.date("Ymd", $time)),'id'),+3) . ")";
		//Leave this escaped for storage
		$now .= "<span class=\"posteruid\" id=\"posterid\" style=\"border-radius:10px;font-size:8pt;\" />" . $id . "</span>";
	}
	return $now;
}

function fortune($com, $name) {
    if (FORTUNE_TRIP) {
        if (stripos($name, "#fortune") !== false) {
            require_once("fortune.php");
            $fortune = new Fortune;
            $com .= "<br><br>" . $fortune->giveFortune();
            $name = S_ANONAME;
        }
    }
    return [
        'com' => $com,
        'name' => $name
    ];
}

function dice($com, $email) {
	if(DICE_ROLL) {
		if ($email) {
			if (preg_match("/dice[ +](\\d+)[ d+](\\d+)(([ +-]+?)(-?\\d+))?/", $email, $match)) {
				$dicetxt = "Rolled ";
				$dicenum = min(25, $match[1]);
				$diceside = $match[2];
				$diceaddexpr = $match[3];
				$dicesign = $match[4];
				$diceadd = intval($match[5]);
				
				for ($i = 0; $i < $dicenum; $i++) {
					$dicerand = mt_rand(1, $diceside);
					if ($i) $dicetxt .= ", ";
					$dicetxt .= $dicerand;
					$dicesum += $dicerand;
				}
				
				if ($diceaddexpr) {
					if (strpos($dicesign, "-") > 0) $diceadd *= -1;
					$dicetxt .= ($diceadd >= 0 ? " + " : " - ").abs($diceadd);
					$dicesum += $diceadd;
				}
				
				$dicetxt .= " = $dicesum<br /><br />";
				$com = "<strong>$dicetxt</strong>" . $com;
				
				return $com;
			}
		}
	}
	return $com;
}