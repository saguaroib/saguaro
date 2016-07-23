<?php


class SaguaroFilterManagement {

    function init() { //$resno = Thread to rebuild, $rebuild = Whether or not to rebuild indexes.
        global $mysql;
        
        
        
        require_once(CORE_DIR . "/page/page.php");
        $page = new Page;

        $page->headVars['page']['title'] = "/" . BOARD_DIR . "/ - " .  TITLE . " - Filters";
        $page->headVars['page']['sub'] = "Manage word-filtering for posts.";
        $page->headVars['css']['sheet'] = (NSFW) ? array("/stylesheets/admin/nwspanel.css", "/stylesheets/admin/settings.css") : array("/stylesheets/admin/wspanel.css", "/stylesheets/admin/settings.css");

        
        $dat .= "<table class='filterList centered'>
            <th class='postblock'>Field</th>
            <th class='postblock'>Original string</th>
            <th class='postblock'>Replaced with</th>
            <th class='postblock'>Board</th>
            <th class='postblock'>Active</th>";

        
        
        $boards = valid('boardlist');
        $boards = explode(",", $boards);
        
        $querstr = "board='" . BOARD_DIR . "' ";
        foreach ($boards as $board) {
            if ($board != BOARD_DIR) $querstr .= " OR board='{$board}' ";
        }

        $query = "SELECT * FROM " . SQLBLACKLIST . " WHERE {$querstr} ORDER BY active DESC";
        
        $query = $mysql->query($query);
        $j = 1;
        while($row = $mysql->fetch_assoc($query)) {
            $rowtype = ($j % 2) ? "row1" : "row2";
            $row['contents'] = (strlen($row['contents']) > 99) ? substr($row['contents'], 0, 99) . "..." : $row['contents'];
            $row['contents'] = str_replace("<br />", " ",  $row['contents']); 
            $row['replace'] = (strlen($row['replace']) > 99) ? substr($row['replace'], 0, 99) . "..." : $row['replace'];
            $row['replace'] = str_replace("<br />", " ",  $row['replace']); 
            $row['active'] = ($row['active'] == 1) ? "<strong>Active</strong>" : "Inactive";
            
            $dat .= "<tr class='{$rowtype}'><td>{$row['field']}</td><td>{$row['contents']}</td><td>{$row['replace']}</td><td>{$row['board']}</td><td>{$row['active']}</td></tr>";
            $j++;
        }
        
        $dat .= "</table>";
        return $page->generate($dat, false, true);
    }
}