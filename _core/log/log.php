<?php

/*

    Log class.

    Needs to be greatly revised, but it's definitely a start.

    require_once(CORE_DIR . "/log/log.php");
    $my_log = new Log;
    $my_log->update_cache();
    print_r($my_log->cache);

*/

require_once("rebuild.php");

class Log {
    public $cache = [];
    private $thread_cache = [];

    function update($resno = 0, $rebuild = 0) {
        global $path, $mysql, $cssArray;

        if ($_SERVER['REQUEST_METHOD'] == 'GET') { //User accessing imgboard.php directly, halt execution.
            if (is_file(PHP_SELF2) && DEBUG_MODE !== true) { //Unless the index file doesn't exist (probably first run)
                exit("<META HTTP-EQUIV='refresh' content='0;URL=" . PHP_SELF2 . "'>Done!");
            }
            echo "Generating index...";
        }

        require_once(CORE_DIR . "/postform.php");
        require_once(CORE_DIR . "/page/head.php");
        require_once(CORE_DIR . "/catalog/catalog.php");
        require_once(CORE_DIR . "/thread/thread.php");
        require_once(CORE_DIR . "/page/foot.php");
        $foot = new Footer;
        $thread = new Thread;
        $catalog = new Catalog;
        $postform = new PostForm;
        $head = new Head;

        $this->update_cache(); //Muh speed increase (for when the function calls itself). Otherwise call Log->update_cache() manually.

        $find = false;
        $resno = (int) $resno;
        $log = $this->cache;

        if ($resno) {
            if (!isset($log[$resno])) {
                $this->update(0, $rebuild); // the post didn't exist, just rebuild the indexes
                return;
            } else if ($log[$resno]['resto']) {
                $this->update($log[$resno]['resto'], $rebuild); // $resno is a reply, try rebuilding the parent
                return;
            }
        }

        $treeline = ($resno) ? array($resno) : $log['THREADS'];
        $numThreads = count($treeline);
        
        $head->info['page']['title'] = "/" . BOARD_DIR . "/ - " . TITLE;
        $head->info['page']['sub'] = S_HEADSUB;
        $head->info['js']['script'] = ["extension.js"]; //Add extra scripts to be included on every page <head> here.
        if (COUNTRY_FLAGS) array_push($head->info['css']['sheet'], "/flags/flags.css");
        if (defined(MOBILE_THEME) && MOBILE_THEME) array_push($head->info['css']['sheet'], "/stylesheets/mobile.css");
        if (defined(FILE_BOARD) && FILE_BOARD) {
            array_push($head->info['js']['script'], "fileboard.js");
            array_push($head->info['css']['sheet'], "/stylesheets/fileboard.css");
        }
        
        
        if (!$numThreads) { //No threads on the board yet
            $logfilename = PHP_SELF2;
            $dat = $head->generate() . $postform->format($resno);
            
            $this->print_page($logfilename, $dat);
        }

        if (CACHE_TTL >= 1) {        //using CACHE_TTL method (Bulk page updating, save disk I/O)
            $logfilename = ($resno) ?  RES_DIR . $resno . PHP_EXT : PHP_SELF2;
            //if(USE_GZIP == 1) $logfilename .= '.html';
            clearstatcache(); // if the file has been made and it's younger than CACHE_TTL seconds ago
            if (file_exists($logfilename) && filemtime($logfilename) > (time() - CACHE_TTL)) {
                rebuildqueue_add($resno); // save the post to be rebuilt later
                if ($resno && !$rebuild) // if it's a thread, try again on the indexes
                    $this->update();
                return true; // and we don't do any more rebuilding on this request
            } else {
                rebuildqueue_remove($resno); // we're gonna update it now, so take it out of the queue
                touch($logfilename); // and make sure nobody else starts trying to update it because it's too old
            }
        }

        for ($page = 1; $page <= $numThreads; $page += THREADS_PER_PAGE) { //This loop generates every index page

            if (!$resno && $page > 1) $head->info['page']['title'] = "/" . BOARD_DIR . "/ - " . TITLE . "- Page {$page}";
            $dat = $head->generate();
            $postform->ribbon = [['link' => 'catalog.html','name' => 'Catalog']/*,['link' => PUBLIC_SERVER . BOARD_DIR . '/imgboard.php?mode=arc','name' => 'Archive'], ['link' => PUBLIC_SERVER . BOARD_DIR . '/imgboard.php?mode=logs','name' => 'Logs']*/];

            $dat .= $postform->format($resno);
            $st = ($resno) ? $page : null;

            $dat .= '<form name="delform" id="delform" action="' . PHP_SELF_ABS . '" method="post">';
            $dat .= "<div class='board'>";
            
            for ($i = $st; $i < $st + THREADS_PER_PAGE; $i++) { //Generates each thread on a page. If the page is the thread itself, loops only once.
                list($_unused, $no) = each($treeline);
                if (!$no) break;

                //This won't need needed once the extra fluff is dealt with as we can just use the Index class.
                $thread->inIndex = ($resno) ? false : true;
                $dat .= $thread->format($no);
                $dat .= "</span><hr>";

                if ($resno) $dat .= "<div class='navLinks navLinksBot'>[<a href='" . PHP_SELF2_ABS . "'>" . S_RETURN . "</a>] [<a href='$resno" . PHP_EXT . "#top'>Top</a>]</div><hr>";

                clearstatcache();
                if ($resno) break;
            }

            $dat .= "</div>";

            if (ENABLE_ADS) $dat .= ADS_BELOWFORM . '<hr>';

            //afterPosts div is closed in page/foot.php
            $dat .= '<div class="afterPosts"><div align="right" class="delsettings">';
            if ($resno) $dat .= "<input type='hidden' name='resnum' value='{$resno}'>";
            $delmode = ($is_archived) ? "arcdel" : "usrdel";
            $dat .= '<input type="hidden" name="mode" value="' . $delmode . '"/>';
            $dat .= S_REPDEL . '[<input type="checkbox" name="onlyimgdel" value="on" />' . S_DELPICONLY . ']';
            //$dat .= S_DELKEY . '<input type="password" name="pwd" size="8" maxlength="8" value="" />';
            $dat .= '<input type="submit" value="' . S_DELETE . '" /><input type="button" value="Report" onclick="report();"></form>';
            $dat .= "<span class='styleChanger'> Style: <select id='styleSelector'>";
            foreach($cssArray as $styleName => $stylePath) {
                $dat .= "<option value='{$styleName}'>{$styleName}</option>";
            }
            $dat .= "</select></span></div>";

            //Page switcher
            if (!$resno) {
                $dat .= "<div class='pagelist pages' style='float:left;padding:8px;'>";
                if ($page > 1) {
                    $prevPage = (($page - 1) > 1) ? ($page - 1) : "./";
                    $dat .= "<div class='prevPage'><form action='{$prevPage}'>";
                    $dat .= "<input type='submit' value='" . S_PREV . "' />";
                    $dat .= "</form></div>";
                }
                for ($i = 1; $i <= PAGES_PER_BOARD; $i+=1) {
                    $dat .= ($i !== $page && ($numThreads > (THREADS_PER_PAGE *  $i))) ? "[<a href='" . $i . PHP_EXT . "'>{$i}</a>] " : ($i === $page) ? "[<strong>{$i}</strong>] ": "[{$i}] "; //this ones for you hitler
                }
                if ($page < PAGES_PER_BOARD && ($numThreads > (THREADS_PER_PAGE *  $i))) {
                    $dat .= "<div class='nextPage'><form action='" . ($page + 1) . "'>";
                    $dat .= "<input type='submit' value='" . S_NEXT . "' />";
                    $dat .= "</form></div>";
                }
                $dat .= "</div>";
            }
            //End page switcher

            $dat .= $foot->format();

            if ($resno) {
                $logfilename = RES_DIR . $resno . PHP_EXT;
                $this->print_page($logfilename, $dat);
                $dat = '';
                if (!$rebuild)
                    $deferred = $this->update(0);
                break;
            }
            $logfilename = ($page === 1) ? PHP_SELF2 : $page / THREADS_PER_PAGE . PHP_EXT;

            $this->print_page($logfilename, $dat);
        }

        if (!$resno) { //Rebuild catalog page if index is changed
            $catalog->generate();
            if (defined('ENABLE_API') && ENABLE_API) {
                $apiClass->generatePages();
            }
        }

        if (isset($deferred))
            return $deferred;

        return false;
    }

    function update_cache($revalidate = false) {
        //For porting purposes, the code was copied, formatted, and then just made to store the result in $this->cache.
        //However, it still needs to be rewritten.

        //Automatically exit if the cache isn't empty.
        if (!empty($this->cache) && $revalidate == false)    //If cache isn't empty, continue. If no request to rebuild cache, continue
            return true; //Otherwise doesn't need to be updated.

        global $ipcount, $mysql_unbuffered_reads, $lastno, $mysql;

        $ips = [];
        $threads = []; // no's
        $log = []; // no -> [ data ]
        $offset = 0;
        $lastno = 0;

        $mysql->query("SET read_buffer_size=1048576");
        $mysql_unbuffered_reads = 1;
        $query = $mysql->fetch_assoc("SELECT * FROM " . SQLLOG);

        foreach($query as $row) {
            if ($row['no'] > $lastno) {
                $lastno = $row['no'];
            }

            $ips[$row['host']] = 1;

            // initialize log row if necessary
            if (!isset($log[$row['no']])) {
                $log[$row['no']] = $row;
                $log[$row['no']]['children'] = array();
            } else { // otherwise merge it with $row
                foreach ($row as $key => $val) {
                    $log[$row['no']][$key] = $val;
                }
            }

            // if this is a reply
            if ($row['resto']) {
                // initialize whatever we need to
                if (!isset($log[$row['resto']])) {
                    $log[$row['resto']] = array();
                }
                if (!isset($log[$row['resto']]['children'])) {
                    $log[$row['resto']]['children'] = array();
                }

                // add this post to list of children
                $log[$row['resto']]['children'][$row['no']] = 1;
                if ($row['media']) {
                    if (!isset($log[$row['resto']]['imgreplycount'])) {
                        $log[$row['resto']]['imgreplycount'] = 0;
                    } else {
                        $log[$row['resto']]['imgreplycount']++;
                    }
                }
            }
        }
        
        $mysql->free_result($query); //Since the data has been moved to $log, free up $query

        //Basic support for bump order with new 'last' column.
        $query = $mysql->fetch_assoc('SELECT no FROM `'. SQLLOG . '` WHERE `resto` = 0 ORDER BY sticky DESC, IF(sticky=0, last, sticky) DESC');
        foreach ($query as $row) {
            if (isset($log[$row['no']]) && $log[$row['no']]['resto'] == 0) {
                $threads[] = $row['no'];
            }
        }
        $log['THREADS'] = $threads;
        $mysql_unbuffered_reads = 0;

        // calculate old-status for PAGE_MAX mode
        if (EXPIRE_NEGLECTED !== 1) {
            rsort($threads, SORT_NUMERIC);

            $threadcount = count($threads);
            if (PAGES_PER_BOARD > 0) { // the lowest 5% of maximum threads get marked old
                for ($i = floor(0.95 * PAGES_PER_BOARD * THREADS_PER_PAGE); $i < $threadcount; $i++) {
                    if (!$log[$threads[$i]]['sticky'] && EXPIRE_NEGLECTED !== 1) {
                        $log[$threads[$i]]['old'] = 1;
                    }
                }
            } else { // threads w/numbers below 5% of LOG_MAX get marked old
                foreach ($threads as $thread) {
                    if ($lastno - LOG_MAX * 0.95 > $thread && !$log[$thread]['sticky']) {
                        $log[$thread]['old'] = 1;
                    }
                }
            }
        }

        $ipcount = count($ips);

        $this->cache = $log;
    }

    function print_page($filename, $contents, $force_nogzip = 0) {
        // print $contents to $filename by using a temporary file and renaming it
        // (makes *.html and *.gz if USE_GZIP is on)

        $gzip = (USE_GZIP == 1 && !$force_nogzip);
        $tempfile = tempnam(realpath(RES_DIR), "tmp"); //note: THIS actually creates the file
        file_put_contents($tempfile, $contents, FILE_APPEND);
        rename($tempfile, $filename);
        chmod($filename, 0664); //it was created 0600

        if ($gzip) {
            $tempgz = tempnam(realpath(RES_DIR), "tmp"); //note: THIS actually creates the file
            $gzfp = gzopen($tempgz, "w");
            gzwrite($gzfp, $contents);
            gzclose($gzfp);
            rename($tempgz, $filename . '.gz');
            chmod($filename . '.gz', 0664); //it was created 0600
        }
    }
}