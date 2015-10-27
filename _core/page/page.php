<?php

/*

    Page generation class.

    Eventually move Head and Footer class into this folder.

*/

class Page {
    public $headVars = [
        'page' => [
            'title' => ''
        ],
        'css' => [
            'extra' => []
        ]
    ];
    
    function generate($middle = '') {
        return $this->head() . $middle . $this->foot();
    }
    
    function head() {
        require_once("head.php");
        $head = new Head;
        $head->info = $this->headVars;
        
        return $head->generate();
    }
    
    function foot() {
        require_once("foot.php");
        
        return Footer::format();
    }

}

?>