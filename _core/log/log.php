<?php

/*

    Log class.

    Needs to be greatly revised, but it's definitely a start.

    require_once(CORE_DIR . "/log/log.php");
    $my_log = new Log;
    $my_log->update_cache();
    print_r($my_log->cache);

*/

class Log {
    public $cache = [];

    function update($resno, $rebuild) {
        global $log, $path;
        $this->update_cache();

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

        if ($resno) {
            $treeline = array(
                 $resno
           );
            //if(!$treeline=mysql_call("select * from ".SQLLOG." where root>0 and no=".$resno." order by root desc")){echo S_SQLFAIL;}
        } else {
            $treeline = $log['THREADS'];
            //if(!$treeline=mysql_call("select * from ".SQLLOG." where root>0 order by root desc")){echo S_SQLFAIL;}
        }

        //Finding the last entry number
        if (!$result = mysql_call("select max(no) from " . SQLLOG)) {
            echo S_SQLFAIL;
        }

        $row = mysql_fetch_array($result);
        $lastno = (int) $row[0];
        mysql_free_result($result);

        $counttree = count($treeline);
        //$counttree=mysql_num_rows($treeline);
        if (!$counttree) {
            $logfilename = PHP_SELF2;
            $dat = head();
            form($dat, $resno);
            $this->print_page($logfilename, $dat);
        }

        if (UPDATE_THROTTLING >= 1) {
            $update_start = time();
            touch("updatelog.stamp", $update_start);
            $low_priority = false;
            clearstatcache();
            if (@filemtime(PHP_SELF) > $update_start - UPDATE_THROTTLING) {
                $low_priority = true;
                //touch($update_start . ".lowprio");
            } else {
                touch(PHP_SELF, $update_start);
            }
            // 	$mt = @filemtime(PHP_SELF);
            //  	touch($update_start . ".$mt.highprio");
        }

        //using CACHE_TTL method
        if (CACHE_TTL >= 1) {
            if ($resno) {
                $logfilename = RES_DIR . $resno . PHP_EXT;
            } else {
                $logfilename = PHP_SELF2;
            }
            //if(USE_GZIP == 1) $logfilename .= '.html';
            // if the file has been made and it's younger than CACHE_TTL seconds ago
            clearstatcache();
            if (file_exists($logfilename) && filemtime($logfilename) > (time() - CACHE_TTL)) {
                // save the post to be rebuilt later
                rebuildqueue_add($resno);
                // if it's a thread, try again on the indexes
                if ($resno && !$rebuild)
                    $this->update();
                // and we don't do any more rebuilding on this request
                return true;
            } else {
                // we're gonna update it now, so take it out of the queue
                rebuildqueue_remove($resno);
                // and make sure nobody else starts trying to update it because it's too old
                touch($logfilename);
            }
        }


        for ($page = 0; $page < $counttree; $page += PAGE_DEF) {
            $dat = head();
            form($dat, $resno);
            if (!$resno) {
                $st = $page;
            }
            $dat .= '<form name= "delform" action="' . PHP_SELF_ABS . '" method="post">';

            for ($i = $st; $i < $st + PAGE_DEF; $i++) {
                list($_unused, $no) = each($treeline);
                if (!$no) {
                    break;
                }

                /*

                Not implemented:

                $com = auto_link($com, $resno);

                */

                //This won't need needed once the extra fluff is dealt with as we can just use the Index class.
                require_once(CORE_DIR . "/thread/thread.php");
                $thread = new Thread;
                $thread->inIndex = ($resno) ? false : true;
                $dat .= $thread->format($no);

                // Deletion pending
                if (isset($log[$no]['old']))
                    $dat .= "<span class=\"oldpost\">" . S_OLD . "</span><br>\n";

                $resline = $log[$no]['children'];
                ksort($resline);
                $countres = count($log[$no]['children']);
                $t        = 0;
                if ($sticky == 1) {
                    $disam = 1;
                } elseif (defined('REPLIES_SHOWN')) {
                    $disam = REPLIES_SHOWN;
                } else {
                    $disam = 5;
                }
                $s   = $countres - $disam;
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
                $dat .= "</span><br clear=\"left\" /><hr />\n";

                if (USE_ADS3)
                    $dat .= ADS3 . '<hr>';

                if ($resno)
                    $dat .= "[<a href='" . PHP_SELF2_ABS . "'>" . S_RETURN . "</a>] [<a href='$resto" . PHP_EXT . "#top'>Top</a>]<hr>";

                clearstatcache(); //clear stat cache of a file
                //mysql_free_result($resline);
                $p++;
                if ($resno) {
                    break;
                } //only one tree line at time of res
            }

            $dat .= '<table align="right"><tr><td class="delsettings" nowrap="nowrap" align="center">
    <input type="hidden" name="mode" value="usrdel" />' . S_REPDEL . '[<input type="checkbox" name="onlyimgdel" value="on" />' . S_DELPICONLY . ']
    ' . S_DELKEY . '<input type="password" name="pwd" size="8" maxlength="8" value="" />
    <input type="submit" value="' . S_DELETE . '" /><input type="button" value="Report" onclick="var o=document.getElementsByTagName(\'INPUT\');for(var i=0;i<o.length;i++)if(o[i].type==\'checkbox\' && o[i].checked && o[i].value==\'delete\') return reppop(\'' . PHP_SELF_ABS . '?mode=report&no=\'+o[i].name+\'\');"></tr></td></form><script>document.delform.pwd.value=l(' . SITE_ROOT . '_pass");</script></td></tr></table>';
            /*<script language="JavaScript" type="script"><!--
            l();
            //--></script>';*/

            foot($dat);
            if ($resno) {
                $logfilename = RES_DIR . $resno . PHP_EXT;
                $this->print_page($logfilename, $dat);
                $dat = '';
                if (!$rebuild)
                    $deferred = $this->update(0);
                break;
            }
            if ($page == 0) {
                $logfilename = PHP_SELF2;
            } else {
                $logfilename = $page / PAGE_DEF . PHP_EXT;
            }
            $this->print_page($logfilename, $dat);
            //chmod($logfilename,0666);
        }
        //mysql_free_result($treeline);
        if (isset($deferred))
            return $deferred;
        return false;
    }

    function update_cache(/*$invalidate = 0*/) {
        //For porting purposes, the code was copied, formatted, and then just made to store the result in $this->cache.
        //However, it still needs to be rewritten.

        //This currently does nothing as nothing ever calls it with true.
        //if ($invalidate == 0 && !empty($this->cache)) { return; }

        global $log, $ipcount, $mysql_unbuffered_reads, $lastno;

        $ips = [];
        $threads = []; // no's
        $log = []; // no -> [ data ]
        $offset = 0;
        $lastno = 0;

        mysql_call("SET read_buffer_size=1048576");
        $mysql_unbuffered_reads = 1;
        $query = mysql_call("SELECT * FROM " . SQLLOG);

        while ($row = mysql_fetch_assoc($query)) {
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
                if ($row['fsize']) {
                    if (!isset($log[$row['resto']]['imgreplycount'])) {
                        $log[$row['resto']]['imgreplycount'] = 0;
                    } else {
                        $log[$row['resto']]['imgreplycount']++;
                    }
                }
            }

        }

        $query = mysql_call("SELECT no FROM " . SQLLOG . " WHERE root>0 order by root desc");
        while ($row = mysql_fetch_assoc($query)) {
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
