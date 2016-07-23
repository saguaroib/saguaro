<?php

/*
    This class does the following:
    - Counts active reports for specific boards
    - Counts active reports for the global queue
    - Returns JSON summary for all active reports on a board a user owns
    - Returns JSON summary for all active reports for the global queue
*/

class ReportCounts {

    public function globalSummary() { //Count number illegal reports for global staff.
        global $mysql;
        $count = (int) $mysql->result("SELECT COUNT(no) FROM " . SQLMODSLOG . " WHERE global='1' AND active='1' AND post<>''");
        return $count;
    }

    public function userSummary() { //Counts number active reports on all owned boards.
        global $mysql;
        
        $boards = $this->buildQuery();
        
        $count = (int) $mysql->result("SELECT COUNT(no) FROM " . SQLREPORTS . " WHERE ({$boards}) AND active='1' AND post<>'' ");
        $illegalCount = (int) $mysql->result("SELECT COUNT(no) FROM " . SQLREPORTS . " WHERE ({$boards}) AND global=1 AND active=1 AND post<>'' ");

        $count = ($count - $illegalCount);
        
        return [
            'illegal' => $illegalCount,
            'rules' => $count
        ];
    }

    public function globalJSON() {      
        $arr = (valid('global')) ? array("status" => "ok", "results" => array("illegal" => $this->globalSummary())) : array("status" => "no");
        
        return json_encode($arr);
    }

    public function userJSON() {
        if ($valid = valid("user")) {
            $count = $this->userSummary();
            $arr = array("status" => "ok", "results" => array("rules" => $count['rules'], "illegal" => $count['illegal']));
        } else {
            $arr = array("status" => "no");
        }

        return json_encode($arr);
    }

    private function buildQuery() {
        
        $boards = valid('boardlist');
        
        $boards = explode(",", $boards);
        foreach ($boards as $board) {
            $query .= "board='{$board}' OR ";
        }
        $query = substr($query, 0, -3);
        
        return $query;
    }
}