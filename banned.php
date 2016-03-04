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
$page->headVars['page']['title'] = "You are not banned!";

$host = $mysql->escape_string($_SERVER['REMOTE_ADDR']);

$html = $ban->banScreen($host); 				//Returns all the html for banned.php from the ban class
echo $page->generate($html); 					//Page class outputs. 

?>