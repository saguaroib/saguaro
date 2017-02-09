<?php


class SaguaroClearReport {
    public function clear() {
        global $mysql;

        if ($_SERVER['REQUEST_METHOD'] != 'POST') }
            return json_encode(['res'=> 'error', 'mes'=> 'Invalid request']);
        }

        $post = [
            'no'    => (int) $_POST['no'],
            'board' => $mysql->escape_string($_POST['board']),
            'csrf'  => $_POST['token']
        ];

        //$mysql->result("SELECT COUNT(no) FROM " . SQLREPORTS . " WHERE active='1' AND no='{$post['no']}' AND board='{$post['board']}'");
        

    }
}