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

class Catalog {
    private $data = [];

    function formatPage() {     
        require_once(CORE_DIR . "/page/page.php");
        $page = new Page;
        $page->headVars['page']['title'] = "/" . BOARD_DIR . "/ - " . TITLE . " - Catalog";
        array_push($page->headVars['css']['extra'], "stylesheets/catalog.css");
        
        return $page->generate($this->format());
    }

    function format() {
        global $my_log;

        $my_log->update_cache();
        $log = $my_log->cache;
        $temp = "";

        $this->parseOPs();
        $this->parseReplies();
        $this->sortOPs();

        $this->data = array_reverse($this->data); //Eh.

        foreach ($this->data as $entry) {
            $temp .= $this->generateOP($log[$entry['no']],$entry);
        }

        $temp = "<div class='catalog_container'>" . $temp . "</div>";

        return $temp;
    }
    function sortOPs(/*$method = null*/) {
        /*
            Might not need this later if we change to a cached jQuery environment.
            Does not sage/autosage into consideration.
        */

        $method = "last"; //($method) ? $method : "last"; //Default to "last".

        //Scope please. Why can't I just $method!
        function last_compare($a, $b) {
            if ($a['last'] == $b['last']) { return 0; }
            return ($a['last'] < $b['last']) ? -1 : 1;
        }
        function images_compare($a, $b) {
            if ($a['images'] == $b['images']) { return 0; }
            return ($a['images'] < $b['images']) ? -1 : 1;
        }
        function replies_compare($a, $b) {
            if ($a['replies'] == $b['replies']) { return 0; }
            return ($a['replies'] < $b['replies']) ? -1 : 1;
        }

        usort($this->data, $method . "_compare");
    }
    function generateOP($input,$stats) {
        $post = new Post;

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
                    'last' => $entry['no']
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