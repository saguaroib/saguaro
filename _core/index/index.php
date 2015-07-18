<?php

/*

    Generate an index page.
    
    Need to do something about the parsing and sorting functions from Catalog.
    
    $sample = new Catalog;
    $sample->format(); //Will default to 1 and generate the first page index.
    $sample->format(2); //Will generate the second page index.

*/

require_once("_core/thread/thread.php"); //PHP_SELF is imgboard.php so we can't relatively include it "../thread/thread.php".

class Index {
    private $data = [];
    
    public function format($page_no) {
        global $log;
        $page_no = (is_numeric($page_no) && $page_no > 0) ? $page_no : 1; //Short circuits when.
        if ($page_no > PAGE_MAX) $page_no = PAGE_MAX;
        $temp = "";

        $this->formatRange($page_no);

        foreach ($this->data as $resno) {
            $temp .= "<div style='display:table'>" . $this->generateThread($resno["no"]) . "</div><hr>";
        }
        
        return $temp;
    }
    private function formatRange($page_no) {
        global $log;
        $temp = [];
        $min = ($page_no - 1) * PAGE_DEF;
        $max = $page_no * PAGE_DEF;

        $this->parseOPs();
        $this->parseReplies();
        
        function last_compare($a, $b) {
            if ($a['last'] == $b['last']) { return 0; }
            return ($a['last'] > $b['last']) ? -1 : 1;
        }
        
        usort($this->data, "last_compare");

        $this->data = array_slice($this->data, $min, $max);
    }
    private function generateThread($resno) {
        $temp = new Thread;
        $temp->inIndex = true;

        return $temp->format($resno);
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