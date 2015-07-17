<?php
/*

    Generates a listing of OPs.

    The class you didn't know you wanted.
    Don't try this at home.

*/

require("post.php");

class Catalog {
    private $data = [];

    function format() {
        global $log;

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
    function sortOPs($method) {
        /*
            Might not need this later if we change to a cached jQuery environment.
            Does not sage/autosage into consideration.
        */

        $method = ($method) ? $method : "last"; //Default to "last".

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
        global $log;

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
        global $log;

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