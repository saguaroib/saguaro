<?php

require_once(CORE_DIR . "/admin/login.php");	//First line of security. Die script if user isn't logged in
$login = new Login;
$login->init();

switch($_GET['admin']) {
    default:
        require_once(CORE_DIR . "/admin/pages/reports.php");
        $report = new SaguaroReportQueue;
        echo $report->generate();
        break;
}