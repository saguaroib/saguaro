<?php

/*

Log class. Generates index/thread HTML pages for a board.

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
    
    function update($resno = 0, $rebuild = 0) { //$resno = Thread to rebuild, $rebuild = Whether or not to rebuild indexes.
        global $path, $mysql;
        
        if ($_SERVER['REQUEST_METHOD'] == 'GET') { //User accessing imgboard.php directly, halt execution.
            if (is_file(PHP_SELF2) && DEBUG_MODE !== true) { //Unless the index file doesn't exist (probably first run)
                exit("<META HTTP-EQUIV='refresh' content='0;URL=" . PHP_SELF2 . "'>Done!");
            }
            echo "Generating index...";
        }
        
        logme("Received call to build res = $resno with index rebuild flag = $rebuild");
        require_once(CORE_DIR . "/postform.php");
        require_once(CORE_DIR . "/page/head.php");
        require_once(CORE_DIR . "/index/index.php");
        require_once(CORE_DIR . "/page/foot.php");
        $postform = new PostForm;
        $head = new Head;
        $index = new Index;
        $foot = new Footer;
        require_once(CORE_DIR . "/thread/thread.php"); //This won't need needed once the extra fluff is dealt with as we can just use the Index class.
        $thread = new Thread;
        if (FILE_BOARD) {
            require_once(CORE_DIR . "/log/fileboard.php");
            $fileboard = new SaguaroFileBoard;
        }
        
        if (!$resno) { //Everything that should be updated when the indexes are updated goes here. Catalog, index API etc..
            clearstatcache();
            //If a board is set to javascript catalog generation, only rebuild the catalog page once every 15 minutes (900 seconds).
            //$catalog_rebuild = (STATIC_CATALOG) ? (time() - CATALOG_THROTTLE) : (time() - 900); 
            //if (@filemtime("catalog.html") < $catalog_rebuild) {
                require_once(CORE_DIR . "/catalog/catalog.php");
                $catalog = new Catalog;
                $catalog->formatPage(STATIC_CATALOG); //Generate/update catalog.
            //}
            if (API_ENABLED) {
                require_once(CORE_DIR . "/api/apoi.php");
                $API = new SaguaroAPI;
            }
        }

        $this->update_cache(); //Muh speed increase (for when the function calls itself). Otherwise call Log->update_cache() manually.
        
        $find  = false;
        $resno = (int) $resno;
        $log   = $this->cache;
        
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
        $board = BOARD_DIR;
        if (!$result = $mysql->query("SELECT MAX(no) FROM " . SQLLOG . " WHERE board='$board'")) {
            error(S_SQLFAIL);
        }

        $row    = $mysql->fetch_array($result);
        $lastno = (int) $row[0];
        $mysql->free_result($result);
        
        $counttree = count($treeline);
        if (!$counttree) {
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

        if (CACHE_TTL >= 1) { // Throttling for thread rebuilds. 
            $logfilename = ($resno) ? RES_DIR . $resno . PHP_EXT : PHP_SELF2;
            clearstatcache();
            if (file_exists($logfilename) && filemtime($logfilename) > (time() - CACHE_TTL)) { // if the file has been made and it's younger than CACHE_TTL seconds ago
                rebuildqueue_add($resno); // save the post to be rebuilt later
                if ($resno && !$rebuild) // if it's a thread, try again on the indexes
                    $this->update();
                return true; // and we don't do any more rebuilding on this request
            } else {
                rebuildqueue_remove($resno); // we're gonna update it now, so take it out of the queue
                touch($logfilename); // and make sure nobody else starts trying to update it because it's too old
            }
        }
        
        if (API_ENABLED && !$resno) {
            unset($apiCache, $apiCache2); //Clear the thread cache
            $apiCache2 = array();
        }
        
        for ($page = 0; $page < $counttree; $page += PAGE_DEF) { //This loop generates every index page
            if (UPDATE_THROTTLING >= 1) {
                clearstatcache();
                if ($low_priority && @filemtime("update.stamp") > $update_start) {
                    return;
                }
                if (mt_rand(0, 15) == 0)
                    return;
            }
            
            if (API_ENABLED && !$resno)
                $apiCache = array();
            
            $head->info['page']['title'] = "/" . BOARD_DIR . "/ - " . strip_tags(TITLE);
            $head->info['page']['sub'] = S_HEADSUB;
            //$head->info['scripts'] = array("jquery.min.js", "main.js", "extension.js", "jquery-ui-1.10.4.min", "jquery.form.js"); //Add extra scripts to be included on every page <head> here.
            $head->info['js']['script'] = array("4ext.js","main.js"); //Add extra scripts to be included on every page <head> here.
            if (COUNTRY_FLAGS) array_push($head->info['css']['sheet'], "/flags/flags.css");
            if (MOBILE_THEME) array_push($head->info['css']['sheet'], "/stylesheets/mobile.css"); //:^) hi repod
            if (FILE_BOARD) {
                array_push($head->info['js']['script'], "fileboard.js");
                array_push($head->info['css']['sheet'], "/stylesheets/fileboard.css");
            }
            
            $dat = $head->generate();
            
            $dat .= $postform->format($resno);
            if (!$resno) {
                $st = $page;
            }
            $dat .= '<form id="delform" name="delform" action="' . PHP_SELF_ABS . '" method="post">';
            $dat .= '<div class="board">';

            if (FILE_BOARD && !$resno) { //Generates the table header
                $dat .= $fileboard->generateTable();
                if (!$resno) $fileboardClass = 0;
            } 
            
            for ($i = $st; $i < $st + PAGE_DEF; $i++) { //This loop generates every thread on an index page, or every reply in a thread page
                list($_unused, $no) = each($treeline);
                if (!$no) {
                    break;
                }
                $thread->inIndex = ($resno) ? false : true;
                
                if (FILE_BOARD) {
                    if (!$resno) {
                        $dat .= $fileboard->generateRow($log[$no], $no, $fileboardClass);
                        $fileboardClass++;
                    } else {
                        $dat .= $thread->format($no);
                    }
                } else {  //This generates each thread preview in the index, or an entire thread.
                    //if ($log[$no]['locked'] !== "2" && !$resno) { //Locked === 2, archived post. Locked === 1, regular locked post. Locked === 0, regular thread
                        $dat .= $thread->format($no);
                    //}
                }
                
                if (API_ENABLED && !$resno)
                    array_push($apiCache, $no); //Push thread numbers to an array to build api pages
                
                if (isset($log[$no]['old']) && EXPIRE_NEGLECTED == false) //Mark threads labeled as old if EXPIRE_NEGLECTED is disabled
                    $dat .= "<span class=\"oldpost\">" . S_OLD . "</span><br>\n";

                $resline = $log[$no]['children'];
                ksort($resline);
                $countres = count($log[$no]['children']);
                $t = 0;
                
                $disam = ($log[$no]['sticky'] >= 1) ? 1 : (defined('REPLIES_SHOWN')) ? REPLIES_SHOWN : 5;
                
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
                if (FILE_BOARD == false || $resno) $dat .= "</span><hr />\n";
                
                if ($resno)
                    $dat .= $postform->afterForm($resno, false);
                
                clearstatcache(); //clear stat cache of a file
                $p++;
                if ($resno) {
                    break;
                } //only one tree line at time of res
            }
            $dat .= "</div>"; //Close board div
            if (FILE_BOARD) $dat .= "</table>"; //Close table for file boards.
            
            if (ENABLE_ADS)
                $dat .= ADS_AFTERPOSTS . '<hr>';
            $arcdel = ($archived = true) ? "arcdel" : "usrdel";
            //afterPosts div is closed in page/foot.php
            $dat .= '<div class="afterPosts" /><div align="right" class="delsettings">';
            $dat .= '<input type="hidden" name="mode" value="$arcdel" />';
            if ($resno) $dat .= '<input type="hidden" value="$resno" name="threadno"/>';  
            $dat .= S_REPDEL . '[<input type="checkbox" name="onlyimgdel" value="on" />' . S_DELPICONLY . ']';
            $dat .= S_DELKEY . /*'<input type="password" name="pwd" size="8" maxlength="8" value="" />'*/;
            $dat .= '<input type="submit" value="' . S_DELETE . '" />
            <input type="button" value="Report" onclick="var o=document.getElementsByTagName(\'INPUT\');for(var i=0;i<o.length;i++)if(o[i].type==\'checkbox\' && o[i].checked && o[i].value==\'delete\') return reppop(\'' . PHP_SELF_ABS . '?mode=report&no=\'+o[i].name+\'\');"></form></div>';
            
            
            //Delete this after implementing Index class.
             if ( !$resno ) { // if not in reply to mode
                $prev = $st - PAGE_DEF;
                $next = $st + PAGE_DEF;
                //  Page processing
                $dat .= "<table align=left border=1 class=pages><tr>";
                if ( $prev >= 0 ) {
                    if ( $prev == 0 ) {
                        $dat .= "<form action=\"" . PHP_SELF2 . "\" method=\"get\" /><td>";
                    } else {
                        $dat .= "<form action=\"" . $prev / PAGE_DEF . PHP_EXT . "\" method=\"get\"><td>";
                    }
                    $dat .= "<input type=\"submit\" value=\"" . S_PREV . "\" />";
                    $dat .= "</td></form>";
                } else {
                    $dat .= "<td>" . S_FIRSTPG . "</td>";
                }
                $dat .= "<td>";
                for ( $i = 0; $i < $counttree; $i += PAGE_DEF ) {
                    if ( $i && !( $i % ( PAGE_DEF * 2 ) ) ) {
                        $dat .= " ";
                    }
                    if ( $st == $i ) {
                        $dat .= "[" . ( $i / PAGE_DEF ) . "] ";
                    } else {
                        if ( $i == 0 ) {
                            $dat .= "[<a href=\"" . PHP_SELF2 . "\">0</a>] ";
                        } else {
                            $dat .= "[<a href=\"" . ( $i / PAGE_DEF ) . PHP_EXT . "\">" . ( $i / PAGE_DEF ) . "</a>] ";
                        }
                    }
                }
                $dat .= "</td>";
                if ( $p >= PAGE_DEF && $counttree > $next ) {
                    $dat .= "<td><form action=\"" . $next / PAGE_DEF . PHP_EXT . "\" method=\"get\">";
                    $dat .= "<input type=\"submit\" value=\"" . S_NEXT . "\" />";
                    $dat .= "</form></td>";
                } else {
                    $dat .= "<td>" . S_LASTPG . "</td>";
                }
                $dat .= "</tr></table><br clear=\"all\" />\n";
            } else {
                $dat .= "<br />";
            }
            //end delete
            
            $dat .= $foot->format();
            if (API_ENABLED && !$resno) {
                $API->formatPage($page, $apiCache);
                $apiCache2 = array_merge($apiCache2, array(
                    $page => $apiCache
                ));
            }

            if ($resno) {
                $logfilename = RES_DIR . $resno . PHP_EXT;
                $this->print_page($logfilename, $dat);
                $dat = '';
                if (!$rebuild)
                    $deferred = $this->update(0);
                break;
            }
            $logfilename = (!$page) ? PHP_SELF2 : $page / PAGE_DEF . PHP_EXT;
            
            $this->print_page($logfilename, $dat);

            if (UPDATE_THROTTLING >= 1) {
                clearstatcache();
                if (@filemtime("update.stamp") == $update_start)
                    unlink("update.stamp");
            }
        }
        
        if (USE_RSS && !$resno) { //Update RSS if enabled after pages are built.
            clearstatcache();
            if ((time() - (30 * 60)) >= @filemtime("index.rss")) { //If rss feed is older than 20 minutes, update it.
                require_once(CORE_DIR . "/api/rss.php");
                $rss = new RSS;
                @$rss->generate();
            }
        }
        
        if (API_ENABLED && !$resno)
            $API->formatOther($apiCache2); //threads.json/catalog.json

        if (isset($deferred))
            return $deferred;

        return false;
    }
    
    function update_cache($revalidate = false) {
        //For porting purposes, the code was copied, formatted, and then just made to store the result in $this->cache.
        //However, it still needs to be rewritten.
        
        //Automatically exit if the cache isn't empty.
        if (!empty($this->cache) && $revalidate == false) //If cache isn't empty, continue. If no request to rebuild cache, continue
            return true; //Otherwise doesn't need to be updated.
        
        global $ipcount, $mysql_unbuffered_reads, $lastno, $mysql;
        
        $ips = [];
        $threads = []; // no's
        $log = []; // no -> [ data ]
        $offset = 0;
        $lastno = 0;
        $board  = BOARD_DIR;
        
        $mysql->query("SET read_buffer_size=1048576");
        $mysql_unbuffered_reads = 1;
        $query = $mysql->query("SELECT * FROM " . SQLLOG . " WHERE board='{$board}' ORDER BY sticky DESC, IF(sticky=0, last, sticky) DESC");
        
        $log['ips'] = array();
        $log['THREADS'] = array();
        while ($row = $mysql->fetch_assoc($query)) {
            if ($row['no'] > $lastno) {
                $lastno = $row['no'];
            }
            
            array_push($log['ips'], $row['host']);
            
            // initialize log row if necessary
            if (!isset($log[$row['no']])) {
                $log[$row['no']]             = $row;
                $log[$row['no']]['children'] = array();
            } else { // otherwise merge it with $row
                foreach ($row as $key => $val) {
                    $log[$row['no']][$key] = $val;
                }
            }
            
            // if this is a reply
            if ($row['resto'] !== 0) {
                ++$log[$row['resto']]['replies'];
                // initialize whatever we need to
                if (!isset($log[$row['resto']])) {
                    $log[$row['resto']] = array();
                }
                if (!isset($log[$row['resto']]['children'])) {
                    $log[$row['resto']]['children'] = array();
                }
                
                // add this post to list of children
                $log[$row['resto']]['children'][$row['no']] = 1;
                
                if (count($log[$row['resto']]['children']) >= MAX_RES) //Find out if we've hit bump limit
                    $log[$row['resto']]['bumplimit'] = 1;
                
                if ($row['fsize']) {
                    ++$log[$row['resto']]['images'];
                    if ($log[$row['resto']]['images'] >= MAX_IMGRES) { //Find out if we've hit image limit
                        $log[$row['resto']]['imagelimit'] = 1;
                    }
                }
            } else {
                array_push($log['THREADS'], $row['no']);
                $log[$row['resto']]['replies'] = 0;
                $log[$row['resto']]['images'] = 0;
                $log[$row['resto']]['imagelimit'] = 0;
                $log[$row['resto']]['bumplimit'] = 0;
                $log[$row['resto']]['bumplimit'] = 0;
            }
        }
        
        $mysql->free_result($query);
        
        $log['ipcount'] = count(array_unique($log['ips']));
        unset($log['ips']);
        
        /* This was integrated right into the code above. muh performance. (This is a large query, integrating it right into the SELECT * should really cut memory usage.) 
        //Basic support for bump order with new 'last' column.
        $query = $mysql->query("SELECT no FROM " . SQLLOG . " WHERE resto<1 AND board='$board' ORDER BY sticky DESC, IF(sticky=0, last, sticky) DESC");
        while ($row = $mysql->fetch_assoc($query)) {
            if (isset($log[$row['no']]) && $log[$row['no']]['resto'] == 0) {
                $threads[] = $row['no'];
            }
        }*/
        $log['THREADS'] = $threads;
        $mysql_unbuffered_reads = 0;
        
        // calculate old-status for PAGE_MAX mode
        if (EXPIRE_NEGLECTED !== true) {
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
        
        $this->cache = $log;
    }

    function print_page($filename, $contents, $force_nogzip = 0) {
        // print $contents to $filename by using a temporary file and renaming it
        // (makes *.html and *.gz if USE_GZIP is on)
        
        $gzip     = (USE_GZIP == 1 && !$force_nogzip);
        $tempfile = tempnam(realpath(RES_DIR), "tmp"); //note: THIS actually creates the file
        file_put_contents($tempfile, $contents, FILE_APPEND);
        rename($tempfile, $filename);
        chmod($filename, 0664); //it was created 0600
        
        if ($gzip) {
            $tempgz = tempnam(realpath(RES_DIR), "tmp"); //note: THIS actually creates the file
            $gzfp   = gzopen($tempgz, "w");
            gzwrite($gzfp, $contents);
            gzclose($gzfp);
            rename($tempgz, $filename . '.gz');
            chmod($filename . '.gz', 0664); //it was created 0600
        }
    }
}

?>