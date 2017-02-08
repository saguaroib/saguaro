<?php

require_once(CORE_DIR . "/admin/login.php");	//First line of security. Die script if user isn't logged in
$login = new Login;
$login->init();

switch($_GET['admin']) {
    default:
        echo "Hello!";
        break;
}