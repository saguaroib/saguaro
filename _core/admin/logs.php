<?php


class SaguaroAdminLog {
    public function generate() {
        global $mysql;
        
        if (!SHOW_ADMIN_LOGS && !valid("janitor")) {
            error(S_LOGSDISABLED);
        }
        
        if (isset($_GET['page']) && is_numeric($_GET['page'])) {       
            $pagenum =(int) $_GET['page'];
            $pageupper = $pagenum * 150;
            $pagelower = $pageupper - 150;
        } else {
            $pagelower = 0;
            $pageupper = 149;
        }
        
        //if (!isset($_GET['board'])) {
            $board = BOARD_DIR;
        /*} else {
            if (isset($_GET['board'])) {
                $board = $mysql->escape_string($_GET['board']);
            } else {
                error(S_BADBOARD);
            }
        }*/
        
        switch ($_GET['m']) {
            case 'sys':
                $type = "type='1'";
                $actionMessage = "System deleted";
                $subtitle = "System actions for board: /{$board}/";
                break;
            case 'del':
                $type = "type='2'";
                $actionMessage = "Deleted post";
                $subtitle = "Deletion logs for board: /{$board}/";
                break;
            case 'bans':
                $type = "type='3'";
                $actionMessage = "Banned user";
                $subtitle = "Ban logs for board: /{$board}/";
                break;
            case 'reports':
                $type = "type='4'";
                $actionMessage = "Cleared report";
                $subtitle = "Report logs: /{$board}/";
                break;
            case 'modify':
                $type = "type='5'";
                $actionMessage = "Modified thread status";
                $subtitle = "Special thread actions for board: /{$board}/";
                break;
            case 'capcode':
                $type = "type='6'";
                $actionMessage = "Made admin post";
                $subtitle = "Admin posts made on board: /{$board}/";
                break;
            default:
                $type = "type<>0";
                $mode = "all";
                $subtitle = "Displaying all logs for board: /{$board}/";
                break;
        }
        
        $query = "SELECT * FROM " . SQLDELLOG . " WHERE {$type} AND board='{$board}'";

        if (isset($_GET['name'])) {
            $query .= " AND name='" . $mysql->escape_string($_GET['name']) . "'";
        }
        
        $query .= " ORDER BY time DESC LIMIT {$pagelower}, {$pageupper}";
        
        require_once(CORE_DIR . "/page/page.php");
        $page = new Page;
        $page->headVars['page']['title'] = "/{$board}/ - " . TITLE . " - Logs";
        $page->headVars['page']['sub']   = $subtitle;
        $page->headVars['css']['raw']    = array(
            "table.actionList {text-align: center; margin: auto; width:75%;}"
        );
        
        require_once(CORE_DIR . "/postform.php");
        $form = new PostForm;
        $dat .= $form->format(false, false, 1);
        
        $dat .= "<div style='text-align:center;margin:auto;width:75%;'>";
        $dat .= "<form action'" . PHP_SELF_ABS . "' method='GET'>";
        $dat .= "<input type='hidden' name='mode' value='logs'>";
        $dat .= "Filter by action: <select name='m'>
          <option>Show all</option>
          <option value='sys'>Self-deletions</option>
          <option value='del'>Staff deletions</option>
          <option value='bans'>Bans & Warnings</option>
          <option value='reports'>Reports</option>
          <option value='modify'>Modified threads</option>
          <option value='capcode'>Admin posts</option>
        </select> | Page: <input type='numeric' size='3' maxlength='3' name='page'><input type='submit' value='Filter'></form></div>";
        
        $dat .= "<table class='actionList'>";
        
        $dat .= "<tr>";
        
        if (SHOW_ADMIN_NAMES || valid("janitor"))
            $dat .= "<th class='postblock'>" . S_NAME . "</th>";
        
        $dat .= "<th class='postblock'>" . S_DATE . "</th>";
        
        $dat .= "<th class='postblock'>" . S_ACTION . "</th>
            <th class='postblock'>" . S_BOARD . "</th>
            <th class='postblock' colspan='1'></th></tr>";
        
        $query = $mysql->query($query);
        $j     = 0;
        
        require_once(CORE_DIR . "/general/calculate_age.php");
        $age = new CalculateAge;
        
        while ($row = $mysql->fetch_assoc($query)) {
            $tablerow = ($j % 2) ? "row1" : "row2";
            
            $dat .= "<tr class='{$tablerow}'>";
            
            if (SHOW_ADMIN_NAMES || valid("janitor"))
                $dat .= "<td>{$row['admin']}</td>";
            
            $dat .= "<td>" . $age->calculate($row['time']) . "</td>";
            $dat .= "<td>{$row['action']}</td>";
            $dat .= "<td>{$row['board']}</td>";
            $dat .= ($row['type'] >= 4) ? "<td>[<a href='" . PHP_SELF_ABS . "?res={$row['postno']}' target='_blank'>View</a>]</td>" : "<td colspan='1'></td>";

            $dat .= "</tr>";
            ++$j;
        }
        
        $dat .= "</table>";
        
        $dat .= "<div style='text-align:center;margin:auto;width:75%;'>";
            
            return $page->generate($dat, $noHead = false);
    }
}
