<?php
/*

    Generates a listing of OPs.

    The class you didn't know you wanted.
    Don't try this at home.
    
    case 'catalog':
        require_once(CORE_DIR . "/catalog/catalog.php");
        $catalog = new Catalog;
        echo $catalog->formatPage();
        break;

*/

require("post.php");

class Catalog extends Log {
    private $data = [];

    public function generate() {
        require_once(CORE_DIR . "/page/page.php");
        $page = new Page;
        require_once(CORE_DIR . "/postform.php");
        $pf = new PostForm;

        $page->headVars['page']['title'] = "/" . BOARD_DIR . "/ - " . TITLE . " - Catalog";
        array_push($page->headVars['css']['sheet'], "stylesheets/catalog.css");

        $pf->ribbon = [['link' => SITE_ROOT_BD,'name' => 'Return']];
        $temp = $pf->format();

       $static = (defined('STATIC_CATALOG') && STATIC_CATALOG);
         
        if (!$static) {
            $page->headVars['js']['script'] =  ["catalog.js"];
            array_push($page->headVars['js']['raw'], "var catalog = " . $this->catalogJS(). ";");
        }

        $temp .= $this->formatPage($static);
        $out = $page->generate($temp);

        $this->print_page(DATA_PATH_BD . "catalog.html", $out, 0);
    }

    private function formatPage($static) {
        global $my_log;

        $temp = "";
        
        if ($static) {
            $my_log->update_cache();
            $log = $my_log->cache;
            $this->parseOPs();
            $this->parseReplies();
            $this->sortOPs();
            foreach ($this->data as $entry) {
                $temp .= $this->generateOP($log[$entry['no']],$entry);
            }
        }

        $temp .= "<div id='catalog_container'>" . $temp . "</div>";
        $temp .= "<hr>";

        return $temp;
    }

    private function catalogJS() {
        global $my_log;

        $catalog = ["count" =>  (int) count($my_log->cache['THREADS']), "slug" => BOARD_DIR];
        $catalog['threads'] = [];

        foreach ($my_log->cache['THREADS'] as $key) {
            $file = false;
            if ($my_log->cache[$key]['media'] != null) {
                $file = json_decode($my_log->cache[$key]['media'], true);
            }
 
            $pushMe = [
                'no'   => $key,
                'date' => $my_log->cache[$key]['time'],
                'file' => ($file) ? $file[0]['filename'] : null,
                'r'    => count($my_log->cache[$key]['children']),
                'i'    => $my_log->cache[$key]['images'],
                'author' => $my_log->cache[$key]['name'],
                'imgurl' => $file[0]['localthumbname'] : null,
                'tn_w' => $file[0]['thumb_width'] : null,
                'tn_h' => $file[0]['thumb_height'] : null,
                'sub'  => $my_log->cache[$key]['sub'],
                'teaser' => $my_log->cache[$key]['com']
            ];

            foreach ($pushMe as $key) {
                if (empty($key)){
                    unset($key);
                }
            }
            array_push($catalog['threads'], $pushMe);
        }

        return json_encode($catalog);
    }
   
   private function sortOPs() {
        //Seperate stickies from non-stickies to process further.
        $temp = ['sticky' => [], 'regular' => []];
        
        foreach ($this->data as $op) {           
            if ($op['sticky'])
                array_push($temp['sticky'],$op);
            else
                array_push($temp['regular'],$op);
        }

        usort($temp['regular'], function($a, $b) {
            if ($a['last'] == $b['last']) { return 0; }
            return ($a['last'] > $b['last']) ? -1 : 1;
        });
        
        //Additional sticky processing...?
        
        $this->data = array_merge($temp['sticky'], $temp['regular']);
    }
    
    private function generateOP($input,$stats) {
        $post = new CatalogPost;

        return $post->format($input,$stats);
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
}