<?php
session_start();
/*
=================================
===Saguaro Imageboard Software===
=================================
>>0.99.0
http://saguaroimgboard.tk/download/
the above link will have the latest version.

This is a branch off of futallaby and is currently in development because 
I felt like doing this and have always prefered imageboards to phpbb clones.

Special thanks to !KNs1o0VDv6, Glas, Anonymous from vchan, RePod, and anyone who actually uses this.
If you need help you can reach me at spoot@saguaroimgboard.tk
or http://saguaroimgboard.tk/sug/
or if you would like to help development and have php experience.
If you need help setting saguaro up, check http://saguaroimgboard.tk/suprt/
Remember to look through older threads and see if your problem wasn't solved already!

*/
include "config.php";
include "lang/language.php"; //Language file.

if ( DEBUG_MODE ) {
	ini_set( 'display_errors', 1 );
	$admin = 1;
} else {
	ini_set( 'display_errors', 0 );
}

$num     = $_REQUEST['num'];
$capkeyx = substr( $_SESSION['capkey'], 0, 5 );

extract( $_POST );
extract( $_GET );
extract( $_COOKIE );

$host = $_SERVER['REMOTE_ADDR'];
$con  = mysql_connect( SQLHOST, SQLUSER, SQLPASS );

if ( !$con ) {
	echo S_SQLCONF; //unable to connect to DB (wrong user/pass?)
	exit;
}

$db_id = mysql_select_db( SQLDB, $con );
if ( !$db_id ) {
	echo S_SQLDBSF;
}

if ( $num == $capkeyx ) {
	$auth = 1;
}

$upfile_name = $_FILES["upfile"]["name"];
$upfile      = $_FILES["upfile"]["tmp_name"];

$path = realpath( "./" ) . '/' . IMG_DIR;
ignore_user_abort( TRUE );
$badstring = array(
	 "nimp.org" 
); // Refused text
$badfile   = array(
	 "dummy",
	"dummy2" 
); //Refused files (md5 hashes)

if ( !table_exist( SQLLOG ) ) {
	echo ( S_TCREATE . SQLLOG . "<br />" );
	$result = mysql_call( "create table " . SQLLOG . " (primary key(no),
    no    int not null auto_increment,
    now   text,
    name  text,
    email text,
    sub   text,
    com   text,
    host  text,
    pwd   text,
    ext   text,
    w     int,
    h     int,
    tim   text,
    time  int,
    md5   text,
    fsize int,
    fname text,
    sticky int,
    permasage int,
    locked int,
    root  timestamp,
    resto int,
    board text)" );
	
	if ( !$result ) {
		echo S_TCREATEF . SQLLOG . "<br />";
	}
}

if ( !table_exist( SQLBANLOG ) ) {
	echo ( S_TCREATE . SQLBANLOG . "<br />" );
	$result = mysql_call( "create table " . SQLBANLOG . " (
    ip   VARCHAR(25) PRIMARY KEY,
    pubreason  VARCHAR(250),
    staffreason  VARCHAR(250),
    banlength  VARCHAR(250),
    placedOn VARCHAR(50),
    board VARCHAR(50))" );
	
	if ( !$result ) {
		echo S_TCREATEF . SQLBANLOG . "<br />";
	}
}

function prune_old()
{
	//This prunes old posts that are pushed off the bottom of last page, called once after each post is made in regist()
	if (PAGE_MAX >= 1) {
		
		$maxposts   = LOG_MAX;
		$maxthreads = (PAGE_MAX > 0) ? (PAGE_MAX * PAGE_DEF) : 0;
		//number of pages x how many threads per page
		
		if ($maxthreads) {
			$result          = mysql_call("SELECT no FROM " . SQLLOG . " WHERE sticky=0 AND resto=0 ORDER BY root ASC");
			$threadcount = mysql_num_rows($result);
			while ($row = mysql_fetch_array($result) and $threadcount >= $maxthreads) {
				//This does the pruning
				mysql_call( "delete from " . SQLLOG . " where no=" . $row['no'] . " or resto=" . $row['no'] . "");
				$threadcount--;
			}
			mysql_free_result($result);
		}
			updatelog();
	}
}


function updatelog( $resno = 0 )
{
	global $path;
	
	$find  = false;
	$resno = (int) $resno;
	if ( $resno ) {
		$result = mysql_call( "select * from " . SQLLOG . " where root>0 and no=$resno" );
		if ( $result ) {
			$find = mysql_fetch_row( $result );
			mysql_free_result( $result );
		}
		if ( !$find )
			error( S_REPORTERR );
	}
	if ( $resno ) {
		if ( !$treeline = mysql_call( "select * from " . SQLLOG . " where root>0 and no=" . $resno . " order by root desc" ) ) {
			echo S_SQLFAIL;
		}
	} else {
		if ( !$treeline = mysql_call( "select * from " . SQLLOG . " where root>0 order by root desc" ) ) {
			echo S_SQLFAIL;
		}
	}
	
	//Finding the last entry number
	if ( !$result = mysql_call( "select max(no) from " . SQLLOG ) ) {
		echo S_SQLFAIL;
	}
	$row    = mysql_fetch_array( $result );
	$lastno = (int) $row[0];
	mysql_free_result( $result );
	
	$counttree = mysql_num_rows( $treeline );
	if ( !$counttree ) {
		$logfilename = PHP_SELF2;
		$dat         = '';
		head( $dat );
		form( $dat, $resno );
		$fp = fopen( $logfilename, "w" );
		set_file_buffer( $fp, 0 );
		rewind( $fp );
		fputs( $fp, $dat );
		fclose( $fp );
		chmod( $logfilename, 0666 );
	}
	for ( $page = 0; $page < $counttree; $page += PAGE_DEF ) {
		$dat = '';
		head( $dat );
		form( $dat, $resno );
		if ( !$resno ) {
			$st = $page;
		}
		$dat .= '<form action="' . PHP_SELF . '" method="post">';
		
		$lskd_fix_loop = 0;
		for ( $i = $st; $i < $st + PAGE_DEF; $i++ ) {
			//    if($lskd_fix_loop==0){echo "-->";} else {echo "";};
			if ( $lskd_fix_loop == 0 ) {
				echo "";
			}
			;
			list( $no, $now, $name, $email, $sub, $com, $host, $pwd, $ext, $w, $h, $tim, $time, $md5, $fsize, $fname,  ) = mysql_fetch_row( $treeline );
			if ( !$no ) {
				break;
			}
			
	// URL and link
			// If not in a thread 
			$threadurl = "" . PHP_SELF . "?res=$no";
			if ( $email )
				$name = "<a href=\"mailto:$email\">$name</a>";
			
			$com = preg_replace( "/(^|>)(&gt;[^<]*|＞[^<]*)/", "$1<span class=\"unkfunc\">$2</span>", $com );
			$com = preg_replace( "/(^|>)(&gt;&gt;[^0-9])/", "$1 $2", $com );
			$com = preg_replace( "/(^|>)(&gt;&gt;)([(0-9)+]*)([^<]*)/", "$1</font><a href=\"$threadurl#$3\" onclick=\"replyhl('$3');\">$2$3</a>$4<font>", $com );

			$dat .= "<span class=\"thread\"><span class=\"op_post\">";
			
			//OP Post image
			
			// Picture file name
			$img        = $path . $tim . $ext;
			$displaysrc = IMG_DIR . $tim . $ext;
			if ( strlen( $fname ) > 40 ) {
				$truncname = substr( $fname, 0, 40 ) . "...." . $ext;
			} else {
				$truncname = $fname;
			}
			// img tag creation
			$imgsrc = "";
			if ( $ext ) {
				//Filesize calculated
				$altmd5 = base64_encode( pack( "H*", $md5 ) );
				if ( $fsize >= 1048576 ) {
					$size = round( ( $fsize / 1048576 ), 2 ) . " M";
				} else if ( $fsize >= 1024 ) {
					$size = round( $fsize / 1024 ) . " K";
				} else {
					$size = $fsize . " ";
				}
				if ( $w && $h ) { //when there is size...
					if ( @is_file( THUMB_DIR . $tim . 's.jpg' ) ) {
						$imgsrc = "    <span class=\"thumbnailmsg\">" . S_THUMB . "</span><br /><a href=\"" . $displaysrc . "\" target=_blank><img src=\"" . THUMB_DIR . $tim . 's.jpg' . "\" border=\"0\" align=\"left\" class=\"postimg\" width=\"$w\" height=\"$h\" hspace=\"20\" alt=\"" . $size . " B\" /></a><br />";
					} else {
						$imgsrc = "<a href=\"" . $tim . "\" target=_blank><img src=\"" . $displaysrc . "\" border=\"0\" align=\"left\" class=\"postimg\" width=\"$w\" height=\"$h\" md5=\"$altmd5\" hspace=\"20\" alt=\"" . $size . " B\" /></a><br />";
					}
				} else {
					$imgsrc = "<a href=\"" . $tim . "\"><img src=\"" . $displaysrc . "\" border=\"0\" align=\"left\" class=\"postimg\" hspace=\"20\" alt=\"" . $size . " B\" /></a><br />";
				}
				$dimensions = "{$w}x{$h}";
				$dat .= "<span class=\"filesize\">" . S_PICNAME . "<a href=\"$displaysrc\" target=\"_blank\">" . $truncname . "</a> (" . $size . "B, " . $dimensions . ")</span>" . $imgsrc;
			}
			
			// word filters
			$com_parts = explode( " ", $com );
			foreach ( $com_parts as $key => $part ) {
				if ( strlen( $part ) > 90 ) {
					$part = substr( $part, 0, 90 ) . '...';
				}
				$chopped[$key] = $part;
			}
			$com       = implode( " ", $chopped );
			$com_parts = ( "" );
			$chopped   = ( "" );
			$key       = ( "" );
			$part      = ( "" );
			
			include( PLUG_PATH . '/wordfilter.php' );
			if ( USE_BBCODE ) {
				include( PLUG_PATH . '/bbcode.php' );
			}
			
			
			//  Main creation
			//op post
			
			//>> function.
			$threadurl2  = "" . PHP_SELF . "?res=$no";
			$threadurl33 = "$no";
			if ( $resno ) {
				$onclick = " onclick=\"replyhl('$no');\" class=qu";
				//    $quote="javascript:setData('&gt;&gt;$no')";
				$quote   = "javascript:insert('&gt;&gt;$no')";
			} else {
				$quote = "\"$threadurl2#q$no\"";
			}
			
			$dat .= "<input type=\"checkbox\" name=\"$no\" value=\"delete\" /><span class=\"filetitle\">$sub</span>   \n";
			$dat .= "<span class=\"postername\"><b>$name</b></span> $now " . "<a id=\"$no\" href=\"$threadurl#$no\" class=\"qu\" title=\"" . S_PERMALINK . "\" $onclick>No.</a>" . "<a href=$quote title=\"" . S_QUOTE . "\" class=\"qu\">$no</a> &nbsp; \n";
			if ( !$resno ) {
				$query = mysql_query( "SELECT sticky, locked FROM " . SQLLOG . " WHERE no=" . $no );
				while ( $row = mysql_fetch_array( $query ) ) {
					global $issticky, $issaged, $islocked;
					$issticky = $row["sticky"];
					$islocked = $row["locked"];
				}
				if ( $issticky ) {
					$dat .= "<img src='css/sticky.gif' /> ";
				}
				if ( $islocked ) {
					$dat .= "<img src='css/locked.gif' /> ";
				}
				mysql_free_result( $query );
				$dat .= "[<a href=\"" . PHP_SELF . "?res=$no\">" . S_REPLY . "</a>]";
			}
			$dat .= "\n<blockquote>$com</blockquote></span>";
			
			// Deletion pending
			if ( $lastno - LOG_MAX * 0.95 > $no ) {
				$dat .= "<span class=\"oldpost\">" . S_OLD . "</span><br />\n";
			}
			
			if ( !$resline = mysql_call( "select * from " . SQLLOG . " where resto=" . $no . " order by no" ) ) {
				echo S_SQLFAIL;
			}
			$countres = mysql_num_rows( $resline );
			
			if ( !$resno ) {
				$s = $countres - S_OMITT_NUM;
				if ( $s < 0 ) {
					$s = 0;
				} elseif ( $s > 0 ) {
					$dat .= "<span class=\"omittedposts\">" . S_RESU . $s . S_ABBR . "</span><br />\n";
				}
			} else {
				$s = 0;
			}
			
			while ( $resrow = mysql_fetch_row( $resline ) ) {
				if ( $s > 0 ) {
					$s--;
					continue;
				}
				list( $no, $now, $name, $email, $sub, $com, $host, $pwd, $ext, $w, $h, $tim, $time, $md5, $fsize, $fname, $sticky, $permasage, $locked ) = $resrow;
				if ( !$no ) {
					break;
				}
				
				// URL and e-mail
				if ( $email )
					$name = "<a href=\"mailto:$email\">$name</a>";
				
				$com = preg_replace( "/(^|>)(&gt;[^<]*|＞[^<]*)/", "$1<span class=\"unkfunc\">$2</span>", $com );
				$com = preg_replace( "/(^|>)(&gt;&gt;[^0-9])/", "$1 $2", $com );
				$com = preg_replace( "/(^|>)(&gt;&gt;)([(0-9)+]*)([^<]*)/", "$1</font><a href=\"$threadurl#$3\" onclick=\"replyhl('$3');\">$2$3</a>$4<font>", $com );
				
				// Main creation
				//replies (not op) 
				
				//>> function. 
				if ( $resno ) {
					$onclick = "onclick=\"replyhl('$no');\" class=qu";
					//    $quote="javascript:setData('&gt;&gt;$no')";
					$quote   = "javascript:insert('&gt;&gt;$no')";
				} else {
					$onclick = false;
					$quote   = "$threadurl#q$no\"";
				}
				
				$dat .= "<table><tr><td class=\"reply\">\n";
				$dat .= "<input type=\"checkbox\" name=\"$no\" value=\"delete\" /><span class=\"replytitle\">$sub</span> \n";
				$dat .= "<span class=\"postername\"><b>$name</b></span> $now " . "<a id=\"$no\" href=\"$threadurl#$no\" class=\"qu\" title=\"Permalink thread\" $onclick>No.</a>" . "<a href=\"$quote\" title=\"Quote\" class=\"qu\">$no</a> &nbsp; \n";
				
			// Picture file name
			$img        = $path . $tim . $ext;
			$displaysrc = IMG_DIR . $tim . $ext;
			if ( strlen( $fname ) > 40 ) {
				$truncname = substr( $fname, 0, 40 ) . "...." . $ext;
			} else {
				$truncname = $fname;
			}
			// img tag creation
			$imgsrc = "";
			if ( $ext ) {
				//Filesize calculated
				$shortmd5 = base64_encode( pack( "H*", $md5 ) );
				if ( $fsize >= 1048576 ) {
					$size = round( ( $fsize / 1048576 ), 2 ) . " M";
				} else if ( $fsize >= 1024 ) {
					$size = round( $fsize / 1024 ) . " K";
				} else {
					$size = $fsize . " ";
				}
					if ( $w && $h ) { //when there is size...
						if ( @is_file( THUMB_DIR . $img . 's.jpg' ) ) {
							$imgsrc = "    <br /><span class=\"thumbnailmsg\">" . S_THUMB . "</span><br /><a href=\"" . $displaysrc . "\" target=_blank><img src=\"" . THUMB_DIR . $tim . 's.jpg' . "\" border=\"0\" align=\"left\" class=\"postimg\" width=\"$w\" height=\"$h\" hspace=\"20\" alt=\"" . $size . " B\" /></a><br />";
						} else {
							$imgsrc = "<a href=\"" . $displaysrc . "\"><br><img src=\"" . THUMB_DIR . $tim . 's.jpg' . "\" border=\"0\" align=\"left\" class=\"postimg\" width=\"$w\" height=\"$h\" hspace=\"20\" alt=\"" . $size . " B\" /></a><br />";
						}
					} else {
						$imgsrc = "<a href=\"" . $displaysrc . "\"><img src=\"" . THUMB_DIR . $tim . 's.jpg' . "\" border=\"0\" align=\"left\" class=\"postimg\" hspace=\"20\" alt=\"" . $size . " B\" /></a><br />";
					}
					$dat .= "<br / ><span class=\"filesize\">" . S_PICNAME . "<a href=\"$displaysrc\">" . $truncname . "</a> (" . $size . "B, " . $dimensions . ")" . $imgsrc. "</span>";
				}
				
				// word filters
				$com_parts = explode( " ", $com );
				foreach ( $com_parts as $key => $part ) {
					if ( strlen( $part ) > 80 ) {
						$part = substr( $part, 0, 80 ) . '...';
					}
					$chopped[$key] = $part;
				}
				$com       = implode( " ", $chopped );
				$com_parts = ( "" );
				$chopped   = ( "" );
				$key       = ( "" );
				$part      = ( "" );
				
				include( PLUG_PATH . '/wordfilter.php' );
				if ( USE_BBCODE ) {
					include( PLUG_PATH . '/bbcode.php' );
				}
				
				$dat .= "<blockquote>$com</blockquote>";
				$dat .= "</td></tr></table>\n";
			}
			
			/*possibility for ads after each post*/
			$dat .= "</span><br clear=\"left\" /><hr />\n";
			
			if ( USE_ADS3 ) {
				$dat .= '' . ADS3 . '<hr />';
			}
			if ( $resno ) {
				$dat .= "[<a href=\"" . PHP_SELF2 . "\">" . S_RETURN . "</a>]\n";
			}
			clearstatcache(); //clear stat cache of a file
			mysql_free_result( $resline );
			$p++;
			if ( $resno ) {
				break;
			} //only one tree line at time of res
		}
		
		
		
		
		$dat .= '<table align="right"><tr><td nowrap="nowrap" align="center">
<input type="hidden" name="mode" value="usrdel" />' . S_REPDEL . '[<input type="checkbox" name="onlyimgdel" value="on" />' . S_DELPICONLY . ']<br />
' . S_DELKEY . '<input type="password" name="pwd" size="8" maxlength="8" value="" />
<input type="submit" value="' . S_DELETE . '" /></td></tr></table></form>
<script language="JavaScript" type="script"><!--
l();
//--></script>';
		
		if ( !$resno ) { // if not in res display mode
			$prev = $st - PAGE_DEF;
			$next = $st + PAGE_DEF;
			//  Page processing
			$dat .= "<table align=left border=1 class=pages><tr>";
			if ( $prev >= 0 ) {
				if ( $prev == 0 ) {
					$dat .= "<form action=\"" . PHP_SELF2 . "\" method=\"get\" /><td>";
				} else {
					$dat .= "<form action=\"" . $prev / PAGE_DEF . PHP_EXT . "\" method=\"get\"><td>";
				}
				$dat .= "<input type=\"submit\" value=\"" . S_PREV . "\" />";
				$dat .= "</td></form>";
			} else {
				$dat .= "<td>" . S_FIRSTPG . "</td>";
			}
			
			$dat .= "<td>";
			for ( $i = 0; $i < $counttree; $i += PAGE_DEF ) {
				if ( $i && !( $i % ( PAGE_DEF * 2 ) ) ) {
					$dat .= " ";
				}
				if ( $st == $i ) {
					$dat .= "[" . ( $i / PAGE_DEF ) . "] ";
				} else {
					if ( $i == 0 ) {
						$dat .= "[<a href=\"" . PHP_SELF2 . "\">0</a>] ";
					} else {
						$dat .= "[<a href=\"" . ( $i / PAGE_DEF ) . PHP_EXT . "\">" . ( $i / PAGE_DEF ) . "</a>] ";
					}
				}
			}
			$dat .= "</td>";
			
			if ( $p >= PAGE_DEF && $counttree > $next ) {
				$dat .= "<td><form action=\"" . $next / PAGE_DEF . PHP_EXT . "\" method=\"get\">";
				$dat .= "<input type=\"submit\" value=\"" . S_NEXT . "\" />";
				$dat .= "</form></td>";
			} else {
				$dat .= "<td>" . S_LASTPG . "</td>";
			}
			$dat .= "</tr></table><br clear=\"all\" />\n";
		} else {
			$dat .= "<br />";
		}
		
		
		foot( $dat );
		if ( $resno ) {
			echo $dat;
			break;
		}
		if ( $page == 0 ) {
			$logfilename = PHP_SELF2;
		} else {
			$logfilename = $page / PAGE_DEF . PHP_EXT;
		}
		$fp = fopen( $logfilename, "w" );
		set_file_buffer( $fp, 0 );
		rewind( $fp );
		fputs( $fp, $dat );
		fclose( $fp );
		chmod( $logfilename, 0666 );
	}
	mysql_free_result( $treeline );
}


function mysql_call( $query )
{
	$ret = mysql_query( $query );
	if ( !$ret ) {
		if ( DEBUG_MODE ) {
			echo "Error on query: " . $query . "<br />";
			echo mysql_error() . "<br />";
		} else {
			echo "MySQL error!<br />";
		}
	}
	return $ret;
}

/* head */
function head( &$dat )
{
	$titlepart = '';
	if ( SHOWTITLEIMG == 1 ) {
		$titlepart .= '<img src="' . TITLEIMG . '" alt="' . TITLE . '" />';
		if ( SHOWTITLETXT == 1 ) {
			$titlepart .= '<br />';
		}
	} else if ( SHOWTITLEIMG == 2 ) {
		$titlepart .= '<img src="' . TITLEIMG . '" onclick="this.src=this.src;" alt="' . TITLE . '" />';
		if ( SHOWTITLETXT == 1 ) {
			$titlepart .= '<br />';
		}
	}
	if ( SHOWTITLETXT == 1 ) {
		$titlepart .= '' . TITLE . '';
	}
	/* print page */
	$dat .= '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="jp"><head>
<meta name="description" content="' . S_DESCR . '"/>
<meta http-equiv="content-type"  content="text/html;charset=utf-8" />
<!-- meta HTTP-EQUIV="pragma" CONTENT="no-cache" -->
<link REL="SHORTCUT ICON" HREF="/favicon.ico">
<link rel="stylesheet" type="text/css" href="' . CSSFILE . '" title="Standard Saguaro" />
<link rel="alternate stylesheet" type="text/css" media="screen" title="' . STYLESHEET_2 . '" href="' . CSSFILE2 . '" />
<link rel="alternate stylesheet" type="text/css" media="screen" title="' . STYLESHEET_3 . '" href="' . CSSFILE3 . '" />
<link rel="alternate stylesheet" type="text/css" media="screen" title="' . STYLESHEET_4 . '" href="' . CSSFILE4 . '" />
<script src="' . PLUG_PATH . '/' . JS_PATH . '/styleswitch.js" type="text/javascript">
/***********************************************
* Style Sheet Switcher v1.1- c Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for this script and 100s more
***********************************************/
/**spoot note: Seriously? Really in the include too?**/
</script>
<title>' . TITLE . '</title>
<script language="JavaScript"><!--
function l(e){var P=getCookie("pwdc"),N=getCookie("namec"),i;with(document){for(i=0;i<forms.length;i++){if(forms[i].pwd)with(forms[i]){pwd.value=P;}if(forms[i].name)with(forms[i]){name.value=N;}}}};onload=l;function getCookie(key, tmp1, tmp2, xx1, xx2, xx3) {tmp1 = " " + document.cookie + ";";xx1 = xx2 = 0;len = tmp1.length;	while (xx1 < len) {xx2 = tmp1.indexOf(";", xx1);tmp2 = tmp1.substring(xx1 + 1, xx2);xx3 = tmp2.indexOf("=");if (tmp2.substring(0, xx3) == key) {return(unescape(tmp2.substring(xx3 + 1, xx2 - xx1 - 1)));}xx1 = xx2 + 1;}return("");}
//--></script>
<script>
function insert(text)
{
	var textarea=document.forms.contrib.com;
	if(textarea)
	{
		if(textarea.createTextRange && textarea.caretPos) // IE
		{
			var caretPos=textarea.caretPos;
			caretPos.text=caretPos.text.charAt(caretPos.text.length-1)==" "?text+" ":text;
		}
		else if(textarea.setSelectionRange) // Firefox
		{
			var start=textarea.selectionStart;
			var end=textarea.selectionEnd;
			textarea.value=textarea.value.substr(0,start)+text+textarea.value.substr(end);
			textarea.setSelectionRange(start+text.length,start+text.length);
		}
		else
		{
			textarea.value+=text+" ";
		}
		textarea.focus();
	}
}
</script>';
	
	if ( USE_IMG_HOVER || USE_IMG_TOOLBAR || USE_IMG_EXP || USE_UTIL_QUOTE || USE_INF_SCROLL || USE_FORCE_WRAP || USE_UPDATER || USE_THREAD_STATS || USE_JS_SETTINGS || USE_EXTRAS ) {
		$dat .= '<script src="' . JS_PATH . '/jquery.min.js" type="text/javascript"></script>';
	}
	
	if ( USE_JS_SETTINGS ) {
		$dat .= '<script src="' . JS_PATH . '/suite_settings.js" type="text/javascript"></script>';
	}
	if ( USE_IMG_HOVER ) {
		$dat .= '<script src="' . JS_PATH . '/image_hover.js" type="text/javascript"></script>';
	}
	if ( USE_IMG_TOOLBAR ) {
		$dat .= '<script src="' . JS_PATH . '/image_toolbar.js" type="text/javascript"></script>';
	}
	if ( USE_IMG_EXP ) {
		$dat .= '<script src="' . JS_PATH . '/image_expansion.js" type="text/javascript"></script>';
	}
	if ( USE_UTIL_QUOTE ) {
		$dat .= '<script src="' . JS_PATH . '/utility_quotes.js" type="text/javascript"></script>';
	}
	if ( USE_INF_SCROLL ) {
		$dat .= '<script src="' . JS_PATH . '/infinite_scroll.js" type="text/javascript"></script>';
	}
	if ( USE_FORCE_WRAP ) {
		$dat .= '<script src="' . JS_PATH . '/force_post_wrap.js" type="text/javascript"></script>';
	}
	if ( USE_UPDATER ) {
		$dat .= '<script src="' . JS_PATH . '/thread_updater.js" type="text/javascript"></script>';
	}
	if ( USE_THREAD_STATS ) {
		$dat .= '<script src="' . JS_PATH . '/thread_stats.js" type="text/javascript"></script>';
	}
	if ( USE_EXTRAS ) {
		foreach ( glob( JS_PATH . "/extra/*.js" ) as $path ) {
			$dat .= '<script src="' . $path . '" type="text/javascript"></script>';
		}
		unset( $path );
	}
	
	
	//if(USE_IMG_HOVER || USE_IMG_TOOLBAR || USE_IMG_EXP || USE_UTIL_QUOTE || USE_INF_SCROLL || USE_FORCE_WRAP || USE_UPDATER || USE_THREAD_STATS){$dat.='<script src="'.JS_PATH.'/suite_settings.js" type="text/javascript"></script>';}
	
	$dat .= '
' . EXTRA_SHIT . '
</head>
<body>
 ' . $titlebar . '
<span class="boardlist">' . get_file_contents(NAV_TXT) . ' </span>
<span class="adminbar">
[<a href="' . HOME . '" target="_top">' . S_HOME . '</a>]
[<a href="' . PHP_SELF . '?mode=admin">' . S_ADMIN . '</a>]
<form id="switchform">
		<select name="switchcontrol" size="1" onChange="chooseStyle(this.options[this.selectedIndex].value, 60)">
			<option value="none" selected="selected">' . STYLESHEET_1 . '</option>
			<option value="' . STYLESHEET_2 . '">' . STYLESHEET_2 . '</option>
			<option value="' . STYLESHEET_3 . '">' . STYLESHEET_3 . '</option>
			<option value="' . STYLESHEET_4 . '">' . STYLESHEET_4 . '</option>
		</select>
	</form>
</span>
<div class="logo">' . $titlepart . '</div>
<div class="headsub">' . S_HEADSUB . '</div><hr />';
	if ( USE_ADS1 ) {
		$dat .= '' . ADS1 . '<hr />';
	}
}
/* Contribution form */
function form( &$dat, $resno, $admin = "" )
{
	$maxbyte = MAX_KB * 1024;
	$no      = $resno;
	if ( $resno ) {
		$msg .= "[<a href=\"" . PHP_SELF2 . "\">" . S_RETURN . "</a>]\n";
		$msg .= "<div class=\"theader\">" . S_POSTING . "</div>\n";
	}
	if ( $admin ) {
		$hidden = "<input type=hidden name=admin value=\"" . PANEL_PASS . "\">";
		$msg    = "<em>" . S_NOTAGS . "</em>";
	}
	$dat .= $msg . '<div align="center"><div class="postarea">
<form action="' . PHP_SELF . '" method="post" name="contrib" enctype="multipart/form-data">
<input type="hidden" name="mode" value="regist" />
' . $hidden . '
<input type="hidden" name="MAX_FILE_SIZE" value="' . $maxbyte . '" />
';
	if ( $no ) {
		$dat .= '<input type="hidden" name="resto" value="' . $no . '" />
';
	}
	if ( !$admin && BOTCHECK ) {
		$dat .= '<table>
<tr><td class="postblock" align="left">' . S_NAME . '</td><td align="left"><input type="text" name="name" size="28" /></td></tr>
<tr><td class="postblock" align="left">' . S_EMAIL . '</td><td align="left"><input type="text" name="email" size="28" /></td></tr>
<tr><td class="postblock" align="left">' . S_SUBJECT . '</td><td align="left"><input type="text" name="sub" size="35" />
<input type="submit" value="' . S_SUBMIT . '" /></td></tr>
<tr><td class="postblock" align="left">' . S_COMMENT . '</td><td align="left"><textarea name="com" cols="48" rows="4"></textarea></td></tr>
<tr><td class="postblock" align="left"><img src="' . PLUG_PATH . '/php_captcha.php" /></td><td align="left"><input type="text" name="num" size="28" /></td></tr>
';
	} elseif ( !$admin && BOTCHECK == 0 ) {
		$dat .= '<table>
<tr><td class="postblock" align="left">' . S_NAME . '</td><td align="left"><input type="text" name="name" size="28" /></td></tr>
<tr><td class="postblock" align="left">' . S_EMAIL . '</td><td align="left"><input type="text" name="email" size="28" /></td></tr>
<tr><td class="postblock" align="left">' . S_SUBJECT . '</td><td align="left"><input type="text" name="sub" size="35" />
<input type="submit" value="' . S_SUBMIT . '" /></td></tr>
<tr><td class="postblock" align="left">' . S_COMMENT . '</td><td align="left"><textarea name="com" cols="48" rows="4"></textarea></td></tr>
';
	} else {
		$dat .= '<table>
<tr><td class="postblock" align="left">' . S_NAME . '</td><td align="left"><input type="text" name="name" size="28" /></td></tr>
<tr><td class="postblock" align="left">' . S_RESNUM . '</td><td align="left"><input type="text" name="resto" size="28" /></td></tr>
<tr><td class="postblock" align="left">' . S_EMAIL . '</td><td align="left"><input type="text" name="email" size="28" /></td></tr>
<tr><td class="postblock" align="left">' . S_SUBJECT . '</td><td align="left"><input type="text" name="sub" size="35" />
<input type="submit" value="' . S_SUBMIT . '" /></td></tr>
<tr><td class="postblock" align="left">' . S_COMMENT . '</td><td align="left"><textarea name="com" cols="48" rows="4"></textarea></td></tr>
';
	}
	
	/*if(!$resno){*/
	
	if ( NOPICBOX && !$resno ) {
		$dat .= '<tr><td class="postblock" align="left">' . S_UPLOADFILE . '</td>
<td><input type="file" name="upfile" size="35" />
[<label><input type="checkbox" name="textonly" value="on" />' . S_NOFILE . '</label>]</td></tr>
';
	} else {
		$dat .= '<tr><td class="postblock" align="left">' . S_UPLOADFILE . '</td>
<td><input type="file" name="upfile" size="35" />
<input type="checkbox" name="textonly" value="on" style="display:none;" /></td></tr>
';
	}
	/*}*/
	if ( $admin ) {
		$dat .= '<tr><td align="left" class="postblock" align="left">Options</td><td align="left">Sticky: <input type="checkbox" name="isSticky" value="isSticky" />Lock:<input type="checkbox" name="isLocked" value="isLocked" />';
	}
	
	$dat .= '<tr><td align="left" class="postblock" align="left">' . S_DELPASS . '</td><td align="left"><input type="password" name="pwd" size="8" maxlength="8" value="" />' . S_DELEXPL . '</td></tr>
<tr><td colspan="2">
<div align="left" class="rules">' . S_RULES . '</div></td></tr></table></form></div></div><hr />
';
	if ( USE_ADS2 ) {
		$dat .= '' . ADS2 . '<hr />';
	}
	
}

/* Footer */
function foot( &$dat )
{
	$dat .= '
<span class="boardlist">' . get_file_contents(NAV_TXT) . '</span>
<div class="footer">' . S_FOOT . '</div>
 
</body></html>';
}


function error( $mes, $dest = '' )
{
    global $upfile_name, $path;
    if (is_file($dest))
        unlink($dest);
    head($dat);
    echo $dat;
    if ($mes == S_BADHOST) { 
    die("<html><head><meta http-equiv=\"refresh\" content=\"0; url=banned.php\"></head></html>");
    } else {
	    echo "<br /><br /><hr size=1><br /><br />
		   <center><font color=blue size=5>$mes<br /><br /><a href=" . PHP_SELF2 . ">" . S_RELOAD . "</a></b></font></center>
		   <br /><br /><hr size=1>";
	    die("</body></html>");
    }
}
/* Auto Linker */
function auto_link( $proto )
{
	$proto = ereg_replace( "(https?|ftp|news)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)", "<a href=\"\\1\\2\">\\1\\2</a>", $proto );
	return $proto;
}

function proxy_connect( $port )
{
	$fp = @fsockopen( $_SERVER["REMOTE_ADDR"], $port, $a, $b, 2 );
	if ( !$fp ) {
		return 0;
	} else {
		return 1;
	}
}
/* Regist */
function regist( $name, $email, $sub, $com, $url, $pwd, $upfile, $upfile_name, $resto, $num )
{
	global $path, $badstring, $badfile, $badip, $pwdc, $textonly, $auth;
	
	if ( $auth == 1 || !BOTCHECK ) {
		
		// time
		$time = time();
		$tim  = $time . substr( microtime(), 2, 3 );
		
		//Manager options
		if ( isset( $_POST['isSticky'] ) && $pwd == PANEL_PASS ) {
			$stickied = 1;
		} else {
			$stickied = 0;
		}
		
		if ( isset( $_POST['isLocked'] ) && $pwd == PANEL_PASS ) {
			$locked = 1;
		} else {
			$locked = 0;
		}
		
		// upload processing
		
		$image_exists = $upfile && file_exists( $upfile );
		if ( $image_exists ) {
			$dest = tempnam( substr( $path, 0, -1 ), "img" );
			move_uploaded_file( $upfile, $dest );

			$upfile_name = CleanStr( $upfile_name );
			if ( !is_file( $dest ) )
				error( S_UPFAIL, $dest );
			$size = getimagesize( $dest );
			if ( !is_array( $size ) )
				error( S_NOREC, $dest );
			$md5 = md5_of_file( $dest );
			foreach ( $badfile as $value ) {
				if ( ereg( "^$value", $md5 ) ) {
					error( S_SAMEPIC, $dest ); //Refuse this image
				}
			}
			chmod( $dest, 0666 );
			$W     = $size[0];
			$H     = $size[1];
			$fsize = filesize( $dest );
			if ( $fsize > MAX_KB * 1024 )
				error( S_TOOBIG, $dest );
			switch ( $size[2] ) {
				//file types
				case 1:
					$ext = ".gif";
					break;
				case 2:
					$ext = ".jpg";
					break;
				case 3:
					$ext = ".png";
					break;
				//case 4 : $ext=".swf";break;
				//case 5 : $ext=".psd";break;
				/*case 6:
				$ext = ".bmp";
				break;*/
				//case 7 : $ext=".tiff";break;
				//case 8 : $ext=".tiff";break;
				//case 9 : $ext=".jpc";break;
				//case 10 : $ext=".jp2";break;
				//case 11 : $ext=".jpx";break;
				//case 12 : $ext=".jb2";break;
				//case 13 : $ext=".swc";break;
				//case 14 : $ext=".iff";break;
				//case 15 : $ext=".wbmp";break;
				//case 16 : $ext=".xbm";break;
				default:
					$ext = ".xxx";
					error( S_BADFILEISBAD, $dest );
			}
			
			if ( $W < MIN_W || $H < MIN_H ) {
				error( S_TOODAMNSMALL, $dest );
			}
			
			// Picture reduction
			if ( !$resto ) {
				$maxw = MAX_W;
				$maxh = MAX_H;
			} else {
				$maxw = MAXR_W;
				$maxh = MAXR_H;
			}
			
			if ( $W > $maxw || $H > $maxh ) {
				$W2 = $maxw / $W;
				$H2 = $maxh / $H;
				( $W2 < $H2 ) ? $key = $W2 : $key = $H2;
				$W = ceil( $W * $key );
				$H = ceil( $H * $key );
			}
			
			$mes = $upfile_name . ' ' . S_UPGOOD;
		}
		
		if ( $_FILES["upfile"]["error"] == 2 ) {
			error( S_TOOBIG, $dest );
		}
		if ( $upfile_name && $_FILES["upfile"]["size"] == 0 ) {
			error( S_TOOBIGORNONE, $dest );
		}
		
		//The last result number
		if ( !$result = mysql_call( "select max(no) from " . SQLLOG ) ) {
			echo S_SQLFAIL;
		}
		$row    = mysql_fetch_array( $result );
		$lastno = (int) $row[0];
		mysql_free_result( $result );
		
		// Number of log lines
		if ( !$result = mysql_call( "select no,ext,tim from " . SQLLOG . " where no<=" . ( $lastno - LOG_MAX ) ) ) {
			echo S_SQLFAIL;
		} else {
			while ( $resrow = mysql_fetch_row( $result ) ) {
				list( $dno, $dext, $dtim ) = $resrow;
				if ( !mysql_query( "delete from " . SQLLOG . " where no=" . $dno ) ) {
					echo S_SQLFAIL;
				}
				if ( $dext ) {
					if ( is_file( $path . $dtim . $dext ) )
						unlink( $path . $dtim . $dext );
					if ( is_file( THUMB_DIR . $dtim . 's.jpg' ) )
						unlink( THUMB_DIR . $dtim . 's.jpg' );
				}
			}
			mysql_free_result( $result );
		}
		
		$find  = false;
		$resto = (int) $resto;
		if ( $resto ) {
			if ( !$result = mysql_call( "select * from " . SQLLOG . " where root>0 and no=$resto" ) ) {
				echo S_SQLFAIL;
			} else {
				$find = mysql_fetch_row( $result );
				mysql_free_result( $result );
			}
			if ( !$find )
				error( S_NOTHREADERR, $dest );
		}
		
		foreach ( $badstring as $value ) {
			if ( ereg( $value, $com ) || ereg( $value, $sub ) || ereg( $value, $name ) || ereg( $value, $email ) ) {
				error( S_STRREF, $dest );
			}
			;
		}
		if ( $_SERVER["REQUEST_METHOD"] != "POST" )
			error( S_UNJUST, $dest );
		// Form content check
		if ( !$name || ereg( "^[ |　|]*$", $name ) )
			$name = "";
		if ( !$com || ereg( "^[ |　|\t]*$", $com ) )
			$com = "";
		if ( !$sub || ereg( "^[ |　|]*$", $sub ) )
			$sub = "";
		
		if ( !$resto && !$textonly && !is_file( $dest ) )
			error( S_NOPIC, $dest );
		if ( !$com && !is_file( $dest ) )
			error( S_NOTEXT, $dest );
		
		$name = ereg_replace( S_MANAGEMENT, "\"" . S_MANAGEMENT . "\"", $name );
		$name = ereg_replace( S_DELETION, "\"" . S_DELETION . "\"", $name );
		
		if ( strlen( $com ) > S_POSTLENGTH )
			error( S_TOOLONG, $dest );
		if ( strlen( $name ) > 100 )
			error( S_TOOLONG, $dest );
		if ( strlen( $email ) > 100 )
			error( S_TOOLONG, $dest );
		if ( strlen( $sub ) > 100 )
			error( S_TOOLONG, $dest );
		if ( strlen( $resto ) > 10 )
			error( S_UNUSUAL, $dest );
		if ( strlen( $url ) > 10 )
			error( S_UNUSUAL, $dest );
		
		//host check
		$host = $_SERVER["REMOTE_ADDR"];	
		$badip = mysql_call("SELECT ip FROM " . SQLBANLOG . " WHERE ip = '$host' and banlength <> 0 ");
		
		$query  = mysql_query( "SELECT * FROM " . SQLLOG . " WHERE no=" . $resto );
		$result = mysql_fetch_assoc( $query );
		if ( $result["locked"] == '1' ) {
			error( S_THREADLOCKED, $dest );
		}
		
		//Check if user IP is in bans table
		if ( mysql_num_rows( $badip ) == 0 ) {
			// Not Banned
		} else {
			//NOW YOU FUCKED UP
			error( S_BADHOST, $dest );
		}
		
		if ( eregi( "^mail", $host ) || eregi( "^ns", $host ) || eregi( "^dns", $host ) || eregi( "^ftp", $host ) || eregi( "^prox", $host ) || eregi( "^pc", $host ) || eregi( "^[^\.]\.[^\.]$", $host ) ) {
			$pxck = "on";
		}
		if ( eregi( "ne\\.jp$", $host ) || eregi( "ad\\.jp$", $host ) || eregi( "bbtec\\.net$", $host ) || eregi( "aol\\.com$", $host ) || eregi( "uu\\.net$", $host ) || eregi( "asahi-net\\.or\\.jp$", $host ) || eregi( "rim\\.or\\.jp$", $host ) ) {
			$pxck = "off";
		} else {
			$pxck = "on";
		}
		
		if ( $pxck == "on" && PROXY_CHECK ) {
			if ( proxy_connect( '80' ) == 1 ) {
				error( S_PROXY80, $dest );
			} elseif ( proxy_connect( '8080' ) == 1 ) {
				error( S_PROXY8080, $dest );
			}
		}
		
		// No, path, time, and url format
		srand( (double) microtime() * 1000000 );
		if ( $pwd == "" ) {
			if ( $pwdc == "" ) {
				$pwd = rand();
				$pwd = substr( $pwd, 0, 8 );
			} else {
				$pwd = $pwdc;
			}
		}
		
		$c_pass = $pwd;
		$pass   = ( $pwd ) ? substr( md5( $pwd ), 2, 8 ) : "*";
		$youbi  = array(
			 S_SUN,
			S_MON,
			S_TUE,
			S_WED,
			S_THU,
			S_FRI,
			S_SAT 
		);
		$yd     = $youbi[gmdate( "w", $time + 9 * 60 * 60 )];
		$now    = gmdate( ' ' . DATE_FORMAT . ' ', $time + 9 * 60 * 60 ) . "(" . (string) $yd . ")" . gmdate( "H:i", $time + 9 * 60 * 60 );
		if ( DISP_ID ) {
			if ( $email && DISP_ID == 1 ) {
				$now .= " ID:???";
			} else {
				$now .= " ID:" . substr( crypt( md5( $_SERVER["REMOTE_ADDR"] . 'id' . gmdate( ' ' . DATE_FORMAT . ' ', $time + 9 * 60 * 60 ) ), 'id' ), -8 );
			}
		}
		//Text plastic surgery (rorororor)
		$email = CleanStr( $email );
		$email = ereg_replace( "[\r\n]", "", $email );
		$sub   = CleanStr( $sub );
		$sub   = ereg_replace( "[\r\n]", "", $sub );
		$url   = CleanStr( $url );
		$url   = ereg_replace( "[\r\n]", "", $url );
		$resto = CleanStr( $resto );
		$resto = ereg_replace( "[\r\n]", "", $resto );
		$com   = CleanStr( $com );
		// Standardize new character lines
		$com   = str_replace( "\r\n", "\n", $com );
		$com   = str_replace( "\r", "\n", $com );
		// Continuous lines
		$com   = ereg_replace( "\n((!@| )*\n){3,}", "\n", $com );
		if ( !BR_CHECK || substr_count( $com, "\n" ) < BR_CHECK ) {
			$com = nl2br( $com ); //br is substituted before newline char
		}
		$com = str_replace( "\n", "", $com ); //\n is erased
		
	list( $name ) = explode( "#", $name );
	$name = CleanStr( $name );
	
	if ( preg_match( "/\#+$/", $names ) ) {
		$names = preg_replace( "/\#+$/", "", $names );
	}
	if ( preg_match( "/\#/", $names ) ) {
		$names = str_replace( "&#", "&&", htmlspecialchars( $names ) ); # otherwise HTML numeric entities screw up explode()!
		list( $nametemp, $trip, $sectrip ) = str_replace( "&&", "&#", explode( "#", $names, 3 ) );
		$names = $nametemp;
		$name .= "</span>";
		
		if ( $trip != "" ) {
			if ( $sectrip == "" ) {
					if ( $name == "</span>" && $sectrip == "" )
						$name = S_ANONAME;
					else
						$name = str_replace( "</span>", "", $name );
				
			} else {
				
				$salt = strtr( preg_replace( "/[^\.-z]/", ".", substr( $trip . "H.", 1, 2 ) ), ":;<=>?@[\\]^_`", "ABCDEFGabcdef" );
				$trip = substr( crypt( $trip, $salt ), -10 );
				$name .= " <span class=\"postertrip\">!" . $trip;
			}
		}
		
		
		if ( $sectrip != "" ) {
			$salt = RAWSALT;
			if ( file_exists( SALTFILE ) ) { //salt already generated
				$salt = file_get_contents( SALTFILE );
			} else {
				system( "openssl rand 448 > '" . SALTFILE . "'", $err );
				if ( $err === 0 ) {
					chmod( SALTFILE, 0400 );
					$salt = file_get_contents( SALTFILE );
				}
			}
			$sha = base64_encode( pack( "H*", sha1( $sectrip . $salt ) ) );
			$sha = substr( $sha, 0, 11 );
			if ( $trip == "" )
				$name .= " <span class=\"postertrip\">";
			$name .= "!!" . $sha;
		}
	}
		
		if ( $email == 'noko' ) {
			$noko  = 1;
			$email = '';
		} else if ( $email == 'nokosage' ) {
			$noko  = 1;
			$email = S_SAGE;
		}
		
		
		if ( !$name )
			$name = S_ANONAME;
		if ( !$com )
			$com = S_ANOTEXT;
		if ( !$sub )
			$sub = S_ANOTITLE;
		
		// Read the log
		$query = "select time from " . SQLLOG . " where com='" . mysql_escape_string( $com ) . "' " . "and host='" . mysql_escape_string( $host ) . "' " . "and no>" . ( $lastno - 20 ); //the same
		if ( !$result = mysql_call( $query ) ) {
			echo S_SQLFAIL;
		}
		$row = mysql_fetch_array( $result );
		mysql_free_result( $result );
		if ( $row && !$upfile_name )
			error( S_RENZOKU3, $dest );
		
		$query = "select time from " . SQLLOG . " where time>" . ( $time - RENZOKU ) . " " . "and host='" . mysql_escape_string( $host ) . "' "; //from precontribution
		if ( !$result = mysql_call( $query ) ) {
			echo S_SQLFAIL;
		}
		$row = mysql_fetch_array( $result );
		mysql_free_result( $result );
		if ( $row && !$upfile_name )
			error( S_RENZOKU3, $dest );
		
		// Upload processing
		if ( $dest && file_exists( $dest ) ) {
			
			$query = "select time from " . SQLLOG . " where time>" . ( $time - RENZOKU2 ) . " " . "and host='" . mysql_escape_string( $host ) . "' "; //from precontribution
			if ( !$result = mysql_call( $query ) ) {
				echo S_SQLFAIL;
			}
			$row = mysql_fetch_array( $result );
			mysql_free_result( $result );
			if ( $row && $upfile_name )
				error( S_RENZOKU2, $dest );
			
			if ( DUPE_CHECK ) {
				//Duplicate image check
				$result = mysql_call( "select tim,ext,md5 from " . SQLLOG . " where md5='" . $md5 . "'" );
				if ( $result ) {
					list( $timp, $extp, $md5p ) = mysql_fetch_row( $result );
					mysql_free_result( $result );
					#      if($timp&&file_exists($path.$timp.$extp)){ #}
					if ( $timp ) {
						error( S_DUPE, $dest );
					}
				}
			}
			
		}
		
		//Sage check
		$sage    = S_SAGE;
		$restoqu = (int) $resto;
		//Bump processing
		if ( $resto ) {
			$query = mysql_query( "SELECT sticky, permasage FROM " . SQLLOG . " WHERE no=" . $resto );
			while ( $row = mysql_fetch_array( $query ) ) {
				global $issticky, $issaged, $islocked;
				$issticky = $row["sticky"];
				$issaged  = $row["permasage"];
			}
			$rootqu = "0";
			if ( !$resline = mysql_query( "select * from " . SQLLOG . " where resto=" . $resto ) ) {
				echo S_SQLFAIL;
			}
			$countres = mysql_num_rows( $resline );
			mysql_free_result( $resline );
			if ( !stristr( $email, $sage ) && $countres < MAX_RES ) {
				if ( $issticky == 0 and $issaged != 1 ) {
					mysql_query( "update " . SQLLOG . " set root=now() where no=$resto" ); //bumps to top if not permasaged or stickied
				}
				/*if (!$result = mysql_query($query)) {
				#echo S_SQLFAIL . " " . mysql_error();
				echo $result;
				}*/
			}
		} else {
			if ( $stickied and !$issaged ) {
				$rootqu = '20270707190707';
			} else if ( $issaged ) {
				$rootqu = "";
			} else {
				$rootqu = "now()";
			}
		} //now it is root
		//Main insert
		$query = "insert into " . SQLLOG . " (now,name,email,sub,com,host,pwd,ext,w,h,tim,time,md5,fsize,fname, sticky, permasage, locked, root,resto) values (" . "'" . $now . "'," . "'" . mysql_escape_string( $name ) . "'," . "'" . mysql_escape_string( $email ) . "'," . "'" . mysql_escape_string( $sub ) . "'," . "'" . mysql_escape_string( $com ) . "'," . "'" . mysql_escape_string( $host ) . "'," . "'" . mysql_escape_string( $pass ) . "'," . "'" . $ext . "'," . (int) $W . "," . (int) $H . "," . "'" . $tim . "'," . (int) $time . "," . "'" . $md5 . "'," . (int) $fsize . "," . "'" . mysql_escape_string( $upfile_name ) . "'," . (int) $stickied . "," . (int) $permasage . "," . (int) $locked . "," . $rootqu . "," . (int) $resto . ")";
		if ( !$result = mysql_call( $query ) ) {
			echo S_SQLFAIL . mysql_error();
		} //post registration
		
		//Cookies
		setcookie( "pwdc", $c_pass, time() + 7 * 24 * 3600 );
		/* 1 week cookie expiration */
		if ( function_exists( "mb_internal_encoding" ) && function_exists( "mb_convert_encoding" ) && function_exists( "mb_substr" ) ) {
			if ( ereg( "MSIE|Opera", $_SERVER["HTTP_USER_AGENT"] ) ) {
				$i      = 0;
				$c_name = '';
				mb_internal_encoding( "SJIS" );
				while ( $j = mb_substr( $names, $i, 1 ) ) {
					$j = mb_convert_encoding( $j, "UTF-16", "SJIS" );
					$c_name .= "%u" . bin2hex( $j );
					$i++;
				}
				header( "Set-Cookie: namec=$c_name; expires=" . gmdate( "D, d-M-Y H:i:s", time() + 7 * 24 * 3600 ) . " GMT", false );
			} else {
				$c_name = $names;
				setcookie( "namec", $c_name, time() + 7 * 24 * 3600 );
				/* 1 week cookie expiration */
			}
		}
		
		if ( !$resto )
			prune_old();
		
		if ( $dest && file_exists( $dest ) ) {
			rename( $dest, $path . $tim . $ext );
			if ( USE_THUMB ) {
				thumb( $path, $tim, $ext );
			}
		}
		updatelog();
		
		$nokolastno = $lastno + 1;
		if ( !$resto && $noko == 1 ) {
			//Noko'd OP
			$redirect = PHP_SELF . '?res=' . $nokolastno;
		} else if ( $noko == 1 ) {
			//noko'd reply
			$redirect = PHP_SELF . '?res=' . $resto;
		} else {
			//No noko
			$redirect = PHP_SELF;
		}
		
		if ( DEBUG_MODE ) {
			echo "<html><head><meta http-equiv=\"refresh\" content=\"2;URL=" . $redirect . "\" /></head>";
		} else {
			echo "<html><head><meta http-equiv=\"refresh\" content=\"0;URL=" . $redirect . "\" /></head>";
		}
		echo "<body>$mes " . S_SCRCHANGE . "</body></html>";
	} else {
		error( S_CAPFAIL, $dest );
	}
}

function sticky_this( $no )
{
	$rootnum = "202707070000";
	mysql_query( 'UPDATE ' . SQLLOG . " SET sticky='1' , root='" . $rootnum . " WHERE no='" . mysql_real_escape_string( $no ) . "'" );
}

function autosage_this( $no )
{
	mysql_query( 'UPDATE ' . SQLLOG . " SET permasage='1' WHERE no='" . mysql_real_escape_string( $no ) . "'" );
}

function lock_this( $no )
{
	mysql_query( 'UPDATE ' . SQLLOG . " SET locked='1' WHERE no='" . mysql_real_escape_string( $no ) . "'" );
}

//OP thumbnail creation
function thumb( $path, $tim, $ext )
{
	if ( !function_exists( "ImageCreate" ) || !function_exists( "ImageCreateFromJPEG" ) )
		return;
	$fname     = $path . $tim . $ext;
	$thumb_dir = THUMB_DIR; //thumbnail directory
	$outpath   = $thumb_dir . $tim . 's.jpg';
	if ( !$resto ) {
		$width  = MAX_W; //output width
		$height = MAX_H; //output height
	} else {
		$width  = MAXR_W; //output width (imgreply)
		$height = MAXR_H; //output height (imgreply)
	}
	// width, height, and type are aquired
	$size                   = GetImageSize( $fname );
	$memory_limit_increased = false;
	if ( $size[0] * $size[1] > 3000000 ) {
		$memory_limit_increased = true;
		ini_set( 'memory_limit', memory_get_usage() + $size[0] * $size[1] * 10 ); // for huge images
	}
	switch ( $size[2] ) {
		case 1:
			if ( function_exists( "ImageCreateFromGIF" ) ) {
				$im_in = ImageCreateFromGIF( $fname );
				if ( $im_in ) {
					break;
				}
			}
			if ( !is_executable( realpath( "/www/global/gif2png" ) ) || !function_exists( "ImageCreateFromPNG" ) )
				return;
			@exec( realpath( "/www/global/gif2png" ) . " $fname", $a );
			if ( !file_exists( $path . $tim . '.png' ) )
				return;
			$im_in = ImageCreateFromPNG( $path . $tim . '.png' );
			unlink( $path . $tim . '.png' );
			if ( !$im_in )
				return;
			break;
		case 2:
			$im_in = ImageCreateFromJPEG( $fname );
			if ( !$im_in ) {
				return;
			}
			break;
		case 3:
			if ( !function_exists( "ImageCreateFromPNG" ) )
				return;
			$im_in = ImageCreateFromPNG( $fname );
			if ( !$im_in ) {
				return;
			}
			break;
		default:
			return;
	}
	// Resizing
	if ( $size[0] > $width || $size[1] > $height || $size[2] == 1 ) {
		$key_w = $width / $size[0];
		$key_h = $height / $size[1];
		( $key_w < $key_h ) ? $keys = $key_w : $keys = $key_h;
		$out_w = ceil( $size[0] * $keys ) + 1;
		$out_h = ceil( $size[1] * $keys ) + 1;
		/*if ($size[2]==1) {
		$out_w = $size[0];
		$out_h = $size[1];
		} //what was this for again? */
	} else {
		$out_w = $size[0];
		$out_h = $size[1];
	}
	// the thumbnail is created
	if ( function_exists( "ImageCreateTrueColor" ) && get_gd_ver() == "2" ) {
		$im_out = ImageCreateTrueColor( $out_w, $out_h );
	} else {
		$im_out = ImageCreate( $out_w, $out_h );
	}
	// copy resized original
	ImageCopyResampled( $im_out, $im_in, 0, 0, 0, 0, $out_w, $out_h, $size[0], $size[1] );
	// thumbnail saved
	ImageJPEG( $im_out, $outpath, 60 );
	//chmod($thumb_dir.$tim.'s.jpg',0666);
	// created image is destroyed
	ImageDestroy( $im_in );
	ImageDestroy( $im_out );
	if ( isset( $pdfjpeg ) ) {
		unlink( $pdfjpeg );
	} // if PDF was thumbnailed delete the orig jpeg
	if ( $memory_limit_increased )
		ini_restore( 'memory_limit' );
	
	return $outpath;
}

//check version of gd
function get_gd_ver()
{
	if ( function_exists( "gd_info" ) ) {
		$gdver   = gd_info();
		$phpinfo = $gdver["GD Version"];
	} else { //earlier than php4.3.0
		ob_start();
		phpinfo( 8 );
		$phpinfo = ob_get_contents();
		ob_end_clean();
		$phpinfo = strip_tags( $phpinfo );
		$phpinfo = stristr( $phpinfo, "gd version" );
		$phpinfo = stristr( $phpinfo, "version" );
	}
	$end     = strpos( $phpinfo, "." );
	$phpinfo = substr( $phpinfo, 0, $end );
	$length  = strlen( $phpinfo ) - 1;
	$phpinfo = substr( $phpinfo, $length );
	return $phpinfo;
}
//md5 calculation for earlier than php4.2.0
function md5_of_file( $inFile )
{
	if ( file_exists( $inFile ) ) {
		if ( function_exists( 'md5_file' ) ) {
			return md5_file( $inFile );
		} else {
			$fd           = fopen( $inFile, 'r' );
			$fileContents = fread( $fd, filesize( $inFile ) );
			fclose( $fd );
			return md5( $fileContents );
		}
	} else {
		return false;
	}
}
/* text plastic surgery */
function CleanStr( $str )
{
	global $admin;
	$str = trim( $str ); //blankspace removal
	if ( get_magic_quotes_gpc() ) { //magic quotes is deleted (?)
		$str = stripslashes( $str );
	}
	if ( $admin != PANEL_PASS ) { //admins can use tags
		$str = htmlspecialchars( $str ); //remove html special chars
		$str = str_replace( "&amp;", "&", $str ); //remove ampersands
	}
	return str_replace( ",", "&#44;", $str ); //remove commas
}

//check for table existance
function table_exist( $table )
{
	$result = mysql_call( "show tables like '$table'" );
	if ( !$result ) {
		return 0;
	}
	$a = mysql_fetch_row( $result );
	mysql_free_result( $result );
	return $a;
}

/* user image deletion */
function usrdel( $no, $pwd )
{
	global $path, $pwdc, $onlyimgdel;
	$host    = $_SERVER["REMOTE_ADDR"];
	$delno   = array();
	$delflag = FALSE;
	reset( $_POST );
	while ( $item = each( $_POST ) ) {
		if ( $item[1] == 'delete' ) {
			array_push( $delno, $item[0] );
			$delflag = TRUE;
		}
	}
	if ( $pwd == "" && $pwdc != "" )
		$pwd = $pwdc;
	$countdel = count( $delno );
	
	$flag = FALSE;
	for ( $i = 0; $i < $countdel; $i++ ) {
		if ( !$result = mysql_call( "select no,ext,tim,pwd,host from " . SQLLOG . " where no=" . $delno[$i] ) ) {
			echo S_SQLFAIL;
		} else {
			while ( $resrow = mysql_fetch_row( $result ) ) {
				list( $dno, $dext, $dtim, $dpass, $dhost ) = $resrow;
				if ( substr( md5( $pwd ), 2, 8 ) == $dpass || substr( md5( $pwdc ), 2, 8 ) == $dpass || $dhost == $host || PANEL_PASS == $pwd ) {
					$flag    = TRUE;
					$delfile = $path . $dtim . $dext; //path to delete
					if ( !$onlyimgdel ) {
						if ( !mysql_call( "delete from " . SQLLOG . " where no=" . $dno ) ) {
							echo S_SQLFAIL;
						} //sql is broke
					}
					$findchildren = mysql_query( "SELECT * FROM " . SQLLOG . " where  resto=" . $dno );
					if ( mysql_num_rows( $findchildren ) > 0 ) {
						$eatchildren = mysql_call( "DELETE FROM " . SQLLOG . " where resto=" . $dno );
						mysql_query( $eatchildren );
					}
					if ( is_file( $delfile ) )
						unlink( $delfile ); //Deletion
					if ( is_file( THUMB_DIR . $dtim . 's.jpg' ) )
						unlink( THUMB_DIR . $dtim . 's.jpg' ); //Deletion
				}
			}
			mysql_free_result( $result );
		}
	}
	if ( !$flag )
		error( S_BADDELPASS );
}

/*password validation */
function valid( $pass )
{
	if ( $pass && $pass != PANEL_PASS )
		error( S_WRONGPASS );
	
	head( $dat );
	echo $dat;
	echo "[<a href=\"" . PHP_SELF2 . "\">" . S_RETURNS . "</a>]\n";
	echo "[<a href=\"" . PHP_SELF . "\">" . S_LOGUPD . "</a>]\n";
	echo "<div class=\"passvalid\">" . S_MANAMODE . "</div>\n";
	echo "<p><form action=\"" . PHP_SELF . "\" method=\"post\">\n";
	// Mana login form
	if ( !$pass ) {
		echo "<div class=\passvalid\"><input type=radio name=admin value=del checked>" . S_MANAREPDEL;
		echo "<input type=radio name=admin value=post>" . S_MANAPOST . "<p>";
		echo "<input type=hidden name=mode value=admin>\n";
		echo "<input type=password name=pass size=8>";
		echo "<input type=submit value=\"" . S_MANASUB . "\"></form></div>\n";
		die( "</body></html>" );
	}
}

function ban( $ip, $pubreason, $staffreason, $banlength )
{
	$placedOn = time();
	$query    = mysql_query( "SELECT ip FROM " . SQLBANLOG . " WHERE ip = '$ip'" );
	switch ( $banlength ) {
		case 'warn':
			$banset = '100';
			break;
		case '3hr':
			$banset = '1';
			break;
		case '3day':
			$banset = '2';
			break;
		case '1wk':
			$banset = '3';
			break;
		case '1mon':
			$banset = '4';
			break;
		case 'perma':
			$banset = '-1';
			break;
		default:
			//Sure is 2007 around here
			$banset = '9001';
	}
	if ( mysql_num_rows( $query ) == 0 ) {
		$sql = "INSERT INTO " . SQLBANLOG . " (ip, pubreason, staffreason, banlength, placedOn, board) VALUES ('$ip', '$pubreason', '$staffreason', '$banset', '$placedOn', " . BOARD_DIR . ")";
		
		if ( mysql_query( $sql ) ) {
			if ( $banset == '100' ) {
				echo "Warned " . $ip . " for public reason: <br /><b> " . $pubreason . " </b><br />";
				echo "Logged private reason: <br /><b> " . $staffreason . " </b>";
			} else {
				echo "Banned (" . $banlength . ") " . $ip . " for public reason: <br /><b> " . $pubreason . " </b><br />";
				echo "Logged private reason: <br /><b> " . $staffreason . " </b>";
			}
		} else {
			echo "ERROR: Could not execute $sql. " . mysql_error();
		}
	} else {
		echo "This IP is already banned!";
	}
	mysql_free_result( $query );
}

/* Admin deletion */
function admindel( $pass )
{
	global $path, $onlyimgdel;
	$delno   = array(
		 dummy 
	);
	$delflag = FALSE;
	reset( $_POST );
	while ( $item = each( $_POST ) ) {
		if ( $item[1] == 'delete' ) {
			array_push( $delno, $item[0] );
			$delflag = TRUE;
		}
	}
	if ( $delflag ) {
		if ( !$result = mysql_call( "select * from " . SQLLOG . "" ) ) {
			echo S_SQLFAIL;
		}
		$find = FALSE;
		while ( $row = mysql_fetch_row( $result ) ) {
			list( $no, $now, $name, $email, $sub, $com, $host, $pwd, $ext, $w, $h, $tim, $time, $md5, $fsize,  ) = $row;
			if ( $onlyimgdel == on ) {
				if ( array_search( $no, $delno ) ) { //only a picture is deleted
					$delfile = $path . $tim . $ext; //only a picture is deleted
					if ( is_file( $delfile ) )
						unlink( $delfile ); //delete
					if ( is_file( THUMB_DIR . $tim . 's.jpg' ) )
						unlink( THUMB_DIR . $tim . 's.jpg' ); //delete
				}
			} else {
				if ( array_search( $no, $delno ) ) { //It is empty when deleting
					$find = TRUE;
					if ( !mysql_call( "delete from " . SQLLOG . " where no=" . $no ) ) {
						echo S_SQLFAIL;
					}
					//eat the baby posts too if we kill the parent (OP) post
					$findchildren = mysql_call( "SELECT * FROM " . SQLLOG . " where  resto=" . $no );
					if ( mysql_num_rows( $findchildren ) > 0 ) {
						$eatchildren = mysql_call( "DELETE FROM " . SQLLOG . " where resto=" . $no );
						mysql_query( $eatchildren );
					}
					$delfile = $path . $tim . $ext; //Delete file
					if ( is_file( $delfile ) )
						unlink( $delfile ); //Delete
					if ( is_file( THUMB_DIR . $tim . 's.jpg' ) )
						unlink( THUMB_DIR . $tim . 's.jpg' ); //Delete
				}
			}
		}
		mysql_free_result( $result );
		if ( $find ) { //log renewal
		}
	}
	
	function calculate_age( $timestamp, $comparison = '' )
	{
		$units = array(
			 'second' => 60,
			'minute' => 60,
			'hour' => 24,
			'day' => 7,
			'week' => 4.25, // FUCK YOU GREGORIAN CALENDAR
			'month' => 12 
		);
		
		if ( empty( $comparison ) ) {
			$comparison = $_SERVER['REQUEST_TIME'];
		}
		$age_current_unit = abs( $comparison - $timestamp );
		foreach ( $units as $unit => $max_current_unit ) {
			$age_next_unit = $age_current_unit / $max_current_unit;
			if ( $age_next_unit < 1 ) // are there enough of the current unit to make one of the next unit?
				{
				$age_current_unit = floor( $age_current_unit );
				$formatted_age    = $age_current_unit . ' ' . $unit;
				return $formatted_age . ( $age_current_unit == 1 ? '' : 's' );
			}
			$age_current_unit = $age_next_unit;
		}
		
		$age_current_unit = round( $age_current_unit, 1 );
		$formatted_age    = $age_current_unit . ' year';
		return $formatted_age . ( floor( $age_current_unit ) == 1 ? '' : 's' );
		
	}
	
	
	// Deletion screen display
	echo "<input type=hidden name=mode value=admin>\n";
	echo "<input type=hidden name=admin value=del>\n";
	echo "<input type=hidden name=pass value=\"$pass\">\n";
	echo "<div class=\"dellist\">" . S_DELLIST . "</div>\n";
	echo "<div class=\"delbuttons\"><input type=submit value=\"" . S_ITDELETES . "\">";
	echo "<input type=reset value=\"" . S_MDRESET . "\">";
	echo "[<input type=checkbox name=onlyimgdel value=on><!--checked-->" . S_MDONLYPIC . "]</div>";
	echo "<table class=\"postlists\">\n";
	echo "<tr class=\"managehead\">" . S_MDTABLE1;
	echo S_MDTABLE2;
	echo "</tr>\n";
	
	if ( !$result = mysql_call( "select * from " . SQLLOG . " order by no desc" ) ) {
		echo S_SQLFAIL;
	}
	$j = 0;
	while ( $row = mysql_fetch_row( $result ) ) {
		$j++;
		$img_flag = FALSE;
		list( $no, $now, $name, $email, $sub, $com, $host, $pwd, $ext, $w, $h, $tim, $time, $md5, $fsize, $fname, $sticky, $permasage, $locked, $root, $resto ) = $row;
		// Format
		$now = ereg_replace( '.{2}/(.*)$', '\1', $now );
		$now = ereg_replace( '\(.*\)', ' ', $now );
		if ( strlen( $name ) > 10 )
			$name = substr( $name, 0, 9 ) . ".";
		if ( strlen( $sub ) > 10 )
			$sub = substr( $sub, 0, 9 ) . ".";
		if ( $email )
			$name = "<a href=\"mailto:$email\">$name</a>";
		$com = str_replace( "<br />", " ", $com );
		$com = htmlspecialchars( $com );
		if ( strlen( $com ) > 20 )
			$com = substr( $com, 0, 18 ) . ".";
		// Link to the picture
		if ( $ext && is_file( $path . $tim . $ext ) ) {
			$img_flag = TRUE;
			$clip     = "<a class=\"thumbnail\" target=\"_blank\" href=\"" . IMG_DIR . $tim . $ext . "\">" . $tim . $ext . "<span><img src=\"" . THUMB_DIR . $tim . 's.jpg' . "\" width=\"100\" height=\"100\" /></span></a><br />";
			if ( $fsize >= 1048576 ) {
					$size = round( ( $fsize / 1048576 ), 2 ) . " M";
					$fsize = $asize;
				} else if ( $fsize >= 1024 ) {
					$size = round( $fsize / 1024 ) . " K";
					$fsize = $asize;
				} else {
					$size = $fsize . " ";
					$fsize = $asize;
				}
			$all += $asize; //total calculation
			$md5 = substr( $md5, 0, 10 );
		} else {
			$clip = "";
			$size = 0;
			$md5  = "";
		}
		$class = ( $j % 2 ) ? "row1" : "row2"; //BG color
		
		if ($resto == '0') {
			$resto = 'Orig. post';
		}
		if ($sticky == '1') {
			$warnSticky = "<b><font color=\"FF101A\">(Sticky)</font></b>";
		} else {
			$warnSticky = '';
		}
		
		echo "<tr class=$class><td><input type=checkbox name=\"$no\" value=delete>$warnSticky</td>";
		echo "<td>$no</td><td>$resto</td><td>$now</td><td>$sub</td>";
		echo "<td>$name</b></td><td>$com</td>";
		echo "<td>$host</td><td>$clip($size)</td><td>$md5</td><td>$fname</td><td>" . calculate_age( $time ) . "</td>\n";
		echo "</tr>\n";
	}
	mysql_free_result( $result );
	
	echo "</table><input type=submit value=\"" . S_ITDELETES . "$msg\">";
	echo "<input type=reset value=\"" . S_RESET . "\"></form>";
	echo "<br /><hr /><br /><form method=\"post\" action=\"" . PHP_SELF . "?mode=banish\" >
    <table><tr><th>IP</th><td><input required type='text' name='ip_to_ban' /></td></tr>
    <tr><th>Public Reason</th>
    <td><input required type='text' name='pubreason' /></td></tr>
    <tr><th>Staff Reason</th>
    <td><input required type='text' name='staffreason' /></td></tr>
    <tr><th>Length</th>
    <td><select name =\"timebannedfor\" value=\"timebannedfor\">
    <option value=\"warn\">Warn</option>
    <option value=\"3hr\">3 hours</option>
    <option value=\"3day\">3 days</option>
    <option value=\"1wk\">1 week</option>
    <option value=\"1mon\">1 month</option>
    <option value=\"perma\">Permanent</option>
    </select></td><tr><th>Ban from:</th><td><select name =\"bannedFrom\" value=\"bannedFrom\">
    <option value=\"thisBoard\">This board</option>
    <option value=\"allBoards\">All boards</option>
    </select></td></tr></table>
    <input type=hidden name=pass value=\"$pass\">
    <input type=\"submit\" value=\"" . S_BANS . "\"/></form>" . S_BANS_EXTRA . "";
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/img.css\" />";
	
	$all = (int) ( $all / 1024 );
	echo "[ " . S_IMGSPACEUSAGE . $all . "</b> KB ]";
	die( "</body></html>" );
}

/*-----------Main-------------*/
switch ( $mode ) {
	case 'regist':
		regist( $name, $email, $sub, $com, '', $pwd, $upfile, $upfile_name, $resto, $num );
		break;
	case 'admin':
		valid( $pass );
		if ( $admin == "del" )
			admindel( $pass );
		if ( $admin == "post" ) {
			echo "</form>";
			form( $post, $res, 1 );
			echo $post;
			die( "</body></html>" );
		}
		break;
	case 'banish':
		$ip          = $_POST['ip_to_ban'];
		$pubreason   = mysql_escape_string( $_POST['pubreason'] );
		$staffreason = mysql_escape_string( $_POST['staffreason'] );
		$banlength   = mysql_escape_string( $_POST['timebannedfor'] );
		ban( $ip, $pubreason, $staffreason, $banlength );
		echo '<br/ > <a href="' . PHP_SELF . '?mode=admin" />Return</a>';
		break;
	case 'usrdel':
		usrdel( $no, $pwd );
	default:
		if ( $res ) {
			updatelog( $res );
		} else {
			updatelog();
			echo "<meta http-equiv=\"refresh\" content=\"0;URL=" . PHP_SELF2 . "\" />";
		}
		
		
}

$referer  = base64_encode( $_SERVER["HTTP_REFERER"] );
$thispage = base64_encode( $_SERVER["REQUEST_URI"] );
$id       = "62059";
$time     = time();
?>
