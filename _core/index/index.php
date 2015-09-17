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

    public function format($page_no = 1 ,$counttree = 0) {
        global $log;

        $page_no = (is_numeric($page_no) && $page_no > 0) ? $page_no : 1; //Short circuits when.
        if ($page_no > PAGE_MAX) $page_no = PAGE_MAX;
        $temp = "";

        $this->formatRange($page_no);

        foreach ($this->data as $resno) {
            //Eventually remove display:table, disgusting.
            $temp .= "<div style='display:table;clear:both;width:100%;'>" . $this->generateThread($resno["no"]) . "</div><hr>";
        }
        
        $temp .= $this->foot($page_no, $counttree);

        return $temp;
    }
    
    public function foot($st, $counttree) {
        $dat = '';
        $prev = $st - PAGE_DEF;
        $next = $st + PAGE_DEF;

        //  Page processing
        $dat .= '<table align="left" border="1" class="pages"><tr>';
        if ($prev >= 0) {
            if ($prev == 0) {
                $dat .= '<form action="' . PHP_SELF2 . '" method="get" /><td>';
            } else {
                $dat .= '<form action="' . $prev / PAGE_DEF . PHP_EXT . '" method="get"><td>';
            }
            $dat .= '<input type="submit" value="' . S_PREV . '" />';
            $dat .= "</td></form>";
        } else {
            $dat .= "<td>" . S_FIRSTPG . "</td>";
        }

        //Page listing.
        $dat .= "<td>";
        for ($i = 0; $i < $counttree; $i += PAGE_DEF) {
            if ($i && !($i % (PAGE_DEF * 2))) {
                $dat .= " ";
            }
            if ($st == $i) {
                $dat .= "[" . ($i / PAGE_DEF) . "] ";
            } else {
                if ($i == 0) {
                    $dat .= '[<a href="' . PHP_SELF2 . '">0</a>] ';
                } else {
                    $dat .= '[<a href="' . ($i / PAGE_DEF) . PHP_EXT . '">' . ($i / PAGE_DEF) . '</a>] ';
                }
            }
        }
        $dat .= "</td>";

        if ($p >= PAGE_DEF && $counttree > $next) {
            $dat .= '<td><form action="' . $next / PAGE_DEF . PHP_EXT . '" method="get">';
            $dat .= '<input type="submit" value="' . S_NEXT . '" />';
            $dat .= "</form></td>";
        } else {
            $dat .= "<td>" . S_LASTPG . "</td>";
        }
        $dat .= "</tr></table><br clear='all' >\n";
        
        return $dat;
    }
    
    private function formatRange($page_no) {
        global $log;
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
        global $log;

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

?>