<?php
switch($_GET['report']) {  
    case 'get':
        require_once(CORE_DIR . "/admin/reports/queue.php");
        $rep = new SaguaroRQData;
        $rep->queueJSON();
        break;
	default:
		require_once(CORE_DIR . "/admin/reports/report.php");
        $report = new SaguaroReports;
        echo $report->init();
		break;
}