<?php

/*

    Page generation class.

*/

class Page {
    public $headVars = [
        'page' => [
            'title' => '',
            'sub' => ''
        ],
        'css' => [
            'raw' => array(),
            'sheet' => array()
        ],
        'js' => [
            'raw' => array(),
            'script' => array()
        ]
    ];

    function generate($middle = '', $noHead = false) {
        return $this->head($noHead) . $middle . $this->foot();
    }

    function head($noHead) {
        require_once("head.php");
        $head = new Head;
        $head->info = $this->headVars;

        return $head->generate($noHead);
    }

    function foot() {
        require_once("foot.php");
        $footer = new Footer;

        return $footer->format();//strict standards prefers <- over -> Footer::format(); because it's STRICT
    }

}

?>
