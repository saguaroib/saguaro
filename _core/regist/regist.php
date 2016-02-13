<?php

/*

    Regist

    The most functionally important and hair removing part of the board software.
    Handles posting and uploads.

    Security for the future:
        MD5 hash the comment and use for checking duplicates (store otherwise).

*/

class Regist {
    private $cache = [
        'moderator' => 0
    ];
    
    function run() {
        $file = $_FILES["upfile"]["tmp_name"];
        $time = time(); $tim  = $time . substr(microtime(), 2, 3);
        
        $this->initialCheck(); //Run prelimary checks.
        $info = [ 
            'post' => $this->extractForm(), //Get the post info.
            'file' => (is_uploaded_file($file)) ? $this->extractFile($file,$tim) : [], //Get the file info and copy/rename to target directory.
            'host' => $_SERVER['REMOTE_ADDR']
        ];
        
        var_dump($info);
        
        //$this->insert($info);
        //Update the log and cache files.
    }
    
    private function insert($info) {
        global $mysql;
        $query = "INSERT INTO {SQLLOG} () VALUES ()";
    }
    
    private function updateCache() {
        
    }

    function initialCheck() {
        require_once('process/upload.php');
        $check = new UploadCheck;
        $check->run();
    }

    function extractForm() {
        //Default post information.
        $post = [
            'name' => (FORCED_ANON == false && $_POST['name']) ? $_POST['name'] : S_ANONAME,
            'subject' => (FORCED_ANON == false && $_POST['sub']) ? $_POST['sub'] : S_ANOTITLE,
            'email' => ($_POST['email']) ? $_POST['email'] : S_ANOTEXT,
            'comment' => ($_POST['com']) ? $_POST['com'] : S_ANOTEXT
        ];
        
        //Apply trip/capcodes to $post['name'].
        //All other magic required here.
        
        return $post;
    }
    
    function extractFile($file,$tim) {
        //Need the file's width/height, MD5 hash, and size.
        //Determine the file type and load the appropriate processor.

        require_once('process/upload_file.php');
        $check = new ProcessFile;
        return $check->run($file,$tim);
    }
}

$regist = new Regist;
$regist->run();

?>