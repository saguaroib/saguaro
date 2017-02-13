<?php


class SaguaroClearReport {
    public $message;

    public function clear() {
        global $mysql;

        $post = [
            'no'    => (int) $_POST['no'],
            'board' => $mysql->escape_string($_POST['board']),
            'csrf'  => $_POST['token']
        ];

        if ($_SERVER['REQUEST_METHOD'] != 'POST' || !valid('report', $post['board'])) {
            $this->notify("error", "Invalid request");
        }

        $count = (int) $mysql->result("SELECT COUNT(no) FROM " . SQLREPORTS . " WHERE active='1' AND no='{$post['no']}' AND board='{$post['board']}'");
        if ($count < 1) {
            $this->notify("error", "Report has already been cleared or deleted");
        }

        $mysql->query("UPDATE " . SQLREPORTS . " SET active='0' WHERE no='{$post['no']}' AND board='{$post['board']}'");
        $this->notify("good", "cleared");

    }

    private function notify($status = "good", $message = '') {
        $ret = [
            'res' => $status, 
            'mes' => $message 
        ];

        header('Content-Type: application/json');
        echo json_encode($ret);
        exit();
    }
}