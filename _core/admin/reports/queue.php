<?php


class ReportQueue {
    
    public function queueJSON() {
        global $mysql;
        if (!$list = valid("boardlist")) 
            error(S_NOPERM);
        
        $list = explode(",", $list);

        if ($_GET['board']) {
            $board = $mysql->escape_string($_GET['board']);
            if (!in_array($board, $list))//User is requesting reports for an invalid board
                return json_encode(array("res" => "no"));
        
            $query = $mysql->query("SELECT no,illegal,rule,post FROM " . SQLREPORTS . " WHERE board='{$board}' AND type>0");
        } else {
            foreach ($list as $board) {
                $querstr .= "board='{$board} OR "
            }
            $querstr = substr($querstr, 0, -3);

            $query = $mysql->query("SELECT no,illegal,rule,post,board FROM " . SQLREPORTS . " WHERE ({$querstr}) AND type>0");
        }
        
        $reports = array();
        $queue = array("res" => "good", "posts" => $reports);
        
        while ($row = $mysql->fetch_row($query)) {
            $data = array(
                "no" => $row['no'],
                "post" => $row['post'],
                "illegalcount" => $row['illegal'],
                "illegalcount" => $row['illegal'],
                "board" => $row['board']
            );
            array_push($reports, $data);
        }
        
    }
    
    public function 
}