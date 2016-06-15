<?php
/*
    Read-only API for Saguaro post log... table thing.
    
    For regist: paste this somewhere.
    If a user is replying to a thread, it requires the user's post # as well as the OP thread #.
    If a user is creating the thread, it requires the op thread # twice. The api handles the rest.
    
    if (API_ENABLED){
        require_once(CORE_DIR . "/api/apoi.php");
        $API = new SaguaroAPI;
        $API->formatThread($no, $replyTo);
    }
*/

class SaguaroAPI extends Log {

    public function formatThread($no, $replyTo) { //Used by regist exclusively to generate individual thread json
        $print = $this->process($no, $replyTo, 0, 'posts');
        $path = API_DIR_RES . $no;
        $this->output($path, $print);
        return true;
    }
   
   public function formatPage($page, $threads) { //Used by log to generate index page json.
        $out['threads'] = array();
        foreach ($threads as $value) {
            array_push($out['threads'], $this->process($value, false, 5, 'posts'));
        }
        $path = API_DIR . $page;
        $this->output($path, $out);

        return true;
    }
    
    public function formatOther($threads) {
        $catalog = $this->formatLists($threads, false);
        $threadList = $this->formatLists($threads, true);
        $catalogPath = API_DIR . "catalog";
        $threadListPath = API_DIR . "threads";
        $this->output($catalogPath, $catalog);
        $this->output($threadListPath, $threadList);
    }
    
    private function formatLists($in, $threadmode) {
        $out = array();
        foreach ($in as $page => $thread) {
            $threadarr = array();
            foreach ($thread as $value) {
                array_push($threadarr, $this->processPage($value, $threadmode));
            }
            $merge = array("page" => $page, "threads" => $threadarr);
            array_push($out, $merge);
        }
        return $out;
    }
    
    private function processPage($no, $threadmode = false) { //Formats for catalog.json & threads.json
        $no = (int) $no;
        $tmplog = $this->cache[$no];
        $children = array_keys($tmplog['children']);
        if ($threadmode) { //Formatting for threads.json
            rsort($children);
            $time = (int) $this->cache[$children[0]]['time'];
            $tmplog = array('no' => $no, 'last_modified' => $time);
            unset($children);
        } else { //Formatting for catalog.json
            sort($children);
            array_slice($children, 0, 5, true);

            $tmplog = $this->sanitize($no);
            $tmplog['last_replies'] = array();
            foreach ($children as $thread) {
                array_push($tmplog['last_replies'], $this->sanitize($thread));
            }
            unset($children);
        }
        return $tmplog;
    }

    private function process($no, $replyTo = 0, $maxChildren = 0, $name) {
        $this->update_cache();
        $no = (int) $no;
        //We're rebuilding the parent file, otherwise we're creating the parent file.
        $preserve = ($replyTo) ? array_keys($this->cache[$replyTo]['children']) : array_keys($this->cache[$no]['children']); 
        $out[$name] = array($this->sanitize($no));

        //Maximun number of replies to an OP to display in the *.json. Used for generating catalog/index pages.
        if ($maxChildren > 0 && (count($preserve) > ($maxChildren - 1))) { 
            $maxChildren =  $maxChildren - 1;
            rsort($preserve, SORT_NUMERIC); //Get latest posts only
            $preserve = array_slice($preserve, 0, $maxChildren, true); //List of children #'s, max # of reply previews, preserve keys
        }
        
        foreach ($preserve as $value) {
            $push = $this->sanitize($value, true);
            array_push($out[$name], $push);
        }

        return $out;
    }

    private function sanitize($no, $reply = false) {
        $temp = $this->cache[$no]; //Translate it to a temporary value so we don't modify the actual cache.
        $bad = ['host', 'pwd', 'children', 'modified', 'last'];
        $preserve = ['bumplimit', 'imagelimit','images','replies','omitted_posts','omitted_images','resto']; //Save these keys even if their value is zero

        $idparts = explode(" ", $temp['now']);
        if (DISP_ID) {
            $temp['id'] = substr($idparts[3], 3, 11);
        }
        $temp['now'] = $idparts[0] . $idparts[1] . $idparts[2];
        $temp['filename'] = $temp['fname']; unset($temp['fname']);
        if (COUNTRY_FLAGS) {
            $temp['flag'] = substr($idparts[6], 0, -2);
        }
        unset($idparts);
        if (strpos($temp['name'], '">')) {
            $name = explode('">', $temp['name']);
            $temp['trip'] = $name[1];
            unset($name);
        }
        if (!$reply) {
            if ($temp['closed'] == 2) {
                $temp['archived'] = 1;
            }
            if ($temp['images'] > 5) {
                $temp['omitted_images'] = $temp['images'] - 5;
            }
            if ($temp['replies'] > 5) {
                $temp['omitted_posts'] = $temp['replies'] - 5;
            }
        }
        
        foreach ($bad as $unset) { //Remove "bad" keys.
            unset($temp[$unset]);
        }

        foreach ($temp as $key => $value) { //Convert to integers where necessary.
            if (is_numeric($value)) {
                $temp[$key] = (int) $value;
            }
        }        

        foreach ($temp as $key => $value) { //Unset null values.
            if (empty($value)) {
                if (!in_array($key, $preserve)) unset($temp[$key]);
            }
        }

        return $temp;
    }
    
    private function output($filename, $contents) { //Generate the .json files
        $filename = $filename . ".json"; //Make *.json files only!
        $contents = json_encode($contents);
        file_put_contents($filename, $contents);
        chmod($filename, 0664); //it was created 0600
    }
}
?>
