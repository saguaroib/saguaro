<?php

class SaguaroReportQueue {
    
    
    public function generate() {
        global $mysql;
        
        require_once(CORE_DIR . "/page/page.php");
        $page = new Page;
        $page->headVars['page']['title'] = "/" . BOARD_DIR . "/ - " . TITLE . " - Report Queue";
        /*$repArr = "var reportArr = " . $this->queueJSON();
        $page->headVars['js']['raw'] = [$repArr];*/
        $page->headVars['js']['script'] = ["admin/reportqueue.js"];
        $page->headVars['css']['sheet'] = (NSFW) ? ["/stylesheets/admin/nwspanel.css", "/stylesheets/admin/reportqueue.css"] : ["/stylesheets/admin/wspanel.css", "/stylesheets/admin/reportqueue.css"];

       /* $list = valid('boardlist');
        
        foreach ($list as $board) {
            $boards = "<span class='button' data-cmd='toggle' data-board='{$board}'>/{$board}/</span> ";
        }*/

        //$dat .= "<div class='banner centered'>Filter board: <span class='button' data-cmd='toggle' data-board='all'>All</span> {$boards}</div>";
        $dat .= "<div class='banner centered'>Showing reports for this board.</div>";
        $dat .= "<div class='content' id='reports'></div>";
        
        return $page->generate($dat, $noHead = false, $admin = true);
    }
}