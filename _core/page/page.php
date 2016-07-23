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
            'raw' => array(), //Raw css to push to <style> tags
            'sheet' => array() ////Names of css files to include
        ],
        'js' => [
            'raw' => array(), //Raw js to push to <script> tags
            'script' => array() //Names of js files to include
        ],
        /*'meta' => [ //Meta tags for the page
            'attribs' => array() //Associative array, attribute => value
        ],*/
        'ribbon' => [ //[Navigation] ribbon items
            'item' => array()
        ]
    ];

    function generate($middle = '', $noHead = false, $admin = false) {
        return $this->head($noHead, $admin) . $middle . $this->foot($noHead, $admin);
    }

    function head($noHead, $admin) {
        require_once("head.php");
        $head = new Head;
        $head->info = $this->headVars;

        return $head->generate($noHead, $admin);
    }

    function foot($noFoot, $admin) {
        require_once("foot.php");
        $footer = new Footer;
        $footer->info['ribbon'] = $this->headVars['ribbon']; 

        return $footer->format($noFoot, $admin);//strict standards prefers <- over -> Footer::format(); because it's STRICT
    }

}

?>
