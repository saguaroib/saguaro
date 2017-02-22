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
    
        function generate($type, $no, $inIndex = false) {
        require_once(CORE_DIR . "/postform.php");

        $dat = '<form name= "delform" action="' . PHP_SELF_ABS . '" method="post">';
        $foot = '<table align="right"><tr><td class="delsettings" nowrap="nowrap" align="center">
                <input type="hidden" name="mode" value="usrdel" />' . S_REPDEL . '[<input type="checkbox" name="onlyimgdel" value="on" />' . S_DELPICONLY . ']
                ' . S_DELKEY . '<input type="password" name="pwd" size="8" maxlength="8" value="" />
                <input type="submit" value="' . S_DELETE . '" /><input type="button" value="Report" onclick="var o=document.getElementsByTagName(\'INPUT\');for(var i=0;i<o.length;i++)if(o[i].type==\'checkbox\' && o[i].checked && o[i].value==\'delete\') return reppop(\'' . PHP_SELF_ABS . '?mode=report&no=\'+o[i].name+\'\');"></tr></td></form><script>document.delform.pwd.value=l(' . SITE_ROOT . '_pass");</script></td></tr></table>';

        if ($type == "index") {
            return PostForm::format() . $dat . $this->generate_index($no, $inIndex) . $foot;
        } elseif ($type == "thread") {
            return PostForm::format($no) . $dat . $this->generate_thread($no, $inIndex) . $foot;
        }
    }

    function generate_index($no, $cacheThreads = false) {
        $this->update_cache();

        require_once(CORE_DIR . "/index/index.php");
        $index = new Index;
        $index_temp = $index->format($no, 0, $cacheThreads);

        $this->thread_cache = $index->thread_cache; //Copy Index thread cache.

        return $index_temp;
    }

    function generate_thread($no, $inIndex) {
        $this->update_cache();

        if ($inIndex && $this->thread_cache[$no]) { //Use $this->thread_cache if we want to generate an inIndex thread and it already exists.
           return $this->thread_cache[$no];
        } else {
            require_once(CORE_DIR . "/thread/thread.php"); //Safety.
            $thread->inIndex = $inIndex;
            $thread = new Thread;

            return $thread->format($no, true);
        }
    }

    function generate_all() {
        $this->update_cache();

        require_once(CORE_DIR . "/page/page.php");
        $pageC = new Page;

        $profile = microtime(); //Basic profiling.
        for ($page = 1; $page <= ceil(count($this->cache['THREADS']) / PAGE_DEF); $page++) {
            //Generate Index pages.
            $pageC->headVars['page']['title'] = "/" . BOARD_DIR . "/ - " . TITLE;
            $temp = $pageC->generate($this->generate("index", $page, false));
            $logfilename = ($page == 1) ? PHP_SELF2 : ($page - 1). PHP_EXT;

            echo "Writing out Index $page ($logfilename)... ";
            $this->print_page($logfilename , $temp);
            echo "Done!<br>";
        }

        foreach ($this->cache['THREADS'] as $no) {
                $pageC->headVars['page']['title'] = "/" . BOARD_DIR . "/" . (!empty($this->cache[$no]['sub']) ? " - " . $this->cache[$no]['sub'] : '') . " - " . TITLE;
                $logfilename = RES_DIR . $no . PHP_EXT;
                echo "Writing out #$no ($logfilename)... ";
                $temp = $pageC->generate($this->generate("thread", $no, false));

                $this->print_page($logfilename, $temp);
                echo "Done!<br>";
        }

        echo sprintf("<br>Took %f", microtime() - $profile) . " seconds.";
    }
}