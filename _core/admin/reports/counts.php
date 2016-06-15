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
        $illegalCount = $this->buildQuery($illegal = true);
        return $mysql->result($illegalCount);
    }

    public function userSummary($boards = null) { //Counts number active reports on all owned boards.
        global $mysql;
        if ($boards) { //We've already determined the user's permitted boards somewhere else. Let's use that.
            $illegalCount = $this->buildQuery($illegal = true, $boards);
            $ruleCount = $this->buildQuery();
        } else { //Gotta determine what boards the user has permission for!
            $valid = valid("boardlist"); //Returns comma delimited string of boards
            if (!$valid) {
                error(S_NOPERM); //User isn't assinged to any boards. what a loser
            }
            
            $illegalCount = $this->buildQuery($illegal = true, $boards = $valid);
            $ruleCount = $this->buildQuery();
        }
        
        return [
            'illegal' => $mysql->result($illegalCount),
            'rules' => $mysql->result($ruleCount)
        ];
    }

    public function globalJSON() {
        if (valid("global")) {
            $arr = array("status" => "ok", "results" => array("illegal" => $this->globalSummary()));
        } else {
            $arr = array("status" => "no");
            return json_encode($arr);
        }
    }

    public function userJSON() {
        if ($valid = valid("boardlist")) {
            $count = $this->userSummary($valid);
            $arr = array("status" => "ok", "results" => array("rules" => $count['rules'], "illegal" => $count['illegal']));
        } else {
            $arr = array("status" => "no");
            return json_encode($arr);
        }
    }

    private function buildQuery($illegal = false, $boards = false) {
        $type = ($illegal) ? "global='1'" : "type>0 AND type<3";
        
        $query = "SELECT COUNT(no) FROM " . SQLREPORTS . " WHERE {$type}";
        
        if ($boards) {
            $boards = explode(",", $boards);
            foreach ($boards as $board) {
                $append .= "board='{$board}' OR ";
            }
            $append = substr($append, 0, -3);
            $query = $query . " AND (" . $append . ")";
        }
        
        return $query;
    }
}