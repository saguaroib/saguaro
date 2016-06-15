<?php

/*
    Generates file-upload board content
    Experimental.
*/

class SaguaroFileBoard {
    
    public function generateTable() {
        $temp = "<table class='fileList'>";
        $temp .= "<th class='postblock' id='numcol'>" . S_NUMPREFIX . "</th>";
        $temp .= "<th class='postblock' id='namecol'>" . S_NAME . "</th>";
        $temp .= "<th class='postblock' id='fnamecol'>" . S_FILE . "</th>";
        $temp .= "<th class='postblock' id='tagcol'>" . S_TAG . "</th>";
        $temp .= "<th class='postblock' id='subcol'>" . S_SUBJECT . "</th>";
        $temp .= "<th class='postblock' id='fsizecol'>" . S_FSIZE . "</th>";
        $temp .= "<th class='postblock' id='datecol'>" . S_NOW . "</th>";
        $temp .= "<th class='postblock' id='repcol'>" . S_REPLIES . "</th>";
        $temp .= "<th class='postblock' colspan='1'></th>";
        
        return $temp;
    }
    
    public function generateRow($post, $no, $j) { //Generates a row for the index.
        
        $nowParts = explode(" ", $post['now']);
        $longTag = end($nowParts);
        $truncsub = (strlen($post['sub']) > 99) ? substr($post['sub'], 0, 99) . "..." : $post['sub'];
        
        $fsize = $post['fsize'];
        if ($fsize >= 1048576) {
            $fsize = round(($fsize / 1048576), 2) . " M";
        } else if ($fsize >= 1024) {
            $fsize = round($fsize / 1024) . " K";
        } else {
            if ($fsize = -1) {
                return; //File deleted, handle "ghost" thread cleanup here eventually.
            }
        }
        $class = ($j % 2) ? "class='alt'" : "";
        $dat .= "<tr {$class} id='p{$no}'>"; 
        $dat .= "<td>{$no}</td>"; //No. 
        $dat .= "<td class='namecol'><span class='name'>{$post['name']}</span></td>"; //Name
        $dat .= "<td>[<a href='" . IMG_DIR . $post['tim'] . $post['ext'] . "' target='_blank'>{$post['fname']}</a>]</td>"; //Filename
        $dat .= "<td>[<span title='{$longTag}'>{$longTag[0]}]</span></td>"; //Tag
        $dat .= "<td><span class='subject'>{$truncsub}</span></td>"; //Subject
        $dat .= "<td>{$fsize}B</td>"; //FSize
        $dat .= "<td>{$nowParts[0]}{$nowParts[1]}{$nowParts[2]}</td>"; //Date
        $dat .= "<td>" . count($post['children']) ."</td>"; //ReplyCount
        $dat .= "<td>[<a href='" . RES_DIR . "{$no}'>Reply</a>]</td></tr>"; //Reply link
        
        return $dat;
    }
}