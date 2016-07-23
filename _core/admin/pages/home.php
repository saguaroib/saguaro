<?php

class SaguaroAdminHome {
    
    public function generate() { //Initial display for admin.php
        global $mysql;

        require_once(CORE_DIR . "/page/page.php");
        $page = new Page;
        require_once(CORE_DIR . "/admin/reports/counts.php");
        $repCounts = new ReportCounts;
        
        $temp .= "<div class='managerBanner'>" . S_LANDING . "</div>";

        $user = $mysql->escape_string($_COOKIE['saguaro_auser']);
        $password = $mysql->escape_string($_COOKIE['saguaro_apass']);
        $boards = valid('boardlist');

        $page->headVars['page']['title'] = SITE_ROOT . " - Mod panel";
        $page->headVars['page']['sub'] = "Welcome back, {$user}!";
        $page->headVars['css']['sheet'] = (NSFW) ? array("/stylesheets/admin/nwspanel.css", "/stylesheets/admin/settings.css") : array("/stylesheets/admin/wspanel.css", "/stylesheets/admin/settings.css");
        $page->headVars['js']['script'] = array("panel.js");

        
        
        $temp = ($boards == "*") ? $this->globalBoardlist() : $this->userBoardlist($boards);
        
        return $page->generate($temp, false, true);
    }

    private function globalBoardlist() { //Displays the board list for 
        global $page;
        require_once(CORE_DIR . "/admin/reports/counts.php");
        $repCounts = new ReportCounts;
        
        $temp .= "<div class='container panel' id='manageBoard' ><div class='header'>Manage a board:</div>";
        $temp .= '<div style="padding: 10px;"><form action="' . PHP_ASELF . '" method="get" id="delForm"><input type="hidden" name="mode" value="overview">
                <input type="text" name="b" placeholder="Board URI (No slashes)"><input type="submit" value="Go"></form></div></div></center>';
        $temp .= "</table>";
        
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
        
        $temp .= "<div class='inline'  id='manageBoard' ><!---<div class='header'>Your boards:</div>--->";
        $temp .= "<table class='boards container centered'><tr class='header'><th class='postblock'>View board index</th><th class='postblock'>Visit admin panel</th><th class='postblock' colspan='1'>Options</th></tr>";
        $boards = explode(",", $boards);
        $perms = valid('allperms');

        foreach($boards as $board) {
            $mode = ($perms[BOARD_DIR]['settings']) ? "settings" : "reports";
            $modemsg = ($perms[BOARD_DIR]['settings']) ? "Change settings" : "Visit report queue";
            
            $temp .= "<tr class='centered' style='font-size:medium;'><td>/<a href='/{$board}' target='_blank' >{$board}</a>/</td><td>[<a href='" . DATA_SERVER. $board . "/" . PHP_SELF . "?mode=admin'>Jump to panel</a>]</td><td>[<a href='" . DATA_SERVER. $board . "/" . PHP_SELF . "?mode=admin&admin={$mode}'>{$modemsg}</a>]</td></tr>";
        }
        
        $rcount = (int) $counts['rules'];
        $icount = (int) $counts['illegal'];
        $total = $rcount + $icount;

        if ($total > 0) {
            $rep = ($total > 1) ? "reports" : "report";
            $summary = "<div class='hasReports centered'>{$total} active {$rep}! (Rule violations: {$rcount} / Illegal: {$icount}) [<a href='" . PHP_SELF_ABS . "?mode=admin&admin=reports'>Visit report queue</a>]</div>";
            $page->headVars['page']['title'] = "({$total}) " . $page->headVars['page']['title'];
        } else {
            $summary = "<div class='noReports centered'>No active reports for any of your boards</div>";
        }
        $temp .= "<tr><td colspan='3'>{$summary}</td></tr>";
        $temp .= "</table></div>";
        return $temp;
    }
}