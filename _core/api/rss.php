<?php

/*
    == SaguaroRSS ==
    Staying trendy on the internet.
    Generates an RSS feed of the first 20 posts in the index
*/

class RSS extends Log{
    
    function generate() {
        
        $this->update_cache();
        $tmplog = $this->cache;
        $threads = $this->cache['THREADS'];
        if (count($threads) < 19) $threads = array_splice($threads, 0, 19);
        
        $temp = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">';
        $temp .= '<channel><title>/' . BOARD_DIR . '/ - ' . TITLE . '</title><link>http://' . SITE_ROOT_BD . '/./</link>';
        $temp .= '<description>Feed for /' . BOARD_DIR . '/ - ' . TITLE . ' at ' . SITE_ROOT . '</description>';
        $temp .= '<atom:link href="http://' . SITE_ROOT_BD . '/index.rss" rel="self" type="application/rss+xml" />';

        foreach ($threads as $key) {
            if (empty($tmplog[$key]['sub']))
                    $title = (strlen($tmplog[$key]['com']) >= 50) ? substr($tmplog[$key]['com'], 0, 49). "..." : $tmplog[$key]['com'];
            else 
                $title = $tmplog[$key]['sub'];
            $temp .= "<item>";
            $temp .= "<title>" . $tmplog[$key]['sub'] . "</title>";
            $temp .= "<link>http://" . SITE_ROOT_BD . RES_DIR . $key  . PHP_EXT . "</link>";
            $temp .= "<title>" . $title . "</title>";
            $temp .= "<link>http://" . SITE_ROOT_BD . RES_DIR . $key  . PHP_EXT . "</link>";
            $temp .= "<guid>http://" . SITE_ROOT_BD . RES_DIR . $key  . PHP_EXT . "</guid>";
            $temp .= "<comments>http://" . SITE_ROOT_BD . RES_DIR . $key  . PHP_EXT . "</comments>";
            $temp .= "<pubDate>" . $tmplog[$key]['now'] . "</pubDate>";
            $temp .= "<dc:creator>" . $tmplog[$key]['name'] . "</dc:creator>";
            $temp .= "<description><![CDATA[ <a href='http://'" . SITE_ROOT_BD . "/" . THUMB_DIR . $tmplog[$key]['tim'] . $tmplog[$key]['ext'] . "' target=_blank>";
            $temp .= "<img style='float:left;margin:8px' border=0 src='http://" . SITE_ROOT_BD . "/" . THUMB_DIR . $tmplog[$key]['tim'] . $tmplog[$key]['ext'] . "' ></a>" . $tmplog[$key]['com'] . " ]]> </description>";
            
            $temp .= "</item>";
        }   
        unset($tmplog, $threads);
        $temp .= "</channel></rss>";        
        $this->print_page("index.rss", $temp);
        
        return true;
    }  
}