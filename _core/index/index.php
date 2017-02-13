<?php

/*

    Generate an index page.

    Need to do something about the parsing and sorting functions from Catalog.

    $sample = new Catalog;
    $sample->format(); //Will default to 1 and generate the first page index.
    $sample->format(2); //Will generate the second page index.

    Surely the sticky sorting is optimized.

*/

require_once(CORE_DIR . "/thread/thread.php");

class Index {
    private $data = [];
    public $thread_cache = [];

    public function format($page_no = 1 ,$counttree = 0, $cacheThreads = false) {
        global $my_log;
        $my_log->update_cache();

        $page_no = (is_numeric($page_no) && $page_no > 0) ? $page_no : 1; //Short circuits when.
        if ($page_no > PAGE_MAX) $page_no = PAGE_MAX;
        $temp = "";

        $this->formatRange($page_no);

        foreach ($this->data as $resno) {
            //Eventually remove display:table, disgusting.
            $cache = "<div style='display:table;clear:both;width:100%;'>" . $this->generateThread($resno["no"]) . "</div><hr>";
            if ($cacheThreads) $this->thread_cache[$resno["no"]] = $cache; //Put in thread cache for use of parent classes (instead of regenerating threads).
            $temp .= $cache;
        }

        $temp .= $this->foot($page_no);

        return $temp;
    }

    public function foot($st) {
        global $my_log;

        $dat = '';
        $currentpage = $st;
        $totalpages = ceil(count($my_log->cache['THREADS']) / PAGE_DEF); //Hardcoded, is there a reason to make this configurable?

        //Previous
        $dat .= '<table align="left" border="1" class="pages"><tr>';
        if ($currentpage > 1) {
            $url = ($currentpage > 2) ? ($currentpage - 1) . PHP_EXT : PHP_SELF2;
            $dat .= "<form action='$url' method='get'><td>";
            $dat .= '<input type="submit" value="' . S_PREV . '" />';
            $dat .= '</td></form>';
        } else {
            $dat .= "<td>" . S_FIRSTPG . "</td>";
        }

        //Page numbers
        $dat .= "<td>";
        for ($page = 1; $page <= $totalpages; $page++) {
            $temp = ($page == $currentpage) ? "<strong>$page</strong>" : $page;
            $url = ($page > 1) ? ($page - 1) . PHP_EXT : PHP_SELF2;
            $dat .= "[<a href='$url'>$temp</a>] ";
        }
        $dat .= "</td>";

        //Next
        if ($currentpage < $totalpages) {
            $dat .= "<td><form action='$currentpage" . PHP_EXT . "' method='get'>";
            $dat .= '<input type="submit" value="' . S_NEXT . '" />';
            $dat .= '</form></td>';
        } else {
            $dat .= "<td>" . S_LASTPG . "</td>";
        }
        $dat .= '</tr></table><br clear="all">';

        return $dat;
    }

    private function formatRange($page_no) {
        $temp = [];
        $min = ($page_no - 1) * PAGE_DEF;
        $max = $page_no * PAGE_DEF;

        $this->parseOPs();
        $this->parseReplies();
        $this->parseStickies();

        if (!function_exists("last_compare")) {
            function last_compare($a, $b) {
                if ($a['last'] == $b['last']) { return 0; }
                return ($a['last'] > $b['last']) ? -1 : 1;
            }
        }

        usort($this->data['sticky'], "last_compare");
        usort($this->data['unsticky'], "last_compare");
        $this->data = array_merge($this->data['sticky'], $this->data['unsticky']);

        $this->data = array_slice($this->data, $min, $max);
    }
    private function generateThread($resno) {
        $temp = new Thread;
        $temp->inIndex = true;

        return $temp->format($resno);
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
    private function parseStickies() {
        //There's probably a better way to do this.
        $temp = ["sticky" => [], "unsticky" => []];

        foreach ($this->data as $thread) {
            $sticky = ($thread['sticky']) ? 'sticky': 'unsticky';
            array_push($temp[$sticky], $thread);
        }

        $this->data = $temp;
    }
}