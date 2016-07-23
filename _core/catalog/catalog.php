<?php
/*

    Generates a listing of OPs.

    The class you didn't know you wanted.
    Don't try this at home.
    
    case 'catalog':
        require_once(CORE_DIR . "/catalog/catalog.php");
        $catalog = new Catalog;
        echo $catalog->formatPage();
        break;

*/

require("post.php");

class Catalog extends Log {
    private $data = [];

    function formatPage($static = false) {     
        require_once(CORE_DIR . "/page/page.php");
        $page = new Page;
        $page->headVars['page']['title'] = "/" . BOARD_DIR . "/ - " . TITLE . " - Catalog";
        //if ($static !== true) array_push($page->headVars['js']['script'], "catalog.js");
        array_push($page->headVars['css']['sheet'], "/stylesheets/catalog.css");
        $out = $page->generate($this->format($static));
        
        $this->print_page("catalog" . PHP_EXT, $out, 0);
    }

    function format() {
        global $my_log;

        $my_log->update_cache();
        $log = $my_log->cache;
        $temp = "";

        $this->parseOPs();
        $this->parseReplies();
        $this->sortOPs();

        foreach ($this->data as $entry) {
            $temp .= $this->generateOP($log[$entry['no']],$entry);
        }
        
        require_once(CORE_DIR . "/postform.php");
        $form = new PostForm;
        $temp = $form->format();

        $temp .= "<div class='catalog_container'>" . $temp . "</div>";

        return $temp;
    }
    function sortOPs() {
        //Seperate stickies from non-stickies to process further.
        $temp = ['sticky' => [], 'regular' => []];
        
        foreach ($this->data as $op) {           
            if ($op['sticky'])
                array_push($temp['sticky'],$op);
            else
                array_push($temp['regular'],$op);
        }

        usort($temp['regular'], function($a, $b) {
            if ($a['last'] == $b['last']) { return 0; }
            return ($a['last'] > $b['last']) ? -1 : 1;
        });
        
        //Additional sticky processing...?
        
        $this->data = array_merge($temp['sticky'], $temp['regular']);
    }
    function generateOP($input,$stats) {
        $post = new CatalogPost;

        return $post->format($input,$stats);
    }
    private function parseOPs() {
        global $my_log;
        $log = $my_log->cache;

        //Pick out OPs.
        foreach ($log as $entry) {
            if ($entry['no'] && $entry['resto'] == 0)
                $this->data[$entry['no']] = [
                    'no' => $entry['no'],
                    'replies' => 0,
                    'images' => 0,
                    'last' => $entry['no'],
                    'sticky' => $entry['sticky']
                ];
        }
    }
    private function parseReplies() {
        global $my_log;
        $log = $my_log->cache;

        //Assign reply stats.
        foreach ($log as $entry) {
            $rto = $entry['resto'];
            if ($rto > 0) {
                $this->data[$rto]['last'] = $entry['no'];
                $this->data[$rto]['replies']++;
                if ($entry['fname'])
                    $this->data[$rto]['images']++;
            }
        }
    }
}

?>
