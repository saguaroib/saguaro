<?php

class Report {
    
	
	function process() {
		if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
			$no = $_GET['no'];
			//Various checks in the popup window before form is filed
			if ( !$this->report_post_exists( $no ) )
				$this->error('That post doesn\'t exist anymore.', $no);
			if ( $this->report_post_isSticky( $no ) )
				$this->error('Stop trying to report a sticky.', $no);
			$this->report_check_ip( BOARD_DIR, $no );
			$this->form_report( BOARD_DIR, $_GET['no'] );			//User passed checks, display form

		} else {
			//Report form has been filled out, POST'ed and can now be filed
			$this->report_check_ip( BOARD_DIR, $_POST['no'] );
			$this->report_submit( BOARD_DIR, $_POST['no'], $_POST['cat'] );
		}
		die( '</body></html>' );		
	}
	
	
    function get_all_reports_board($list = 0) {
        $query = mysql_query(" SELECT * FROM reports WHERE board='" . BOARD_DIR . "' ");
		if (!$list) { //If the call is for the oldvalid() alert in admin.php, this will be 1.	
			$active = mysql_num_rows( $query );
			if ( $active > 0 )
				$active = "<b><font color='red'/>$active Reports!</font></b>";
			else
				$active = "Reports";
		} else {
			$active = $query;
		}
		return $active;
    }
    
    function report_post_exists( $no ) {
        $query = mysql_query( "SELECT * FROM " . SQLLOG . " WHERE no=" . $no . " LIMIT 1" );
        $row   = mysql_fetch_row( $query );
        if ( $row[0] )
            return true;
    }
    
    function report_post_isSticky( $no ) {
        $query = mysql_query( "SELECT sticky FROM " . SQLLOG . " WHERE no=" . $no . " LIMIT 1" );
        $row   = mysql_fetch_row( $query );
        if ( $row[0] == 1 )
            return true;
    }
    
    function report_check_ip($board, $no ) {
        //I don't know what's going on here
        //Maybe check if the submitting user has already reported this ip? or is going on a reporting spree?
    }
    
    function report_submit( $board, $no, $type ) {
		require_once(CORE_DIR . "/general/captcha.php");
		$captcha = new Captcha;        
        
        $style = (NSFW) ? "saguaba" : "sagurichan";
        
        if ($captcha->isValid() !== true) {
            die("<head><link rel='stylesheet' type='text/css' href='" . CSS_PATH . "/stylesheets/" . $style . ".css'/></head><body>
        <center><font color=blue size=5>You did not solve the captcha correctly.</b></font><br><br>[<a href='" . PHP_SELF . "?mode=report&no=" . $no . "'>Try again?</a>]</center></body>");}
        //cat = 1: Rule violation
        //cat = 2: Illegal content
        //cat = 3: Advertising
        $host   = $_SERVER['REMOTE_ADDR'];
        $cboard = mysql_real_escape_string( $board );
        $cno    = mysql_real_escape_string( $no );
        $ctype  = mysql_real_escape_string( $type );
        mysql_call( "INSERT INTO reports (`num`, `no`, `board`, `type`, `time`, `ip`) VALUES ( '" . rand() . "', '" . $cno . "', '" . $cboard . "', '" . $ctype . "', NOW(), '" . $host . "') " );
        
        echo "<head><link rel='stylesheet' type='text/css' href='" . CSS_PATH . "/saguaba.css'/><script>function loaded(){window.setTimeout(CloseMe, 3000);}function CloseMe() {window.close();}</script></head><body onLoad='loaded()'>
	<center><font color=blue size=5>Report submitted! This window will close in 3 seconds...</b></font></center></body>";
    }
	
	function form_head($no) {
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		<html>
		<head>
		<title>Report Post #' . $no . '</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" type="text/css" href="' . CSS_PATH . '/saguaba.css"/>
		<style>fieldset { margin-right: 25px; }</style>
		</head>';
	}
	
    function form_report( $board, $no ) {
		require_once(CORE_DIR . "/general/captcha.php");
		$captcha = new Captcha;
        if (RECAPTCHA) 
            $temp .= "<tr><td colspan='2'><script src='//www.google.com/recaptcha/api.js'></script><div class='g-recaptcha' data-sitekey='" . RECAPTCHA_SITEKEY ."'></td></tr>";
        else 
            $temp .= "<tr><td><img src='" . CORE_DIR_PUBLIC . "/general/captcha.php' /></td><td><input type='text' name='num' size='20' placeholder='Captcha'></td></tr>";
		//Taken from parley who probably took it from 4chan anyway. Yolo.
		$this->form_head($no);
		echo '
		<body>
		<form action="' . PHP_SELF_ABS . '?mode=report&no=' . $no . '" method="POST">
		<table width="100%">
		<tr><td>
		<fieldset><legend>Report type</legend>
		<input type="hidden" name="no" value="' . $no . '" />
		<input type="radio" name="cat" value="2" checked>Rule violation<br/>
		<input type="radio" name="cat" value="3">Illegal content<br/>
		<input type="radio" name="cat" value="1">Spam
		</fieldset>
		</td><td>
		</td>
		<td>' . $temp . '
		</td></tr>
		</table>
		<table width="100%"><tr><td width="240px"></td><td>
		<input type="submit" value="Submit">
		</td></tr></table>
		</center>
		</form>
		<br>
		<div class="rules"><u>Note</u>: Submitting frivolous reports will result in a ban. When reporting, make sure that the post in question violates the global/board rules, or contains content illegal in the United States.</div>
		</body>
		</html>';

	   /* if ($captcha->isValid() === false)
			die(error(S_CAPFAIL, $dest));
		}*/
		
	}
	
	function display_list() {

	$active = mysql_query(" SELECT * FROM reports WHERE board='" . BOARD_DIR . "' ORDER BY `type` DESC ");
	
	if ( !$result = mysql_call( "select * from " . SQLLOG . "" ) ) {
            echo S_SQLFAIL;
        }
	
    if ( !$active ) {
        echo S_SQLFAIL;
    }
    $j = 0;
	
    echo "<input type=hidden name=mode value=admin>\n";
    echo "<input type=hidden name=pass value=\"$pass\">\n";
    echo "<div class=\"dellist\">" . S_DELLIST . "</div>\n";
    echo "<div class=\"delbuttons\"><input type=submit value=\"" . S_ITDELETES . "\">";
    echo "<input type=reset value=\"" . S_MDRESET . "\">";
    echo "<table class=\"postlists\">\n";
    echo "<tr class=\"postTable head\"><th>Delete Post</th><th>Clear Report</th><th>Post Number</th><th>Image</th><th>Board</th><th>Reason</th><th>Reporting IP</th><th>Post info</th>";
    echo "</tr>\n";
	
    while ( $row = mysql_fetch_row( $active ) ) {
        $j++;
		$path = realpath( "./" ) . '/' . IMG_DIR;
        list( $num, $no, $board, $type, $time, $ip ) = $row;
		
		switch ($type) {
			case '1':
				$type = 'Spam';
				break;
			case '2':
				$type = 'Rule Violation';
				break;
			case '3':
				$type = 'Illegal Content';
				break;
			default:
				$type = 'Type Error';
				break;
		}
		
        if ( $ext && is_file( $path . $tim . $ext ) ) {
            $clip     = "<a class=\"thumbnail\" target=\"_blank\" href=\"" . IMG_DIR . $tim . $ext . "\">" . $tim . $ext . "<span><img class='postimg' src=\"" . THUMB_DIR . $tim . 's.jpg' . "\" width=\"100\" height=\"100\" /></span></a><br />";
		}
		$class = ( $j % 2 ) ? "row1" : "row2"; //BG color
        echo "<br /><br /><link rel='stylesheet' type='text/css' href='" . CSS_PATH . "/img.css' />";

        echo "<tr class=$class><td><input type=radio name=\"$no\" value=delete></td><td><input type=radio name=\"$no\" value=clear></td>";
        echo "<td>$no</td><td>$clip</td><td>/$board/</td><td>$type</td><td>$ip</td>
		<td><input type=\"button\" text-align=\"center\" onclick=\"location.href='" . PHP_ASELF_ABS . "?mode=more&no=" . $no . "';\" value=\"Post Info\" /></td>";
        echo "</tr>\n";
    }
    //mysql_free_result( $active );
    //die( "</body></html>" );		
	}
	
	function error($mes, $no) {
		
		$this->form_head($no);
		echo "<br /><br /><center><font color=blue size=5>$mes</b></font></center>";
        die( "</body></html>");
	}
}


?>