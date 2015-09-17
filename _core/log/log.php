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
            $dat = '';
            $dat .= head();
            form($dat, $resno);
            print_page($logfilename, $dat);
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
                //list($no,$sticky,$permasage,$closed,$now,$name,$email,$sub,$com,$host,$pwd,$filename,$ext,$w,$h,$tn_w,$tn_h,$tim,$time,$md5,$fsize,$root,$resto)=mysql_fetch_row($treeline);
                if (!$no) {
                    break;
                }
                extract($log[$no]);
                
                // URL and link
                // If not in a thread 
                //$threadurl = "" . PHP_SELF . "?res=$no";
                if ($email)
                    $name = "<a href=\"mailto:$email\" class=\"linkmail\">$name</a>";
                if (strpos($sub, "SPOILER<>") === 0) {
                    $sub = substr($sub, strlen("SPOILER<>")); //trim out SPOILER<>
                    $spoiler = 1;
                } else {
                    $spoiler = 0;
                }
                $com = auto_link($com, $resno);
                if (!$resno)
                    list($com, $abbreviated) = abbreviate($com, MAX_LINES_SHOWN);
                
                if (isset($abbreviated) && $abbreviated)
                    $com .= "<br /><span class=\"abbr\">Comment too long. Click <a href=\"" . RES_DIR . ( $resto ? $resto : $no ) . PHP_EXT . "#$no\">here</a> to view the full text.</span>";
                
                //OP Post image
                
                $imgdir = IMG_DIR;
                $thumbdir = DATA_SERVER . BOARD_DIR . "/" . THUMB_DIR;
                $cssimg = CSS_PATH;
                
                // Picture file name
                $img        = $path . $tim . $ext;
                $displaysrc = DATA_SERVER . BOARD_DIR . "/" . $imgdir . $tim . $ext;
                $linksrc    = ( ( USE_SRC_CGI == 1 ) ? ( str_replace( ".cgi", "", $imgdir ) . $tim . $ext ) : $displaysrc );
                if ( defined( 'INTERSTITIAL_LINK' ) )
                    $linksrc = str_replace( INTERSTITIAL_LINK, "", $linksrc );
                $src = IMG_DIR . $tim . $ext;
                if ( $fname == 'image' )
                    $fname = time();
                $longname  = $fname;
                $shortname = ( strlen( $fname ) > 40 ) ? substr( $fname, 0, 40 ) . "(...)" . $ext : $longname;
                // img tag creation
                $imgsrc    = "";
                if ( $ext ) {
                    // turn the 32-byte ascii md5 into a 24-byte base64 md5
                    $shortmd5 = base64_encode( pack( "H*", $md5 ) );
                    if ( $fsize >= 1048576 ) {
                        $size = round( ( $fsize / 1048576 ), 2 ) . " M";
                    } else if ( $fsize >= 1024 ) {
                        $size = round( $fsize / 1024 ) . " K";
                    } else {
                        $size = $fsize . " ";
                    }
                    if ( !$tn_w && !$tn_h && $ext == ".gif" ) {
                        $tn_w = $w;
                        $tn_h = $h;
                    }
                    if ( $spoiler ) {
                        $size   = "Spoiler Image, $size";
                        $imgsrc = "<br><a href=\"" . $displaysrc . "\" target=_blank><img src=\"" . SPOILER_THUMB . "\" border=0 align=left hspace=20 alt=\"" . $size . "B\" md5=\"$shortmd5\"></a>";
                    } elseif ( $tn_w && $tn_h ) { //when there is size...
                        if ( @is_file( THUMB_DIR . $tim . 's.jpg' ) ) {
                            $imgsrc = "<br><a href=\"" . $displaysrc . "\" target=_blank><img class=\"postimg\" src=" . $thumbdir . $tim . 's.jpg' . " border=0 align=left width=$tn_w height=$tn_h hspace=20 alt=\"" . $size . "B\" md5=\"$shortmd5\"></a>";
                        } else {
                            $imgsrc = "<a href=\"" . $displaysrc . "\" target=_blank><span class=\"tn_thread\" title=\"" . $size . "B\">Thumbnail unavailable</span></a>";
                        }
                    } else {
                        if ( @is_file( THUMB_DIR . $tim . 's.jpg' ) ) {
                            $imgsrc = "<br><a href=\"" . $displaysrc . "\" target=_blank><img class=\"postimg\" src=" . $thumbdir . $tim . 's.jpg' . " border=0 align=left hspace=20 alt=\"" . $size . "B\" md5=\"$shortmd5\"></a>";
                        } else {
                            $imgsrc = "<a href=\"" . $displaysrc . "\" target=_blank><span class=\"tn_thread\" title=\"" . $size . "B\">Thumbnail unavailable</span></a>";
                        }
                    }
                    if ( !is_file( $src ) ) {
                        $dat .= '<img src="' . $cssimg . 'filedeleted.gif" alt="File deleted.">';
                    } else {
                        $dimensions = ( $ext == '.pdf' ) ? 'PDF' : "{$w}x{$h}";
                        if ( $resno ) {
                            $dat .= "<span class=\"filesize\">" . S_PICNAME . "<a href=\"$linksrc\" target=\"_blank\">$time$ext</a> (" . $size . "B, " . $dimensions . ", <span title=\"" . $longname . "\">" . $shortname . "</span>)</span>" . $imgsrc;
                        } else {
                            $dat .= "<span class=\"filesize\">" . S_PICNAME . "<a href=\"$linksrc\" target=\"_blank\">$time$ext</a> (" . $size . "B, " . $dimensions . ")</span>" . $imgsrc;
                        }
                    }
                }
                
                //  Main creation
                
                $dat .= "<a name=\"$resno\"></a>\n<input type=checkbox name=\"$no\" value=delete><span class=\"filetitle\">$sub</span> \n";
                $dat .= "<span class=\"postername\">$name</span> $now <span id=\"nothread$no\">";
                
                if ( $sticky == 1 )
                    $stickyicon = ' <img src="' . CSS_PATH . '/sticky.gif" alt="sticky"> ';
                else
                    $stickyicon = '';
                
                if ( $locked == 1 )
                    $stickyicon .= ' <img src="' . CSS_PATH . '/locked.gif" alt="closed"> ';
                
                if ( $resno ) {
                    $dat .= "<a href=\"#$no\" class=\"quotejs\">No.</a><a href=\"javascript:insert('$no')\" class=\"quotejs\">$no</a> $stickyicon &nbsp; ";
                } else {
                    $dat .= "<a href=\"" . RES_DIR . $no . PHP_EXT . "#" . $no . "\" class=\"quotejs\">No.</a><a href=\"" . RES_DIR . $no . PHP_EXT . "#q" . $no . "\" class=\"quotejs\">$no</a> $stickyicon &nbsp; [<a href=\"" . RES_DIR . $no . PHP_EXT . "\">" . S_REPLY . "</a>]";
                }
                
                $dat .= "</span>\n<blockquote>$com</blockquote>";
                
                // Deletion pending
                if ( isset( $log[$no]['old'] ) )
                    $dat .= "<span class=\"oldpost\">" . S_OLD . "</span><br>\n";
                
                $resline = $log[$no]['children'];
                ksort( $resline );
                $countres = count( $log[$no]['children'] );
                $t        = 0;
                if ( $sticky == 1 ) {
                    $disam = 1;
                } elseif ( defined( 'REPLIES_SHOWN' ) ) {
                    $disam = REPLIES_SHOWN;
                } else {
                    $disam = 5;
                }
                $s   = $countres - $disam;
                $cur = 1;
                while ( $s >= $cur ) {
                    list( $row ) = each( $resline );
                    if ( $log[$row]["fsize"] != 0 ) {
                        $t++;
                    }
                    $cur++;
                }
                if ( $countres != 0 )
                    reset( $resline );
                
                if ( !$resno ) {
                    if ( $s < 2 ) {
                        $posts = " post";
                    } else {
                        $posts = " posts";
                    }
                    if ( $t < 2 ) {
                        $replies = "reply";
                    } else {
                        $replies = "replies";
                    }
                    if ( ( $s > 0 ) && ( $t == 0 ) ) {
                        $dat .= "<span class=\"omittedposts\">" . $s . $posts . " omitted. Click <a href=\"" . RES_DIR . $no . PHP_EXT . "#" . $no . "\"> Reply</a> to view.</span>\n";
                    } elseif ( ( $s > 0 ) && ( $t > 0 ) ) {
                        $dat .= "<span class=\"omittedposts\">" . $s . $posts . " and " . $t . " image " . $replies . " omitted. Click <a href=\"" . RES_DIR . $no . PHP_EXT . "#" . $no . "\"> Reply</a> to view.</span>\n";
                    }
                } else {
                    $s = 0;
                }
                
                while ( list( $resrow ) = each( $resline ) ) {
                    if ( $s > 0 ) {
                        $s--;
                        continue;
                    }
                    //list($no,$sticky,$permasage,$closed,$now,$name,$email,$sub,$com,$host,$pwd,$filename,$ext,$w,$h,$tn_w,$tn_h,$tim,$time,$md5,$fsize,$root,$resto)=$resrow;
                    extract( $log[$resrow] );
                    if ( !$no ) {
                        break;
                    }
                    
                    // URL and e-mail
                    if ( $email )
                        $name = "<a href=\"mailto:$email\" class=\"linkmail\">$name</a>";
                    if ( strpos( $sub, "SPOILER<>" ) === 0 ) {
                        $sub     = substr( $sub, strlen( "SPOILER<>" ) ); //trim out SPOILER<>
                        $spoiler = 1;
                    } else
                        $spoiler = 0;
                    $com = auto_link( $com, $resno );
                    if ( !$resno )
                        list( $com, $abbreviated ) = abbreviate( $com, MAX_LINES_SHOWN );
                    
                    if ( isset( $abbreviated ) && $abbreviated )
                        $com .= "<br /><span class=\"abbr\">Comment too long. Click <a href=\"" . RES_DIR . ( $resto ? $resto : $no ) . PHP_EXT . "#$no\">here</a> to view the full text.</span>";
                    
                    //Replies creation      
                    // Picture file name
                    $r_img        = $path . $tim . $ext;
                    $r_displaysrc = DATA_SERVER . BOARD_DIR . "/" . $imgdir . $tim . $ext;
                    $r_linksrc    = ( ( USE_SRC_CGI == 1 ) ? ( str_replace( ".cgi", "", $imgdir ) . $tim . $ext ) : $r_displaysrc );
                    if ( defined( 'INTERSTITIAL_LINK' ) )
                        $r_linksrc = str_replace( INTERSTITIAL_LINK, "", $r_linksrc );
                    $r_src = DATA_SERVER . BOARD_DIR . "/" . IMG_DIR . $tim . $ext;
                    if ( $fname == 'image' )
                        $fname = time();
                    $longname  = $fname;
                    $shortname = ( strlen( $fname ) > 40 ) ? substr( $fname, 0, 40 ) . "(...)" . $ext : $longname;
                    // img tag creation
                    $r_imgsrc  = "";
                    if ( $ext ) {
                        // turn the 32-byte ascii md5 into a 24-byte base64 md5
                        $shortmd5 = base64_encode( pack( "H*", $md5 ) );
                        if ( $fsize >= 1048576 ) {
                            $size = round( ( $fsize / 1048576 ), 2 ) . " M";
                        } else if ( $fsize >= 1024 ) {
                            $size = round( $fsize / 1024 ) . " K";
                        } else {
                            $size = $fsize . " ";
                        }
                        if ( !$tn_w && !$tn_h && $ext == ".gif" ) {
                            $tn_w = $w;
                            $tn_h = $h;
                        }
                        if ( $spoiler ) {
                            $size     = "Spoiler Image, $size";
                            $r_imgsrc = "<br><a href=\"" . $r_displaysrc . "\" target=_blank><img src=\"" . SPOILER_THUMB . "\" border=0 align=left hspace=20 alt=\"" . $size . "B\" md5=\"$shortmd5\"></a>";
                        } elseif ( $tn_w && $tn_h ) { //when there is size...
                            if ( @is_file( THUMB_DIR . $tim . 's.jpg' ) ) {
                                $r_imgsrc = "<br><a href=\"" . $r_displaysrc . "\" target=_blank><img class='postimg'  src=" . $thumbdir . $tim . 's.jpg' . " border=0 align=left width=$tn_w height=$tn_h hspace=20 alt=\"" . $size . "B\" md5=\"$shortmd5\"></a>";
                            } else {
                                $r_imgsrc = "<a href=\"" . $r_displaysrc . "\" target=_blank><span class=\"tn_reply\" title=\"" . $size . "B\">Thumbnail unavailable</span></a>";
                            }
                        } else {
                            if ( @is_file( THUMB_DIR . $tim . 's.jpg' ) ) {
                                $r_imgsrc = "<br><a href=\"" . $r_displaysrc . "\" target=_blank><img class='postimg'  src=" . $thumbdir . $tim . 's.jpg' . " border=0 align=left hspace=20 alt=\"" . $size . "B\" md5=\"$shortmd5\"></a>";
                            } else {
                                $r_imgsrc = "<a href=\"" . $r_displaysrc . "\" target=_blank><span class=\"tn_reply\" title=\"" . $size . "B\">Thumbnail unavailable</span></a>";
                            }
                        }
                        if ( !is_file( $src ) ) {
                            $r_imgreply = '<br><img src="' . $cssimg . 'filedeleted-res.gif" alt="File deleted.">';
                        } else {
                            $dimensions = ( $ext == '.pdf' ) ? 'PDF' : "{$w}x{$h}";
                            if ( $resno ) {
                                $r_imgreply = "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"filesize\">" . S_PICNAME . "<a href=\"$r_linksrc\" target=\"_blank\">$time$ext</a>-(" . $size . "B, " . $dimensions . ", <span title=\"" . $longname . "\">" . $shortname . "</span>)</span>" . $r_imgsrc;
                            } else {
                                $r_imgreply = "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"filesize\">" . S_PICNAME . "<a href=\"$r_linksrc\" target=\"_blank\">$time$ext</a>-(" . $size . "B, " . $dimensions . ")</span>" . $r_imgsrc;
                            }
                        }
                    }
                    
                    // Main Reply creation
                    $dat .= "<a name=\"$no\"></a>\n";
                    $dat .= "<table><tr><td nowrap class=\"doubledash\">&gt;&gt;</td><td id=\"$no\" class=\"reply\">\n";
                    //      if (($t>3)&&($fsize!=0)) {
                    //      $dat.="&nbsp;&nbsp;&nbsp;<b>Image hidden</b>&nbsp;&nbsp; $now No.$no \n";
                    //			} else {
                    $dat .= "<input type=checkbox name=\"$no\" value=delete><span class=\"replytitle\">$sub</span> \n";
                    $dat .= "<span class=\"commentpostername\">$name</span> $now <span id=\"norep$no\">";
                    if ( $resno ) {
                        $dat .= "<a href=\"#$no\" class=\"quotejs\">No.</a><a href=\"javascript:insert('$no')\" class=\"quotejs\">$no</a></span>";
                    } else {
                        $dat .= "<a href=\"" . RES_DIR . $resto . PHP_EXT . "#$no\" class=\"quotejs\">No.</a><a href=\"" . RES_DIR . $resto . PHP_EXT . "#q$no\" class=\"quotejs\">$no</a></span>";
                    }
                    
                    if ( isset( $r_imgreply ) )
                        $dat .= $r_imgreply;
                    $dat .= "<blockquote>$com</blockquote>";
                    $dat .= "</td></tr></table>\n";
                    unset( $r_imgreply );
                }
                
                /*possibility for ads after each post*/
                $dat .= "</span><br clear=\"left\" /><hr />\n";
                
                if ( USE_ADS3 )
                    $dat .= '' . ADS3 . '<hr />';
                
                if ( $resno )
                    $dat .= "[<a href=\"" . PHP_SELF2_ABS . "\">" . S_RETURN . "</a>] [<a href=\"" . $resto . PHP_EXT . "#top\"/>Top</a>]\n<hr />";
                
                clearstatcache(); //clear stat cache of a file
                //mysql_free_result( $resline );
                $p++;
                if ( $resno ) {
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
            
            
            foot( $dat );
            if ( $resno ) {
                $logfilename = RES_DIR . $resno . PHP_EXT;
                print_page( $logfilename, $dat );
                $dat = '';
                if ( !$rebuild )
                    $deferred = $this->update(0);
                break;
            }
            if ( $page == 0 ) {
                $logfilename = PHP_SELF2;
            } else {
                $logfilename = $page / PAGE_DEF . PHP_EXT;
            }
            print_page( $logfilename, $dat );
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
                if ( $row['fsize'] ) {
                    if (!isset( $log[$row['resto']]['imgreplycount'])) {
                        $log[$row['resto']]['imgreplycount'] = 0;
                    } else {
                        $log[$row['resto']]['imgreplycount']++;
                    }
                }
            }

        }

        $query = mysql_call( "SELECT no FROM " . SQLLOG . " WHERE root>0 order by root desc" );
        while ( $row = mysql_fetch_assoc( $query ) ) {
            if ( isset( $log[$row['no']] ) && $log[$row['no']]['resto'] == 0 ) {
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
}


?>
