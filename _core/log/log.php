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

        //Finding the last entry number
        if (!$result = $mysql->query("select max(no) from " . SQLLOG)) {
            echo S_SQLFAIL;
        }

        $row = $mysql->fetch_array($result);
        $lastno = (int) $row[0];
        $mysql->free_result($result);

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

        if (UPDATE_THROTTLING >= 1) { //Throttling for Index pages (Bulk page updating, save disk I/O)
            $update_start = time();
            touch("update.stamp", $update_start);
            $low_priority = false;
            clearstatcache();
            if (@filemtime(PHP_SELF) > $update_start - UPDATE_THROTTLING) {
                $low_priority = true;
            } else {
                touch(PHP_SELF, $update_start);
            }
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

        for ($page = 1; $page <= $numThreads; $page += PAGE_DEF) { //This loop generates every index page
            if (UPDATE_THROTTLING >= 1) {
                clearstatcache();
                if ($low_priority && @filemtime("update.stamp") > $update_start) {
                    return;
                }
                if (mt_rand(0, 15) == 0)
                    return;
            }
            //$head->info['page']['title'] = "/" . BOARD_DIR . "/" . (($resno && !empty($log[$resno]['sub'])) ? " - " . $log[$resno]['sub'] : '') . " - " . TITLE; //apo note: this isn't the cleanest way to do it, disabling for now
            if (!$resno && $page > 1) $head->info['page']['title'] = "/" . BOARD_DIR . "/ - " . TITLE . "- Page {$page}";
            $dat = $head->generate();
            $dat .= $postform->format($resno);
            
            $st = ($resno) ? $page : null;
            
            $dat .= '<form name="delform" id="delform" action="' . PHP_SELF_ABS . '" method="post">';

            $dat .= "<div class='board'>";
            
            for ($i = $st; $i < $st + PAGE_DEF; $i++) {
                list($_unused, $no) = each($treeline);
                if (!$no) {
                    break;
                }

                //This won't need needed once the extra fluff is dealt with as we can just use the Index class.
                $thread->inIndex = ($resno) ? false : true;
                $dat .= $thread->format($no);

                // Deletion pending (We'll disable this for now as it currently serves no purpose)
                /*if (isset($log[$no]['old']))
                    $dat .= "<span class=\"oldpost\">" . S_OLD . "</span><br>\n"; */

                $resline = $log[$no]['children'];
                ksort($resline);
                $countres = count($log[$no]['children']);
                $t = 0;
                
                $disam = ($log[$no]['sticky'] != 0) ? 1 : (defined('REPLIES_SHOWN')) ? REPLIES_SHOWN : 5;

                $s = $countres - $disam;
                $cur = 1;
                while ($s >= $cur) {
                    list($row) = each($resline);
                    if ($log[$row]["fsize"] != 0) {
                        $t++;
                    }
                    $cur++;
                }
                if ($countres != 0)
                    reset($resline);


                /*possibility for ads after each post*/
                $dat .= "</span><hr>";

                if ($resno)
                    $dat .= "<div class='navLinks navLinksBot'>[<a href='" . PHP_SELF2_ABS . "'>" . S_RETURN . "</a>] [<a href='$resno" . PHP_EXT . "#top'>Top</a>]</div><hr>";

                clearstatcache(); //clear stat cache of a file
                //mysql_free_result($resline);
                $p++;
                if ($resno) {
                    break;
                } //only one tree line at time of res
            }

            
            $dat .= "</div>";

            if (ENABLE_ADS)
                $dat .= ADS_BELOWFORM . '<hr>';

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
                for ($i = 1; $i <= PAGE_MAX; $i+=1) {
                    $dat .= ($i !== $page && ($numThreads > (PAGE_DEF *  $i))) ? "[<a href='" . $i . PHP_EXT . "'>{$i}</a>] " : ($i === $page) ? "[<strong>{$i}</strong>] ": "[{$i}] "; //this ones for you hitler
                }
                if ($page < PAGE_MAX && ($numThreads > (PAGE_DEF *  $i))) {
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
            $logfilename = ($page === 1) ? PHP_SELF2 : $page / PAGE_DEF . PHP_EXT;

            $this->print_page($logfilename, $dat);
        }

		if (!$resno) { //Rebuild catalog page if index is changed. Eventually should handle catalog stuff client side...
            $catalog->formatPage();
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
        $query = $mysql->query("SELECT * FROM " . SQLLOG);

        while ($row = $mysql->fetch_assoc($query)) {
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
                /*$log[$row['resto']]['children'][$row['no']] = 1;
                if ($row['fsize']) {
                    if (!isset($log[$row['resto']]['imgreplycount'])) {
                        $log[$row['resto']]['imgreplycount'] = 0;
                    } else {
                        $log[$row['resto']]['imgreplycount']++;
                    }
                }*/
            }
        }
        
        $mysql->free_result($query); //Since the data has been moved to $log, free up $query
        $query = $mysql->query("SELECT * FROM " . SQLMEDIA . " WHERE board='" . BOARD_DIR . "'");
        
        while ($row = $mysql->fetch_assoc($query)) {
            if (!isset($log[$row['parent']][$row['no']])) {
                $log[$row['parent']][$row['no']] = $row;
            } else { // otherwise merge it with $row
                foreach ($row as $key => $val) {
                    $log[$row['parent']][$row['no']][$key] = $val;
                }
            }
        }
        $mysql->free_result($query); //Since the data has been moved to $log, free up $query

        //Basic support for bump order with new 'last' column.
        $query = $mysql->query('SELECT no FROM `'. SQLLOG . '` WHERE `resto` = 0 ORDER BY sticky DESC, IF(sticky=0, last, sticky) DESC');
        while ($row = $mysql->fetch_assoc($query)) {
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
            if (PAGE_MAX > 0) { // the lowest 5% of maximum threads get marked old
                for ($i = floor(0.95 * PAGE_MAX * PAGE_DEF); $i < $threadcount; $i++) {
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


?>