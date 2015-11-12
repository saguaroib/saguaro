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
        
        $write = htmlspecialchars($write);
        
        if ($file === "boardlist")
            return file_put_contents(BOARDLIST, $write);
        if ($file === "globAnno");
            return file_put_contents(GLOBAL_NEWS, $write);
            
        return error("There was an error updating the file.");
    }
    
    function newsGetFile($file) {
        //Get contents of file, otherwise return error message.
        $file = (file_exists($file)) ? file_get_contents(htmlspecialchars_decode($file)) : "File " . $file . " not found! Check your config setting for the file!";
        return $file;
    }
    
    function newsPanel() {
        //Show the edit panel
        
        $bl = $this->newsGetFile(BOARDLIST);
        $anno = $this->newsGetFile(GLOBAL_NEWS);
        
        $temp = "<br><div class='container' style='text-align:center;'><div class='managerBanner' >Edit Boardlist or Global announcements</div><br><br>";
        $temp .= "<b>Edit HTML contents of your board list ( <font color='red'>" . BOARDLIST . "</font> )<br>";
        $temp .= "<form action='" . PHP_ASELF_ABS . "?mode=editNews' method='post'><input type='hidden' name='file' value='boardlist'><textarea name='update' cols='100' rows='15'/>" . $bl . "</textarea><br><input type='submit' value='Submit'>";
        $temp .= "<br><br><hr style='width:40%;'><br>";
        $temp .= "<b>Edit HTML contents of your board news ( <font color='red'>" . GLOBAL_NEWS . "</font> ). For announcements unique to a board, reference unique filepaths in the config. <br>If you want all the boards to share an announcement, reference the same filepath in the config.<br>";
        $temp .= "<form action='" . PHP_ASELF_ABS . "?mode=editNews' method='post'><input type='hidden' name='file' value='globAnno'><textarea name='update' cols='100' rows='15'/>" . $anno . "</textarea><br><input type='submit' value='Submit'></div>";

        return $temp;
        
    }
    
}

?>
