<?php

/*

    Page generation class.

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

    function generate($middle = '', $admin = 0, $noHead = 0) {
        return $this->head($admin, $noHead) . $middle . $this->foot();
    }

    function head($admin, $noHead) {
        require_once("head.php");
        $head = new Head;
        $head->info = $this->headVars;
        
        return ($admin) ? $head->generateAdmin($noHead) : $head->generate();
    }

    function foot() {
        require_once("foot.php");
        $footer = new Footer;
		
        return $footer->format();//strict standards prefers <- over -> Footer::format(); because it's STRICT
    }

}

?>
