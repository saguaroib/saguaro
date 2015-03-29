<?php
/*	this method (ereg_replace...) didn't really worked, so i just changed it to the one you see in
here and the other tripcodes script. PROTIP: i will leave you the last one (the other 2 came with this
file), just go and try it out... you'll get a nice surprise xD

	$trip=ereg_replace("TJ9qoWuqvA","<b><font color=#FF0000>0</font>ne</b>",$trip);
 	$trip=ereg_replace("/.fjTeojqQ","Mr.Fluffy",$trip);
*/
	// If $cap is a staff password, use staff label
	if ($regtrip == ADMIN_PASS)
	{ $name	.= ACAPCODE; }
	else if ($sectrip == ADMIN_PASS)
	{ $name	.= ACAPCODE; }
	else if ($sectrip == MOD_PASS)
	{ $name	.= MCAPCODE; }
	else if ($regtrip == MOD_PASS)
	{ $name	.= MCAPCODE; }
	else if ($regtrip == 'triforcetripomg')
	{ $name     .= ' &nbsp;&nbsp;&#9650;&#11;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#9650;&nbsp;&#9650; ';}
	else if ($sectrip == 'triforcetripomg')
	{ $name     .= ' &nbsp;&nbsp;&#9650;&#11;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#9650;&nbsp;&#9650; ';}

?>nbsp;&#9650; ';}

?>