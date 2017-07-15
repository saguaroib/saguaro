<?php

class SaguaroAPI {
    public function generatePages() {
        global $my_log;
        $log = $my_log->cache;
        $this->indexes($log);
        $this->threadList($log);
        $this->catalog($log);
        return;
    }
    
    //Create thread specific .json list provided an OP post #
    public function thread($no) {
        global $my_log;
        $log = $my_log->cache;
        $data = ['posts'=>[]];

        array_push($data['posts'], $this->sanitize($log[$no]));

        if (isset($log[$no]['children'])) {
            foreach ($log[$no]['children'] as $reply => $value) {
                array_push($data['posts'], $this->sanitize($log[$reply]));
            }
        }
        $filepath = API_DIR_RES . $no;
        $this->printFile($filepath, json_encode($data));
    }
    
    //Create json index pages, 0.json, 1.json etc..
    public function indexes($log) {
        $pagecount = 0; //This is the page # to start on. 
        $data = ['threads' => []];
        $temp = ['posts'=>[]];
        $threadcount = 0;
        foreach($log['THREADS'] as $thread) {
            if ($threadcount >= PAGE_DEF) {
                $printfile = API_DIR . "/" . $pagecount;
                $this->printFile($printfile, json_encode($data));
                $data = ['threads' => []];
                $threadcount = 0;
                ++$pagecount;
            }
            array_push($temp['posts'], $this->sanitize($log[$thread]));
            if (isset($log[$thread]['children'])) {
                ksort($log[$thread]['children']);
                array_slice($log[$thread]['children'], -4, 4, true);
                foreach ($log[$thread]['children'] as $reply => $value) {
                    array_push($temp['posts'], $this->sanitize($log[$reply]));
                }
            }
            array_push($data['threads'], $temp);
            $temp = ['posts'=>[]];
            ++$threadcount;
        }
        $printfile = API_DIR . "/" . $pagecount;
        $this->printFile($printfile, json_encode($data));
    }
    
    //Create list of threads, threads.json
    public function threadList($log) {
        $pagecount = 0; //This is the page # to start on. 
        $data = [];
        $temp = ['page' => $pagecount, 'threads'=>[]];
        $threadcount = 0;
        foreach($log['THREADS'] as $thread) {
            $temp['page'] = $pagecount;
            if ($threadcount >= PAGE_DEF) {
                array_push($data, $temp);
                $temp = ['page' => ++$pagecount, 'threads' => []];
                $threadcount = 0;
            }
            array_push($temp['threads'], [
                'no'            => (int) $log[$thread]['no'],
                'last_modified' => (int) $log[$thread]['last_modified']
            ]);
            ++$threadcount;
        }
        array_push($data, $temp);
        $printfile = API_DIR . "/threads";
        $this->printFile($printfile, json_encode($data));
    }

    //Creates catalog.json
    //NOTE: the native catalog uses json generated in /catalog/catalog.php , not here!
    public function catalog($log) {
        $pagecount = 0; //This is the page # to start on. 
        $data = [];
        $temp = ['page' => $pagecount, 'threads'=>[]];
        $threadcount = 0;
        foreach($log['THREADS'] as $thread) {
            $temp['page'] = $pagecount;
            if ($threadcount >= PAGE_DEF) {
                array_push($data, $temp);
                $temp = ['page' => ++$pagecount, 'threads' => []];
                $threadcount = 0;
            }
            array_push($temp['threads'], $this->sanitize($log[$thread], $log));
            ++$threadcount;
        }
        array_push($data, $temp);
        $printfile = API_DIR . "/catalog";
        $this->printFile($printfile, json_encode($data));
    }

    //Remove bad rows from 
    private function sanitize($temp, $catalog = false) {
        $bad = ['host', 'media', 'pwd', 'children', 'root', 'ips', 'permasage', 'last', 'modified'];
        $preserve = ['images','replies','resto', 'imagelimit', 'bumplimit']; //Save these keys even if their value is zero

        if ($temp['media'] != null) {
            $media = json_decode($temp['media'], true);
            $temp['filecount'] = count($media);
            $temp['ext']      = $media[0]['extension'];
            $temp['tn_w']     = $media[0]['thumb_width'];
            $temp['tn_h']     = $media[0]['thumb_height'];
            $temp['w']        = $media[0]['width'];
            $temp['h']        = $media[0]['height'];
            $temp['md5']      = $media[0]['hash'];
            $temp['fsize']    = $media[0]['filesize'];
            $temp['filename'] = $media[0]['filename'];
            //$temp['tim']      = $media[0]['CHANGEME'];
        }

        if (DISPLAY_ID != true) unset($temp['id']);

        if (!$temp['resto']) {
            if ($temp['images'] > 5) $temp['omitted_images'] = $temp['images'] - 5;
            if ($temp['replies'] > 5) $temp['omitted_posts'] = $temp['replies'] - 5;

            $temp['unique_ips'] = count($temp['ips']);
            $temp['imagelimit'] = ($temp['images'] >= MAX_IMGRES) ? 1 : 0;
            $temp['bumplimit'] = ($temp['replies'] >= MAX_RES) ? 1 : 0;
            
            if ($catalog && (count($temp['children']) > 0)) { //Sort catalog's "last_replies" item.
                $temp['last_replies'] = [];
                ksort($temp['children']);
                $temp['children'] = array_slice($temp['children'], -5, 5, true);
                foreach ($temp['children'] as $reply => $_unused) {
                    array_push($temp['last_replies'], $this->sanitize($catalog[$reply])); //welcome to my recursive never ending hell
                }
           }
        }

        foreach ($bad as $unset) { //Remove "bad" keys.
            unset($temp[$unset]);
        }
        foreach ($temp as $key => $value) { //Unset null values.
            if (is_numeric($value)) {
                $temp[$key] = (int) $value;
            }
            if (empty($value) && !in_array($key, $preserve)) {
                unset($temp[$key]);
            }
        }
        return $temp;
    }
    
    //Writes the json files.
    private function printFile($filename, $contents) {
        $filename = $filename . ".json";
        $tempfile = tempnam(realpath(API_DIR), "tmp"); //note: THIS actually creates the file
        file_put_contents($tempfile, $contents, FILE_APPEND);
        rename($tempfile, $filename);
        chmod($filename, 0664); //it was created 0600
    }
}