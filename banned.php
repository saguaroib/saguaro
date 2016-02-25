<?php
//Ban status page, 25 lines or less edition! All HTML is in the bans class (bans.php).
require_once("config.php"); //Kinda uncomfortable about this. 

//Init SQL
require_once(CORE_DIR . "/mysql/mysql.php");
$mysql = new SaguaroMySQL;
$mysql->init();

//Init bans
require_once(CORE_DIR . "/admin/bans.php");
$ban  = new Banish;

//Init page class. repod whispers "finally" somehwere.
require_once(CORE_DIR . "/page/page.php");
$page = new Page;

$host = $_SERVER['REMOTE_ADDR'];

$status = $ban->isBanned($host); 				//Check if user is banned
$info = ($status) $ban->banInfo : "none"; 		//If ban exists in the table, get the information array. Otherwise, user isn't banned
$html = $ban->banScreen($info); 				//Returns all the html for banned.php from the ban class
echo $page->generate($html); 					//Page class outputs. 
$ban->append();									//Run checks to see if the ban needs to be updated and we're done here!

?>