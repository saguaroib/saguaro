<?php

/*
    News class, allows user to modify the contents of their 
    boardlist/board announcement files from the panel.
    Could possibly be extended to implement website front pages
    in the future.    
    
    Testing:
    
    require_once(CORE_DIR . "/admin/news.php");
    $news = new News; //lol
    [Update contents]
        $news->newsUpdate($bl, $anno);
    [Get contents]
        $news->newsGetFile($file);
    [Display edit panel]
        $news->newsPanel();
*/

class News {
    
    function newsUpdate($write, $file) {
    
    $write = preg_replace('/^<\?php(.*)(\?>)?$/s', '$1', $write); //http://stackoverflow.com/questions/3154644/php-removing-php-tags-from-a-string
    
        if ($file === "boardlist")
            return file_put_contents(BOARDLIST, $write);
        if ($file === "globAnno");
            return file_put_contents(GLOBAL_NEWS, $write);
            
        return error(S_UPDERR);
    }
    
    private function newsGetFile($file) {
        //Get contents of file, otherwise return error message. 
        if ($file == 1) {
            $get = BOARDLIST;
            return $file = (file_exists($get)) ? file_get_contents(BOARDLIST) : "Board list txtfile not found! Check your config setting for BOARDLIST!";
        }
        
        if ($file == 2)  {
            $get = GLOBAL_NEWS;
            return $file = (file_exists($get)) ? file_get_contents(GLOBAL_NEWS) : "Global news txtfile not found! Check your config setting for GLOBAL_NEWS!";
        }
        return error(S_UPDERR);
    }
    
    function newsPanel() {
        //Show the edit panel
        
        $bl = $this->newsGetFile(1);
        $anno = $this->newsGetFile(2);
        
        $temp = "<br><div class='container' style='text-align:center;'><div class='managerBanner' >Edit Boardlist or Global announcements</div><br><br>";
        $temp .= "<b>Edit HTML contents of your board list ( " . BOARDLIST . " )<br>";
        $temp .= "<form action='" . PHP_ASELF_ABS . "?mode=news' method='post'><input type='hidden' name='file' value='boardlist'><textarea name='update' cols='100' rows='15'/>" . $bl . "</textarea><br><input type='submit' value='Submit'></form>";
        $temp .= "<br><br><hr style='width:40%;'><br>";
        $temp .= "<b>Edit HTML contents of your board news ( " . GLOBAL_NEWS . " ). For unique board announcements, reference different filepaths in the config. <br>If you want all the boards to share an announcement, reference the same filepath in the config.<br>";
        $temp .= "<form action='" . PHP_ASELF_ABS . "?mode=news' method='post'><input type='hidden' name='file' value='globAnno'><textarea name='update' cols='100' rows='15'/>" . $anno . "</textarea><br><input type='submit' value='Submit'></div></form>";
        $temp .= "<div class='container' style='text-align:center;'><br><br>You will need to [<a href='?mode=rebuild'>Rebuild</a>] your pages for updates to be immediately visible. Otherwise, allow users to update pages via posting normally.</div>";
        
        return $temp;
        
    }
    
}

?>