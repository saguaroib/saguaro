<?php


class SaguaroReportQueue {
    
    
    public function generate() {
        global $mysql;
        
        require_once(CORE_DIR . "/page/page.php");
        $page = new Page;
        $page->headVars['page']['title'] = "/" . BOARD_DIR . "/ - " . TITLE . " - Report Queue";
        $repArr = "var reportArr = " . $this->queueJSON();
        $page->headVars['js']['raw'] = array($repArr);
        $page->headVars['js']['script'] = array("reportqueue.js");
        $page->headVars['css']['sheet'] = (NSFW) ? array("/stylesheets/admin/nwspanel.css", "/stylesheets/admin/reportqueue.css") : array("/stylesheets/admin/wspanel.css", "/stylesheets/admin/reportqueue.css");

        $list = explode(",", valid('boardlist'));
        
        foreach ($list as $board) {
            $boards = "<span class='button' data-cmd='toggle' data-board='{$board}'>/{$board}/</span> ";
        }

        $dat .= "<div class='banner centered'>Filter board: <span class='button' data-cmd='toggle' data-board='all'>All</span> {$boards}</div>";
        $dat .= "<div class='content' id='reports'></div>";
        
        return $page->generate($dat, $noHead = false, $admin = true);
    }
    
    public function queueJSON() {
        global $mysql;
        
        $list = explode(",", valid('boardlist'));

        if ($_GET['board']) { //Requesting for a specific board. What a polite user.
            $board = $mysql->escape_string($_GET['board']);
            if (!in_array($board, $list))//User is requesting reports for an invalid board
                return json_encode(array("res" => "no"));
        
            $query = $mysql->query("SELECT * FROM " . SQLREPORTS . " WHERE board='{$board}' AND post<>'' AND active='1' ORDER BY global DESC");
        } else { //This asshole is sucking up all our memory and requesting for every board they own
            foreach ($list as $board) {
                $querstr .= "board='{$board}' OR ";
            }
            $querstr = substr($querstr, 0, -3);
            $query = $mysql->query("SELECT * FROM " . SQLREPORTS . " WHERE ({$querstr}) AND post<>'' AND active='1' ORDER BY global DESC");
        }
        
        $reports = array();
        $j = 0;

        while ($row = $mysql->fetch_assoc($query)) {
            $data = array(
                "no" => (int) $row['no'],
                "post" => $row['post'],
                "rule_count" => (int) $row['rule_count'],
                "spam_count" => (int) $row['spam_count'],
                "illegal_count" => (int) $row['illegal_count'],
                "cp_count" => (int) $row['cp_count'],
                "board" => $row['board']
            );
            array_push($reports, $data);
        }
        $queue = array("res" => "good", "posts" => $reports);

        return json_encode($queue);
    }
}