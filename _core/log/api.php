<?php
/* 
        
        Called in Regist after every new post, Called in delpost for every post deletion.
        Renders post table in json, currently outputs json files to API_PATH: 
         - Entire catalog as {catalog.json}
         - Single thread as {thread/{threadnumber}.json}
         - Page index as {pagenumber}.json

        Usage:
            require_once(CORE_DIR . "/log/api.php");
            $api = new SaguaroAPI;
        [All]    
            $api->apiGrabAll(THREADNUMBER);
        [Thread only]
            $api->apiThread(THREADNUMBER, $api->apiLog);
        [Catalog only]
            $api->apiCatalog($api->apiLog);
        [Pages only]
            $api->apiPage($api->apiLog);
*/

class SaguaroAPI {
    
    function apiGrabAll($no = 0) {
        $log = $this->apiLog();
        
        $this->apiThread($no, $log);
        $this->apiCatalog($log);
        $this->apiPage($log);

    }
    
    function apiThread($no = 0, $log) {
        
        $logout = array(
            "posts" => array($log[$no])
            );
        
        print_r(array_keys($log[$no]['children']));
        
        foreach (array_keys($log[$no]['children']) as $values) {
            $log2 = array(
                "posts" => $log[$values]
            );
        }
        //$log2= array_push($logout, $log2);
            echo json_encode($log2);
        //$this->apiWrite('thread' . $no, $log);
    }
    
    function apiCatalog($log) {
//PAGE_DEF   Threads per page.
//PAGE_MAX   Maximum number of pages, posts that are pushed past the last page are deleted.
        for ($onPage = 0; $onPage < PAGE_DEF; $onPage++) {
            $log = array(
                "page" => $onPage,
                "threads" => array_values(
                    $this->apiLog()
                )
            );
        }
        
        /*$log = array(
            "page" => $onPage,
            "threads" => array_values(
                $this->apiLog()
            )
        );*/
        
        echo json_encode($log);

        //$this->apiWrite('catalog', $log);
    }
    
    function apiPage($log) {
        
        /*foreach ($log['no'] as $value) {
            $paged = array(
                "posts" => array_values($log[$value])
                );
                        
        }
        echo json_encode($log);
        /*$log = array(
            "threads" => array($paged)
        );*/
        
        //echo json_encode($paged);

        //$this->apiWrite('catalog', $log);
    }
    
    function apiWrite($type = 'none', $input) {
        
        $path   = API_PATH . $type . ".json";
        $output = json_encode($input);
        
        $fp = fopen($path, 'w');
        fwrite($fp, $output);
        fclose($fp);
    }
    
    function apiCleanup($no = 0) {
        //Remove expired json thread files. Since catalog and pages aren't unique files, only thread json needs to be deleted.
        $path = API_PATH . '/thread/' . $no;
        if (!unlink($path))
            return false;
    }
    
    function apiLog() {
        //This annoys the RePod. Specalized log class that does all the heavy lifting/computing. death 2 multidimensional arrays.
        $log = []; // $log[ Thread # ] [ data ]
        $lastno = 0;
        
        mysql_call("SET read_buffer_size=1048576");
        $mysql_unbuffered_reads = 1;
        //Leave out sensitive values such as host, pwd. I couldn't find any easy ways to just exclude them from the query
        if(!$query = mysql_call("SELECT * FROM " . SQLLOG . " ORDER BY root DESC"))
            return; //oh boy
        
        while ($row = mysql_fetch_assoc($query)) {
            if ($row['no'] > $lastno) {
                $lastno = $row['no'];
            }

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
            if ($row['resto']) { // initialize whatever we need to

                if (!isset($log[$row['resto']])) 
                    $log[$row['resto']] = array();

                if (!isset($log[$row['resto']]['children'])) 
                    $log[$row['resto']]['children'] = array();

                if (!isset($log[$row['resto']]['replies'])) {
                    $log[$row['resto']]['replies'] = 0;
                    $log[$row['resto']]['replies']++;   
                } else  
                    $log[$row['resto']]['replies']++;                
                
                if (!isset($log[$row['resto']]['images']))
                    $log[$row['resto']]['images'] = 0;
                
                if ($row['fsize']) 
                    $log[$row['resto']]['images']++;

                if ($log[$row['resto']]['replies'] >= MAX_RES) 
                    $log[$row['resto']]['bumplimit'] = 1;
                else
                    $log[$row['resto']]['bumplimit'] = 0;
                
                if ($log[$row['resto']]['images'] >= MAX_IMGRES) 
                    $log[$row['resto']]['imagelimit'] = 1;
                else 
                    $log[$row['resto']]['imagelimit'] = 0;
                
                // add this post to list of children
                $log[$row['resto']]['children'][$row['no']] = 1;
                
                $badRepKeys = ['sub']; //No subject field for replies. For now.
                foreach($badRepKeys as $bad)
                    unset($log[$row['no']][$bad]);
                
                unset($log[$row['no']]['children']); //Unset children for replies 
            }
            
            //Cleanup empty or sensitive values. If you want something removed from log, add it to this array.
            $neverShow = ['host', 'email', 'pwd', 'root', 'permasage', 'locked', 'sticky', 'board'];
            $noimg = ['fsize', 'w','h', 'tn_w', 'tn_h', 'md5', 'fname', 'ext', 'tim'];
            
            foreach ($neverShow as $bad)
                unset($log[$row['no']][$bad]);
            
            //Unset img keys if no img exists.
            if ($log[$row['no']]['fsize'] < 1) {
                foreach ($noimg as $img) 
                    unset($log[$row['no']][$img]);
            }
            
            //Convert all values that SHOULD be integers. Otherwise they are spit out as strings.
            $stringToInt = ['no', 'time', 'resto','fsize', 'w','h', 'tn_w', 'tn_h', 'md5', 'fname', 'tim'];
            foreach($stringToInt as $convert)
                if ($log[$row['no']][$convert])
                    $log[$row['no']][$convert] = (int) $log[$row['no']][$convert];
        }
        
        $this->cache = $log;
        return $log;
    }
    
}


?>