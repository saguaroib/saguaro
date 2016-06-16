<?php

/*
    Builds the archive page if ARCHIVE_ENABLE is set in board config.
    Prints out static page, archive.html
*/
require_once(CORE_DIR . "/log/log.php");
class SaguaroArchive extends Log{

    //Archives a thread when given the OP #
    public function archive($no) {
        global $mysql;
        $board = BOARD_DIR;
        //"no, now, name, email, sub, com, host, pwd, ext, w, h, tn_w, tn_h, tim, time, md5, fsize, fname, embed, sticky, permasage, locked, last, modified, resto, board",
        $mysql->query("UPDATE " . SQLLOG . " SET locked='2' WHERE no='{$no}' AND board='{$board}'");
        $this->update($no, 1); //Don't rebuild the index, that's regists responsibility.
        $now = time();
        
        //Buckle up lads, you're in for a ride.
        $mysql->query("INSERT INTO " . SQLARCHIVE . " SELECT * FROM " . SQLLOG . " WHERE (no='{$no}' OR resto='{$no}') AND board='{$board}");
        $mysql->query("UPDATE " . SQLARCHIVE . " SET archived_on='{$now}' WHERE (no='{$no}' OR resto='{$no}') AND board='{$board}");
        $mysql->query("DELETE FROM " . SQLLOG . " WHERE (no='{$no}' OR resto='{$no}') AND board='{$board}");
        $this->updateArchive();
        return;
    }

    private function updateArchive() {
        global $mysql;
        
        require_once(CORE_DIR . "/postform.php");
        require_once(CORE_DIR . "/page/head.php");
        //require_once(CORE_DIR . "/page/foot.php");
        $postform = new PostForm;
        $head     = new Head;
        //$foot     = new Footer;

        
        
        $head->info['page']['title'] = "/" . BOARD_DIR . "/ - " .  TITLE . " - Archive";
        $head->info['css']['sheet'] = array('/stylesheets/archive.css');

        $log   = $this->archive_cache;        
        
        $head->info['page']['sub'] = "Currently displaying " . count($archiveList) . " archived threads.";
        //$head->info['js']['script'] = array("4ext.js","main.js"); //Add extra scripts to be included on every page <head> here.
        $dat = $head->generate();
        $dat .= $postform->afterForm(0, 2);
        $dat .= "<table class='archiveList'>
            <th class='postblock'>" . S_NUMPREFIX . "</th>
            <th class='postblock'>" . S_NAME . "</th>
            <th class='postblock'>" . S_COMMENT . "</th>
            <th class='postblock'>" . S_REPLIES . "</th>
            <th class='postblock' colspan='1'></th>";

        $j = 0;        
        foreach ($log['THREADS'] as $thread) {
            if ($log[$thread]['archived_on'] <= (time() - ARCHIVE_AGE)) {
                $this->delete_archive($thread, true); //The time has come and so have i. Remove posts from archive.
            } else {
                $row = ($j % 2) ? "alt" : "";
                $trunccom = (strlen($log[$thread]['com']) > 99) ? substr($log[$thread]['com'], 0, 99) . "..." : $log[$thread]['com'];
                $repcount = $log[$thread]['replies'];
                $dat .= "<tr class='{$row}'><td>{$thread}</td><td class='name'>{$log[$thread]['name']}</td><td>{$trunccom}</td><td>{$repcount}</td><td>[<a id='arc{$thread}' class='arcLink' href='" . RES_DIR . "{$thread}'>Open</a>]</td></tr>";
                $j++;
            }
        }
        //$dat .= $foot->generate();

        $this->print_page("archive.html", $dat);
    }
    
    private function archive_log() {
        //Cut down version of log cache, for archives.
        
        global $mysql;

        $log = []; // no -> [ data ]
        $lastno = 0;
        $board  = BOARD_DIR;
        $log['THREADS'] = array();
        $mysql->query("SET read_buffer_size=1048576");

        $query = $mysql->query("SELECT * FROM " . SQLARCHIVE . " WHERE board='$board' ORDER BY archived_on DESC");

        while ($row = $mysql->fetch_assoc($query)) {
            if ($row['no'] > $lastno) {
                $lastno = $row['no'];
            }

            // initialize log row if necessary
            if (!isset($log[$row['no']])) {
                $log[$row['no']] = $row;
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
            } else {
                array_push($log['THREADS'], $row['no'])
                $log[$row['resto']]['replies'] = 0;
            }
        }
        
        $mysql->free_result($query);
        
        return $log;
    }
    
    private function delete_archive($no, $automatic = true) {
        require_once(CORE_DIR . "/admin/delete.php");
        $delete = new Delete;
        $delete->arcDel($no, $automatic);
    }
}