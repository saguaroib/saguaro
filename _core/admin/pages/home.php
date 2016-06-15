<?php

class AdminHomePage {
    
    function display() { //Initial display for admin.php
        global $mysql, $page;

        $temp .= "<div class='managerBanner'>" . S_LANDING . "</div>";

        $user = $mysql->escape_string($_COOKIE['saguaro_auser']);
        $password = $mysql->escape_string($_COOKIE['saguaro_apass']);
        $boards = $mysql->result("SELECT boards FROM " . SQLMODSLOG . " WHERE username='" . $user . "' AND password='" . $password . "' LIMIT 1");

        $page->headVars['page']['title'] = SITE_ROOT . " - Mod panel";
        $page->headVars['page']['sub'] = "Welcome back, {$user}!";
        $page->headVars['css']['sheet'] = array("/stylesheets/admin/panel.css");
        $page->headVars['js']['script'] = array("panel.js");

        require_once(CORE_DIR . "/admin/reports/counts.php");
        $repCounts = new ReportCounts;
        
        $temp = ($boards == "*") ? $this->globalBoardlist() : $this->userBoardlist($boards);
        
        return $temp;
    }

    private function globalBoardlist() { //Displays the board list for 
        global $page;
        require_once(CORE_DIR . "/admin/reports/counts.php");
        $repCounts = new ReportCounts;
        
        $temp .= "<div class='container panel' id='manageBoard' ><div class='header'>Manage a board:</div>";
        $temp .= '<center><div style="padding: 10px;"><form action="' . PHP_ASELF . '" method="get" id="delForm"><input type="hidden" name="mode" value="overview">
                <input type="text" name="b" placeholder="Board URI (No slashes)"><input type="submit" value="Go"></form></div></div></center>';
        $temp .= "</table></center>";
        
        $count = (int) $repCounts->globalSummary();
        if ($count > 0) {
            $rep = ($count > 1) ? "reports" : "report";
            $summary = "<div class='hasReports centered'>{$count} active {$rep}! [<a href='" . PHP_ASELF . "?mode=queue'>Visit report queue</a>]</div>";
            $page->headVars['page']['title'] = "({$count}) " . $page->headVars['page']['title'];
        } else {
            $summary = "<div class='noReports centered'>No active reports</div>";
        }
        $temp .= "<div class='container panel'><div class='header'>Global report summary:</div>{$summary}</div>";
        $temp .= "</div>";

        return $temp;
    }
    
    private function userBoardlist($boards) {
        global $page;
        require_once(CORE_DIR . "/admin/reports/counts.php");
        $repCounts = new ReportCounts;
        $counts = $repCounts->userSummary($boards);
        
        //$temp .= "<div class='container panel'  id='manageBoard' ><!---<div class='header'>Your boards:</div>--->";
        $temp .= "<center><table class='boards container'><tr class='header'><th class='postblock'>Visit board:</th><th class='postblock'>Settings</th><th class='postblock'>Summary</th></tr>";
        $boards = explode(",", $boards);
        foreach($boards as $board) {
            $temp .= "<tr class='centered' style='font-size:medium;'><td>/<a href='/{$board}' target='_blank' >{$board}</a>/</td><td>[<a href='" . PHP_ASELF . "?mode=settings&b={$board}'>Change settings</a>]</td><td>[<a href='" . PHP_ASELF . "?mode=overview&b={$board}'>View stats</a>]</td></tr>";
        }
        
        $rcount = (int) $counts['rules'];
        $icount = (int) $counts['illegal'];
        $total = $rcount + $icount;

        if ($total > 0) {
            $rep = ($total > 1) ? "reports" : "report";
            $summary = "<div class='hasReports centered'>{$total} active {$rep}! (Rule violations: {$rcount} / Illegal: {$icount}) [<a href='" . PHP_ASELF . "?mode=queue'>Visit report queue</a>]</div>";
            $page->headVars['page']['title'] = "({$total}) " . $page->headVars['page']['title'];
        } else {
            $summary = "<div class='noReports centered'>No active reports</div>";
        }
        $temp .= "<tr><td colspan='3'>{$summary}</td></tr>";
        $temp .= "</table></center></div>";
        return $temp;
    }
}