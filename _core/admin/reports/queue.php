<?php

ini_set('display_errors', "Off");
error_reporting(~E_NOTICE);

class SaguaroRQData {
    public function queueJSON() {
        global $mysql;
        
        header('Content-Type: application/json');
        
        $list = valid('boardlist');
        
        if (!valid('janitor')) { //User is requesting reports for an invalid board
            echo json_encode(["res" => "error", "mes" => "Invalid permission."]);
            return;
        }
        
        if ($_GET['board']) { //Requesting for a specific board. What a polite user.
            $board = $mysql->escape_string($_GET['board']);
            if (!in_array($board, $list)) { //User is requesting reports for an invalid board
                echo json_encode(["res" => "error", "mes" => "Invalid permission."]);
                return;
            }
        
            $query = $mysql->query("SELECT * FROM `" . SQLREPORTS . "` WHERE board='{$board}' AND post<>'' AND active='1' ORDER BY global DESC");
        } else { //Requesting queue for every board.
            foreach ($list as $board) {
                $querstr .= "OR board='{$board}'";
                if ($board === "*") $querstr = "OR 1";
            }
            $query = $mysql->query("SELECT * FROM `" . SQLREPORTS . "` WHERE (0 {$querstr}) AND post<>'' AND active='1' ORDER BY global DESC");
        }
        
        $reports = [];
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
        echo json_encode($queue);
        return;
    }
}