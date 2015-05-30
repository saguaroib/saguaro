<?php
/*	this method (ereg_replace...) didn't really worked, so i just changed it to the one you see in
here and the other tripcodes script. PROTIP: i will leave you the last one (the other 2 came with this
file), just go and try it out... you'll get a nice surprise xD

$trip=ereg_replace("TJ9qoWuqvA","<b><font color=#FF0000>0</font>ne</b>",$trip);
$trip=ereg_replace("/.fjTeojqQ","Mr.Fluffy",$trip);
*/
// If $cap is a staff password, use staff label
if ( $regtrip == ADMIN_PASS ) {
	$name .= ACAPCODE;
} else if ( $sectrip == ADMIN_PASS ) {
	$name .= ACAPCODE;
} else if ( $sectrip == MOD_PASS ) {
	$name .= MCAPCODE;
} else if ( $regtrip == MOD_PASS ) {
	$name .= MCAPCODE;
} else if ( $regtrip == 'triforcetripomg' ) {
	$name .= ' &nbsp;&nbsp;&#9650;&#11;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#9650;&nbsp;&#9650; ';
} /*else if ( $sectrip == 'triforcetripomg' ) {
	$name .= ' &nbsp;&nbsp;&#9650;&#11;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#9650;&nbsp;&#9650; ';
}*/

/*Setting up custom tripcodes

uncomment lines below and change the text to the desired output. 
The first changeme is what the tripcode you want to modify outputs. For example, a tripcode with the password #example outputs !6kgJ33pzx.
You would paste 6kgJ33pzx (without the leading !) in the first changeme, and whatever you want to display in the second changeme
*/

//$trip=ereg_replace("Ep8pui8Vw2","<font color=#FF0000>Raging Homo</font>",$trip);
//$trip=ereg_replace("CHANGEME","CHANGEME2",$trip);
//$trip=ereg_replace("CHANGEME","CHANGEME2",$trip);
//$trip=ereg_replace("CHANGEME","CHANGEME2",$trip);

?>
