<?php
session_start();
/*
=================================
===Saguaro Imageboard Software===
=================================
>>1.0
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

if ( BOARD_DIR == 'test' ) {
	ini_set( 'display_errors', 1 );
} else {
	ini_set( 'display_errors', 0 );
}

if (!is_dir(RES_DIR))
	mkdir(RES_DIR, 0777, true);

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
    tn_w     int,
    th_h     int,
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

if ( !table_exist( "reports" ) ) {		
	echo ( S_TCREATE . "reports log<br />" );		
	$result = mysql_call( "create table reports (		
    no   VARCHAR(25) PRIMARY KEY,		
    reason  VARCHAR(250),		
    ip  VARCHAR(250),		
    board  VARCHAR(250))" );		
			
	if ( !$result ) {		
		echo S_TCREATEF . "reports log<br />";		
	}		
}		
	
if ( !table_exist( "loginattempts" ) ) {		
	echo ( S_TCREATE . "loginattempts<br />" );		
	$result = mysql_call( "create table loginattempts (		
    userattempt   VARCHAR(25) PRIMARY KEY,		
    passattempt  VARCHAR(250),		
    board  VARCHAR(250),		
    ip  VARCHAR(250),		
    attemptno  VARCHAR(50))" );		
			
	if ( !$result ) {		
		echo S_TCREATEF . "loginattempts<br />";		
	}		
}	 
   
if ( !table_exist( SQLMODSLOG ) ) {		
	echo ( S_TCREATE . SQLMODSLOG . "<br />" );		
	$result = mysql_call( "create table " . SQLMODSLOG . " (		
    user   VARCHAR(25) PRIMARY KEY,		
    password  VARCHAR(250),		
    allowed  VARCHAR(250),		
    denied  VARCHAR(250))" );		
			
	if ( !$result ) {		
		echo S_TCREATEF . SQLMODSLOG . "<br />";		
	}

	mysql_call("INSERT INTO " . SQLMODSLOG . " (user, password, allowed, denied) VALUES ('admin', 'guest', 'janitor_board,moderator,admin,manager', 'none') ");
	echo "Default account inserted. Username: admin, Passwor: guest.";
}		
		
if ( !table_exist( SQLDELLOG ) ) {		
	echo ( S_TCREATE . SQLDELLOG . "<br />" );		
	$result = mysql_call( "create table " . SQLDELLOG . " (		
    imgonly   VARCHAR(25) PRIMARY KEY,		
    postno  VARCHAR(250),		
    board  VARCHAR(250),		
    name  VARCHAR(250),		
    sub  VARCHAR(50),		
    com VARCHAR(" . S_POSTLENGTH . "),		
    img VARCHAR(250),	
    filename VARCHAR(250),		
    admin VARCHAR(100))" );		
			
	if ( !$result ) {		
		echo S_TCREATEF . SQLDELLOG . "<br />";		
	}		
}

function prune_old()
{
	//This prunes old posts that are pushed off the bottom of last page, called once after each post is made in regist()
	log_cache();
    
    if ( PAGE_MAX >= 1 ) {
		
		$maxposts   = LOG_MAX;
		$maxthreads = ( PAGE_MAX > 0 ) ? ( PAGE_MAX * PAGE_DEF ) : 0;
		//number of pages x how many threads per page
		
		if ($maxthreads) {
				$exp_order = 'no';
				if (EXPIRE_NEGLECTED == 1)
						$exp_order = 'root';
				$result = mysql_call("SELECT no FROM " . SQLLOG . " WHERE sticky=0 AND resto=0 ORDER BY $exp_order ASC");
				$threadcount = mysql_num_rows($result);
				while ($row = mysql_fetch_array($result) and $threadcount >= $maxthreads) {
						delete_post($row['no'], 'trim', 0, 1); // imgonly=0, automatic=1, children=1
						$threadcount--;
				}
				mysql_free_result($result);
				// Original max-posts method (note: cleans orphaned posts later than parent posts)
		} else {
				// make list of stickies
				$stickies = array(); // keys are stickied thread numbers
				$result   = mysql_call("SELECT no from " . SQLLOG . " where sticky=1 and resto=0");
				while ($row = mysql_fetch_array($result)) {
						$stickies[$row['no']] = 1;
				}
				
				$result    = mysql_call("SELECT no,resto,sticky FROM " . SQLLOG . " ORDER BY no ASC");
				$postcount = mysql_num_rows($result);
				while ($row = mysql_fetch_array($result) and $postcount >= $maxposts) {
						// don't delete if this is a sticky thread
						if ($row['sticky'] == 1)
								continue;
						// don't delete if this is a REPLY to a sticky
						if ($row['resto'] != 0 && $stickies[$row['resto']] == 1)
								continue;
						delete_post($row['no'], 'trim', 0, 1, 0); // imgonly=0, automatic=1, children=0
						$postcount--;
				}
				mysql_free_result($result);
		}
    }
}


function rebuildqueue_create_table()
{
	$sql = <<<EOSQL
CREATE TABLE `rebuildqueue` (
  `board` char(4) NOT NULL,
  `no` int(11) NOT NULL,
  `ownedby` int(11) NOT NULL default '0',
  `ts` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`board`,`no`,`ownedby`)
)
EOSQL;
	mysql_call( $sql );
}

function rebuildqueue_add( $no )
{
	$board = BOARD_DIR;
	$no    = (int) $no;
	for ( $i = 0; $i < 2; $i++ )
		if ( !mysql_call( "INSERT IGNORE INTO rebuildqueue (board,no) VALUES ('$board','$no')" ) )
			rebuildqueue_create_table();
		else
			break;
}

function rebuildqueue_remove( $no )
{
	$board = BOARD_DIR;
	$no    = (int) $no;
	for ( $i = 0; $i < 2; $i++ )
		if ( !mysql_call( "DELETE FROM rebuildqueue WHERE board='$board' AND no='$no'" ) )
			rebuildqueue_create_table();
		else
			break;
}

function rebuildqueue_take_all()
{
	$board = BOARD_DIR;
	$uid   = mt_rand( 1, mt_getrandmax() );
	for ( $i = 0; $i < 2; $i++ )
		if ( !mysql_call( "UPDATE rebuildqueue SET ownedby=$uid,ts=ts WHERE board='$board' AND ownedby=0" ) )
			rebuildqueue_create_table();
		else
			break;
	$q     = mysql_call( "SELECT no FROM rebuildqueue WHERE board='$board' AND ownedby=$uid" );
	$posts = array();
	while ( $post = mysql_fetch_assoc( $q ) )
		$posts[] = $post['no'];
	return $posts;
}

// build a structure out of all the posts in the database.
// this lets us replace a LOT of queries with a simple array access.
// it only builds the first time it was called.
// rather than calling log_cache(1) to rebuild everything,
// you should just manipulate the structure directly.
function log_cache( $invalidate = 0 )
{
	global $log, $ipcount, $mysql_unbuffered_reads, $lastno;
	$ips     = array();
	$threads = array(); // no's
	if ( $invalidate == 0 && isset( $log ) )
		return;
	$log = array(); // no -> [ data ]
	mysql_call( "SET read_buffer_size=1048576" );
	$mysql_unbuffered_reads = 1;
	$query                  = mysql_call( "SELECT * FROM " . SQLLOG );
	$offset                 = 0;
	$lastno                 = 0;
	while ( $row = mysql_fetch_assoc( $query ) ) {
		if ( $row['no'] > $lastno )
			$lastno = $row['no'];
		$ips[$row['host']] = 1;
		// initialize log row if necessary
		if ( !isset( $log[$row['no']] ) ) {
			$log[$row['no']]             = $row;
			$log[$row['no']]['children'] = array();
		} else { // otherwise merge it with $row
			foreach ( $row as $key => $val )
				$log[$row['no']][$key] = $val;
		}
		// if this is a reply
		if ( $row['resto'] ) {
			// initialize whatever we need to
			if ( !isset( $log[$row['resto']] ) )
				$log[$row['resto']] = array();
			if ( !isset( $log[$row['resto']]['children'] ) )
				$log[$row['resto']]['children'] = array();
			
			// add this post to list of children
			$log[$row['resto']]['children'][$row['no']] = 1;
			if ( $row['fsize'] ) {
				if ( !isset( $log[$row['resto']]['imgreplycount'] ) )
					$log[$row['resto']]['imgreplycount'] = 0;
				else
					$log[$row['resto']]['imgreplycount']++;
			}
		}

	}
	
	$query = mysql_call( "SELECT no FROM " . SQLLOG . " WHERE root>0 order by root desc" );
	while ( $row = mysql_fetch_assoc( $query ) ) {
		if ( isset( $log[$row['no']] ) && $log[$row['no']]['resto'] == 0 )
			$threads[] = $row['no'];
	}
	$log['THREADS']         = $threads;
	$mysql_unbuffered_reads = 0;
	
	// calculate old-status for PAGE_MAX mode
	if ( EXPIRE_NEGLECTED != 1 ) {
		rsort( $threads, SORT_NUMERIC );
		
		$threadcount = count( $threads );
		if ( PAGE_MAX > 0 ) // the lowest 5% of maximum threads get marked old
			for ( $i = floor( 0.95 * PAGE_MAX * PAGE_DEF ); $i < $threadcount; $i++ ) {
				if ( !$log[$threads[$i]]['sticky'] && EXPIRE_NEGLECTED != 1 )
					$log[$threads[$i]]['old'] = 1;
			} else { // threads w/numbers below 5% of LOG_MAX get marked old
			foreach ( $threads as $thread ) {
				if ( $lastno - LOG_MAX * 0.95 > $thread )
					if ( !$log[$thread]['sticky'] )
						$log[$thread]['old'] = 1;
			}
		}
	}
	
	$ipcount = count( $ips );
}

// truncate $str to $max_lines lines and return $str and $abbr
// where $abbr = whether or not $str was actually truncated
function abbreviate( $str, $max_lines )
{
	if ( !defined( 'MAX_LINES_SHOWN' ) ) {
		if ( defined( 'BR_CHECK' ) ) {
			define( 'MAX_LINES_SHOWN', BR_CHECK );
		} else {
			define( 'MAX_LINES_SHOWN', 20 );
		}
		$max_lines = MAX_LINES_SHOWN;
	}
	$lines = explode( "<br />", $str );
	if ( count( $lines ) > $max_lines ) {
		$abbr  = 1;
		$lines = array_slice( $lines, 0, $max_lines );
		$str   = implode( "<br />", $lines );
	} else {
		$abbr = 0;
	}
	
	//close spans after abbreviating
	//XXX will not work with more html - use abbreviate_html from shiichan
	$str .= str_repeat( "</span>", substr_count( $str, "<span" ) - substr_count( $str, "</span" ) );
	
	return array(
		 $str,
		$abbr 
	);
}

// print $contents to $filename by using a temporary file and renaming it 
// (makes *.html and *.gz if USE_GZIP is on)
function print_page( $filename, $contents, $force_nogzip = 0 )
{
	$gzip     = ( USE_GZIP == 1 && !$force_nogzip );
	$tempfile = tempnam( realpath( RES_DIR ), "tmp" ); //note: THIS actually creates the file
	file_put_contents( $tempfile, $contents, FILE_APPEND );
	rename( $tempfile, $filename );
	chmod( $filename, 0664 ); //it was created 0600
	
	if ( $gzip ) {
		$tempgz = tempnam( realpath( RES_DIR ), "tmp" ); //note: THIS actually creates the file
		$gzfp   = gzopen( $tempgz, "w" );
		gzwrite( $gzfp, $contents );
		gzclose( $gzfp );
		rename( $tempgz, $filename . '.gz' );
		chmod( $filename . '.gz', 0664 ); //it was created 0600
	}
}


// check whether the current user can perform $action (on $no, for some actions)
// board-level access is cached in $valid_cache.
function valid( $action = 'moderator', $no = 0 )
{
	static $valid_cache; // the access level of the user
	$access_level = array(
		 'none' => 0,
		'janitor' => 1,
		'janitor_this_board' => 2,
		'moderator' => 5,
		'manager' => 10,
		'admin' => 20 
	);
	if ( !isset( $valid_cache ) ) {
		$valid_cache = $access_level['none'];
		if ( isset( $_COOKIE['saguaro_auser'] ) && isset( $_COOKIE['saguaro__apass'] ) ) {
			$user = mysql_real_escape_string( $_COOKIE['saguaro_auser'] );
			$pass = mysql_real_escape_string( $_COOKIE[saguaro_apass'] );
		}
		if ( $user && $pass ) {
			$result = mysql_call( "SELECT allowed,denied FROM " . SQLMODSLOG . " WHERE user='$user' and password='$pass'" );
			list( $allow, $deny ) = mysql_fetch_row( $result );
			mysql_free_result( $result );
			if ( $allow ) {
				$allows             = explode( ',', $allow );
				$seen_janitor_token = false;
				// each token can increase the access level,
				// except that we only know that they're a moderator or a janitor for another board
				// AFTER we read all the tokens
				foreach ( $allows as $token ) {
					if ( $token == 'janitor' )
						$seen_janitor_token = true;
					else if ( $token == 'manager' && $valid_cache < $access_level['manager'] )
						$valid_cache = $access_level['manager'];
					else if ( $token == 'admin' && $valid_cache < $access_level['admin'] )
						$valid_cache = $access_level['admin'];
					else if ( ( $token == BOARD_DIR || $token == 'all' ) && $valid_cache < $access_level['janitor_this_board'] )
						$valid_cache = $access_level['janitor_this_board']; // or could be moderator, will be increased in next step
				}
				// now we can set moderator or janitor status 
				if ( !$seen_janitor_token ) {
					if ( $valid_cache < $access_level['moderator'] )
						$valid_cache = $access_level['moderator'];
				} else {
					if ( $valid_cache < $access_level['janitor'] )
						$valid_cache = $access_level['janitor'];
				}
				if ( $deny ) {
					$denies = explode( ',', $deny );
					if ( in_array( BOARD_DIR, $denies ) ) {
						$valid_cache = $access_level['none'];
					}
				}
			}
		}
	}
	switch ( $action ) {
		case 'moderator':
			return $valid_cache >= $access_level['moderator'];
		case 'admin':
			return $valid_cache >= $access_level['admin'];
		case 'textonly':
			return $valid_cache >= $access_level['moderator'];
		case 'janitor_board':
			return $valid_cache >= $access_level['janitor'];
		case 'manager':
			return $valid_cache >= $access_level['manager'];
		case 'delete':
			if ( $valid_cache >= $access_level['janitor_this_board'] ) {
				return true;
			}
			// if they're a janitor on another board, check for illegal post unlock			
			else if ( $valid_cache >= $access_level['janitor'] ) {
				$query         = mysql_call( "SELECT COUNT(*) from reports WHERE board='" . BOARD_DIR . "' AND no=$no AND cat=2" );
				$illegal_count = mysql_result( $query, 0, 0 );
				mysql_free_result( $query );
				return $illegal_count >= 3;
			}
		case 'reportflood':
			return $valid_cache >= $access_level['janitor'];
		case 'floodbypass':
			return $valid_cache >= $access_level['moderator'];
		default: // unsupported action
			return false;
	}
}

function spoiler_parse( $com )
{
	if ( !find_match_and_prefix( "/\[spoiler\]/", $com, 0, $m ) )
		return $com;
	
	$bl  = strlen( "[spoiler]" );
	$el  = $bl + 1;
	//$st  = '<span class="spoiler" onmouseover="this.style.color=\'#FFF\';" onmouseout="this.style.color=this.style.backgroundColor=\'#000\'" style="color:#000;background:#000">';
    $st  = '<span class="spoiler">';

	$et  = '</span>';
	$ret = $m[0] . $st;
	$lev = 1;
	$off = strlen( $m[0] ) + $bl;
	
	while ( 1 ) {
		if ( !find_match_and_prefix( "@\[/?spoiler\]@", $com, $off, $m ) )
			break;
		list( $txt, $tag ) = $m;
		
		$ret .= $txt;
		$off += strlen( $txt ) + strlen( $tag );
		
		if ( $tag == "[spoiler]" ) {
			$ret .= $st;
			$lev++;
		} else if ( $lev ) {
			$ret .= $et;
			$lev--;
		}
	}
	
	$ret .= substr( $com, $off, strlen( $com ) - $off );
	$ret .= str_repeat( $et, $lev );
	
	return $ret;
}

function updatelog( $resno = 0, $rebuild = 0 )
{
	global $log, $path;
	log_cache();
	
    $find  = false;
	$resno = (int) $resno;
	if ( $resno ) {
		if ( !isset( $log[$resno] ) ) {
			updatelog( 0, $rebuild ); // the post didn't exist, just rebuild the indexes
			return;
		} else if ( $log[$resno]['resto'] ) {
			updatelog( $log[$resno]['resto'], $rebuild ); // $resno is a reply, try rebuilding the parent
			return;
		}
	}
    
	if ( $resno ) {
		$treeline = array(
			 $resno 
		);
		//if(!$treeline=mysql_call("select * from ".SQLLOG." where root>0 and no=".$resno." order by root desc")){echo S_SQLFAIL;}
	} else {
		$treeline = $log['THREADS'];
		//if(!$treeline=mysql_call("select * from ".SQLLOG." where root>0 order by root desc")){echo S_SQLFAIL;}
	}
	
	//Finding the last entry number
	if ( !$result = mysql_call( "select max(no) from " . SQLLOG ) ) {
		echo S_SQLFAIL;
	}
	$row    = mysql_fetch_array( $result );
	$lastno = (int) $row[0];
	mysql_free_result( $result );
	
	$counttree = count( $treeline );
	//$counttree=mysql_num_rows($treeline);
	if ( !$counttree ) {
		$logfilename = PHP_SELF2;
		$dat         = '';
		head( $dat, $resno );
		form( $dat, $resno );
		print_page( $logfilename, $dat );
	}
    
    	if ( UPDATE_THROTTLING >= 1 ) {
		$update_start = time();
		touch( "updatelog.stamp", $update_start );
		$low_priority = false;
		clearstatcache();
		if ( @filemtime( PHP_SELF ) > $update_start - UPDATE_THROTTLING ) {
			$low_priority = true;
			//touch($update_start . ".lowprio");
		} else {
			touch( PHP_SELF, $update_start );
		}
		// 	$mt = @filemtime(PHP_SELF);
		//  	touch($update_start . ".$mt.highprio");
	}
	
	//using CACHE_TTL method
	if ( CACHE_TTL >= 1 ) {
		if ( $resno ) {
			$logfilename = RES_DIR . $resno . PHP_EXT;
		} else {
			$logfilename = PHP_SELF2;
		}
		//if(USE_GZIP == 1) $logfilename .= '.html';
		// if the file has been made and it's younger than CACHE_TTL seconds ago
		clearstatcache();
		if ( file_exists( $logfilename ) && filemtime( $logfilename ) > ( time() - CACHE_TTL ) ) {
			// save the post to be rebuilt later
			rebuildqueue_add( $resno );
			// if it's a thread, try again on the indexes
			if ( $resno && !$rebuild )
				updatelog();
			// and we don't do any more rebuilding on this request
			return true;
		} else {
			// we're gonna update it now, so take it out of the queue
			rebuildqueue_remove( $resno );
			// and make sure nobody else starts trying to update it because it's too old
			touch( $logfilename );
		}
	}
    
    
	for ( $page = 0; $page < $counttree; $page += PAGE_DEF ) {
		$dat = '';
		head( $dat );
        form( $dat, $resno );
		if ( !$resno ) {
			$st = $page;
		}
		$dat .= '<form name= "delform" action="' . PHP_SELF_ABS . '" method="post">';
		
		for ( $i = $st; $i < $st + PAGE_DEF; $i++ ) {	
			list( $_unused, $no ) = each( $treeline );
			//list($no,$sticky,$permasage,$closed,$now,$name,$email,$sub,$com,$host,$pwd,$filename,$ext,$w,$h,$tn_w,$tn_h,$tim,$time,$md5,$fsize,$root,$resto)=mysql_fetch_row($treeline);
			if ( !$no ) {
				break;
			}
			extract( $log[$no] );
			
			// URL and link
			// If not in a thread 
			//$threadurl = "" . PHP_SELF . "?res=$no";
			if ( $email )
				$name = "<a href=\"mailto:$email\" class=\"linkmail\">$name</a>";
			if ( strpos( $sub, "SPOILER<>" ) === 0 ) {
				$sub     = substr( $sub, strlen( "SPOILER<>" ) ); //trim out SPOILER<>
				$spoiler = 1;
			} else
				$spoiler = 0;
			$com = auto_link( $com, $resno );
			if ( !$resno )
				list( $com, $abbreviated ) = abbreviate( $com, MAX_LINES_SHOWN );
			
            if (isset($abbreviated) && $abbreviated)
				$com .= "<br /><span class=\"abbr\">Comment too long. Click <a href=\"" . RES_DIR . ($resto ? $resto : $no) . PHP_EXT . "#$no\">here</a> to view the full text.</span>";

			//OP Post image
			
		  $imgdir   = IMG_DIR;
            $thumbdir = DATA_SERVER . BOARD_DIR . "/" . THUMB_DIR;
            $cssimg = CSS_PATH;
            
// Picture file name
						$img        = $path . $tim . $ext;
						$displaysrc = DATA_SERVER . BOARD_DIR . "/" . $imgdir . $tim . $ext;
						$linksrc    = ((USE_SRC_CGI == 1) ? (str_replace(".cgi", "", $imgdir) . $tim . $ext) : $displaysrc);
						if (defined('INTERSTITIAL_LINK'))
								$linksrc = str_replace(INTERSTITIAL_LINK, "", $linksrc);
						$src      = IMG_DIR . $tim . $ext;
						$longname = $filename . $ext;
						if (strlen($filename) > 40) {
								$shortname = substr($filename, 0, 40) . "(...)" . $ext;
						} else {
								$shortname = $longname;
						}
						// img tag creation
						$imgsrc = "";
						if ($ext) {
								// turn the 32-byte ascii md5 into a 24-byte base64 md5
								$shortmd5 = base64_encode(pack("H*", $md5));
								if ($fsize >= 1048576) {
										$size = round(($fsize / 1048576), 2) . " M";
								} else if ($fsize >= 1024) {
										$size = round($fsize / 1024) . " K";
								} else {
										$size = $fsize . " ";
								}
								if (!$tn_w && !$tn_h && $ext == ".gif") {
										$tn_w = $w;
										$tn_h = $h;
								}
								if ($spoiler) {
										$size   = "Spoiler Image, $size";
										$imgsrc = "<br><a href=\"" . $displaysrc . "\" target=_blank><img src=\"" . SPOILER_THUMB . "\" border=0 align=left hspace=20 alt=\"" . $size . "B\" md5=\"$shortmd5\"></a>";
								} elseif ($tn_w && $tn_h) { //when there is size...
										if (@is_file(THUMB_DIR . $tim . 's.jpg')) {
												$imgsrc = "<br><a href=\"" . $displaysrc . "\" target=_blank><img class=\"postimg\" src=" . $thumbdir . $tim . 's.jpg' . " border=0 align=left width=$tn_w height=$tn_h hspace=20 alt=\"" . $size . "B\" md5=\"$shortmd5\"></a>";
										} else {
												$imgsrc = "<a href=\"" . $displaysrc . "\" target=_blank><span class=\"tn_thread\" title=\"" . $size . "B\">Thumbnail unavailable</span></a>";
										}
								} else {
										if (@is_file(THUMB_DIR . $tim . 's.jpg')) {
												$imgsrc = "<br><a href=\"" . $displaysrc . "\" target=_blank><img class=\"postimg\" src=" . $thumbdir . $tim . 's.jpg' . " border=0 align=left hspace=20 alt=\"" . $size . "B\" md5=\"$shortmd5\"></a>";
										} else {
												$imgsrc = "<a href=\"" . $displaysrc . "\" target=_blank><span class=\"tn_thread\" title=\"" . $size . "B\">Thumbnail unavailable</span></a>";
										}
								}
								if (!is_file($src)) {
										$dat .= '<img src="' . $cssimg . 'filedeleted.gif" alt="File deleted.">';
								} else {
										$dimensions = ($ext == '.pdf') ? 'PDF' : "{$w}x{$h}";
										if ($resno) {
												$dat .= "<span class=\"filesize\">" . S_PICNAME . "<a href=\"$linksrc\" target=\"_blank\">$time$ext</a>-(" . $size . "B, " . $dimensions . ", <span title=\"" . $longname . "\">" . $shortname . "</span>)</span>" . $imgsrc;
										} else {
												$dat .= "<span class=\"filesize\">" . S_PICNAME . "<a href=\"$linksrc\" target=\"_blank\">$time$ext</a>-(" . $size . "B, " . $dimensions . ")</span>" . $imgsrc;
										}
								}
						}
            
            //  Main creation
			
            $dat .= "<a name=\"$resno\"></a>\n<input type=checkbox name=\"$no\" value=delete><span class=\"filetitle\">$sub</span> \n";
			$dat .= "<span class=\"postername\">$name</span> $now <span id=\"nothread$no\">";

			if ($sticky == 1) 
				$stickyicon = ' <img src="' . CSS_PATH . '/sticky.gif" alt="sticky"> ';
            else 
                $stickyicon = '';
			
            if ($locked == 1) 
				$stickyicon .= ' <img src="' . CSS_PATH . '/locked.gif" alt="closed"> ';
			
            if ($resno) {
				$dat .= "<a href=\"#$no\" class=\"quotejs\">No.</a><a href=\"javascript:quote('$no')\" class=\"quotejs\">$no</a> $stickyicon &nbsp; ";
			} else {
				$dat .= "<a href=\"" . RES_DIR . $no . PHP_EXT . "#" . $no . "\" class=\"quotejs\">No.</a><a href=\"" . RES_DIR . $no . PHP_EXT . "#q" . $no . "\" class=\"quotejs\">$no</a> $stickyicon &nbsp; [<a href=\"" . RES_DIR . $no . PHP_EXT . "\">" . S_REPLY . "</a>]";
			}
            
			$dat .= "</span>\n<blockquote>$com</blockquote>";

            // Deletion pending
			if ( isset( $log[$no]['old'] ) )
				$dat .= "<span class=\"oldpost\">" . S_OLD . "</span><br>\n";
			
			$resline = $log[$no]['children'];
			ksort( $resline );
			$countres = count( $log[$no]['children'] );
			$t        = 0;
			if ( $sticky == 1 ) {
				$disam = 1;
			} elseif ( defined( 'REPLIES_SHOWN' ) ) {
				$disam = REPLIES_SHOWN;
			} else {
				$disam = 5;
			}
			$s   = $countres - $disam;
			$cur = 1;
			while ( $s >= $cur ) {
				list( $row ) = each( $resline );
				if ( $log[$row]["fsize"] != 0 ) {
					$t++;
				}
				$cur++;
			}
			if ( $countres != 0 )
				reset( $resline );
			
			if ( !$resno ) {
				if ( $s < 2 ) {
					$posts = " post";
				} else {
					$posts = " posts";
				}
				if ( $t < 2 ) {
					$replies = "reply";
				} else {
					$replies = "replies";
				}
				if (($s > 0) && ($t == 0)) {
					$dat .= "<span class=\"omittedposts\">" . $s . $posts . " omitted. Click <a href=\"" . RES_DIR . $no . PHP_EXT . "#" . $no . "\"> Reply</a> to view.</span>\n";
				} elseif (($s > 0) && ($t > 0)) {
					$dat .= "<span class=\"omittedposts\">" . $s . $posts . " and " . $t . " image " . $replies . " omitted. Click <a href=\"" . RES_DIR . $no . PHP_EXT . "#" . $no . "\"> Reply</a> to view.</span>\n";
				}
			} else {
				$s = 0;
			}
			
			while ( list( $resrow ) = each( $resline ) ) {
				if ( $s > 0 ) {
					$s--;
					continue;
				}
				//list($no,$sticky,$permasage,$closed,$now,$name,$email,$sub,$com,$host,$pwd,$filename,$ext,$w,$h,$tn_w,$tn_h,$tim,$time,$md5,$fsize,$root,$resto)=$resrow;
				extract( $log[$resrow] );
				if ( !$no ) {
					break;
				}
				
				// URL and e-mail
				if ( $email )
					$name = "<a href=\"mailto:$email\" class=\"linkmail\">$name</a>";
				if ( strpos( $sub, "SPOILER<>" ) === 0 ) {
					$sub     = substr( $sub, strlen( "SPOILER<>" ) ); //trim out SPOILER<>
					$spoiler = 1;
				} else
					$spoiler = 0;
				$com = auto_link( $com, $resno );
				if ( !$resno )
					list( $com, $abbreviated ) = abbreviate( $com, MAX_LINES_SHOWN );
				
				if ( isset( $abbreviated ) && $abbreviated )
					$com .= "<br /><span class=\"abbr\">Comment too long. Click <a href=\"" . RES_DIR . ( $resto ? $resto : $no ) . PHP_EXT . "#$no\">here</a> to view the full text.</span>";
				
//Replies creation      
// Picture file name
								$r_img        = $path . $tim . $ext;
								$r_displaysrc = DATA_SERVER . BOARD_DIR . "/" . $imgdir . $tim . $ext;
								$r_linksrc    = ((USE_SRC_CGI == 1) ? (str_replace(".cgi", "", $imgdir) . $tim . $ext) : $r_displaysrc);
								if (defined('INTERSTITIAL_LINK'))
										$r_linksrc = str_replace(INTERSTITIAL_LINK, "", $r_linksrc);
								$r_src    = DATA_SERVER . BOARD_DIR . "/" . IMG_DIR . $tim . $ext;
								$longname = $fname . $ext;
								if (strlen($fname) > 30) {
										$shortname = substr($fname, 0, 30) . "(...)" . $ext;
								} else {
										$shortname = $longname;
								}
								// img tag creation
								$r_imgsrc = "";
								if ($ext) {
										// turn the 32-byte ascii md5 into a 24-byte base64 md5
										$shortmd5 = base64_encode(pack("H*", $md5));
										if ($fsize >= 1048576) {
												$size = round(($fsize / 1048576), 2) . " M";
										} else if ($fsize >= 1024) {
												$size = round($fsize / 1024) . " K";
										} else {
												$size = $fsize . " ";
										}
										if (!$tn_w && !$tn_h && $ext == ".gif") {
												$tn_w = $w;
												$tn_h = $h;
										}
										if ($spoiler) {
												$size     = "Spoiler Image, $size";
												$r_imgsrc = "<br><a href=\"" . $r_displaysrc . "\" target=_blank><img src=\"" . SPOILER_THUMB . "\" border=0 align=left hspace=20 alt=\"" . $size . "B\" md5=\"$shortmd5\"></a>";
										} elseif ($tn_w && $tn_h) { //when there is size...
												if (@is_file(THUMB_DIR . $tim . 's.jpg')) {
														$r_imgsrc = "<br><a href=\"" . $r_displaysrc . "\" target=_blank><img class='postimg'  src=" . $thumbdir . $tim . 's.jpg' . " border=0 align=left width=$tn_w height=$tn_h hspace=20 alt=\"" . $size . "B\" md5=\"$shortmd5\"></a>";
												} else {
														$r_imgsrc = "<a href=\"" . $r_displaysrc . "\" target=_blank><span class=\"tn_reply\" title=\"" . $size . "B\">Thumbnail unavailable</span></a>";
												}
										} else {
												if (@is_file(THUMB_DIR . $tim . 's.jpg')) {
														$r_imgsrc = "<br><a href=\"" . $r_displaysrc . "\" target=_blank><img class='postimg'  src=" . $thumbdir . $tim . 's.jpg' . " border=0 align=left hspace=20 alt=\"" . $size . "B\" md5=\"$shortmd5\"></a>";
												} else {
														$r_imgsrc = "<a href=\"" . $r_displaysrc . "\" target=_blank><span class=\"tn_reply\" title=\"" . $size . "B\">Thumbnail unavailable</span></a>";
												}
										}
										if (!is_file($src)) {
												$r_imgreply = '<br><img src="' . $cssimg . 'filedeleted-res.gif" alt="File deleted.">';
										} else {
												$dimensions = ($ext == '.pdf') ? 'PDF' : "{$w}x{$h}";
												if ($resno) {
														$r_imgreply = "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"filesize\">" . S_PICNAME . "<a href=\"$r_linksrc\" target=\"_blank\">$time$ext</a>-(" . $size . "B, " . $dimensions . ", <span title=\"" . $longname . "\">" . $shortname . "</span>)</span>" . $r_imgsrc;
												} else {
														$r_imgreply = "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"filesize\">" . S_PICNAME . "<a href=\"$r_linksrc\" target=\"_blank\">$time$ext</a>-(" . $size . "B, " . $dimensions . ")</span>" . $r_imgsrc;
												}
										}
								}
			
            // Main Reply creation
			$dat .= "<a name=\"$no\"></a>\n";
			$dat .= "<table><tr><td nowrap class=\"doubledash\">&gt;&gt;</td><td id=\"$no\" class=\"reply\">\n";
				//      if (($t>3)&&($fsize!=0)) {
				//      $dat.="&nbsp;&nbsp;&nbsp;<b>Image hidden</b>&nbsp;&nbsp; $now No.$no \n";
				//			} else {
			$dat .= "<input type=checkbox name=\"$no\" value=delete><span class=\"replytitle\">$sub</span> \n";
			$dat .= "<span class=\"commentpostername\">$name</span> $now <span id=\"norep$no\">";
			if ($resno) {
				$dat .= "<a href=\"#$no\" class=\"quotejs\">No.</a><a href=\"javascript:quote('$no')\" class=\"quotejs\">$no</a></span>";
			} else {
				$dat .= "<a href=\"" . RES_DIR . $resto . PHP_EXT . "#$no\" class=\"quotejs\">No.</a><a href=\"" . RES_DIR . $resto . PHP_EXT . "#q$no\" class=\"quotejs\">$no</a></span>";
			}
            
            if (isset($r_imgreply))
				$dat .= $r_imgreply;
				$dat .= "<blockquote>$com</blockquote>";
                $dat .= "</td></tr></table>\n";
				unset($r_imgreply);
			}
			
			/*possibility for ads after each post*/
			$dat .= "</span><br clear=\"left\" /><hr />\n";
			
			if ( USE_ADS3 ) 
				$dat .= '' . ADS3 . '<hr />';
            
			if ( $resno ) 
				$dat .= "[<a href=\"" . PHP_SELF2_ABS . "\">" . S_RETURN . "</a>] [<a href=\""  . $resto . PHP_EXT . "#top\"/>Top</a>]\n";
            
			clearstatcache(); //clear stat cache of a file
			//mysql_free_result( $resline );
			$p++;
			if ( $resno ) {
				break;
			} //only one tree line at time of res
		}
		
		
		
		
		$dat .= '<table align="right"><tr><td nowrap="nowrap" align="center">
<input type="hidden" name="mode" value="usrdel" />' . S_REPDEL . '[<input type="checkbox" name="onlyimgdel" value="on" />' . S_DELPICONLY . ']<br />
' . S_DELKEY . '<input type="password" name="pwd" size="8" maxlength="8" value="" />
<input type="submit" value="' . S_DELETE . '" /><input type="button" value="Report" onclick="var o=document.getElementsByTagName(\'INPUT\');for(var i=0;i<o.length;i++)if(o[i].type==\'checkbox\' && o[i].checked && o[i].value==\'delete\') return reppop(\'' . PHP_SELF_ABS . '?mode=report&no=\'+o[i].name+\'\');"></tr></td></form><script>document.delform.pwd.value=l('. SITE_ROOT . '_pass");</script></td></tr></table>';
/*<script language="JavaScript" type="script"><!--
l();
//--></script>';*/
		
		if ( !$resno ) { // if not in reply to mode
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
			$logfilename = RES_DIR . $resno . PHP_EXT;
			print_page( $logfilename, $dat );
			$dat = '';
			if ( !$rebuild )
				$deferred = updatelog( 0 );
			break;
		}
		if ( $page == 0 ) {
			$logfilename = PHP_SELF2;
		} else {
			$logfilename = $page / PAGE_DEF . PHP_EXT;
		}
		print_page( $logfilename, $dat );
		//chmod($logfilename,0666);
	}
	//mysql_free_result($treeline);
	if ( isset( $deferred ) )
		return $deferred;
	return false;
}


function mysql_call( $query )
{
	$ret = mysql_query( $query );
	if ( !$ret ) {
	//	if ( DEBUG_MODE ) {
			echo "Error on query: " . $query . "<br />";
			echo mysql_error() . "<br />";
	//	} else {
		//	echo "MySQL error!<br />";
		}
	//}
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
	} elseif ( SHOWTITLETXT == 2) {
        	$titlepart .= '/' . BOARD_DIR . '/ - ' . TITLE . '';
    	}
	/* begin page content */
	$dat .= '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="jp"><head>
<meta name="description" content="' . S_DESCR . '"/>
<meta http-equiv="content-type"  content="text/html;charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- meta HTTP-EQUIV="pragma" CONTENT="no-cache" -->
<link REL="SHORTCUT ICON" HREF="/favicon.ico">
<link rel="stylesheet" type="text/css" href="' . CSS_PATH . CSS1 . '" title="Standard Saguaro" />
<link rel="alternate stylesheet" type="text/css" media="screen" title="' . CSS2 . '" href="'  . CSS_PATH . CSS2 . '" />
<link rel="alternate stylesheet" type="text/css" media="screen" title="' . CSS3 . '" href="' .CSS_PATH . CSS3 . '" />
<link rel="alternate stylesheet" type="text/css" media="screen" title="' . CSS4 . '" href="' . CSS_PATH . CSS4 . '" />
<script src="' . JS_PATH . '/styleswitch.js" type="text/javascript">
/***********************************************
* Style Sheet Switcher v1.1- c Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for this script and 100s more
***********************************************/
</script>
<title>' . TITLE . '</title>
<script src="' . JS_PATH . 'main.js" type="text/javascript"></script>
<title>' . TITLE . '</title>';
	
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
<span class="boardlist">' . file_get_contents(BOARDLIST) . ' </span>
<span class="adminbar">
[<a href="' . HOME . '" target="_top">' . S_HOME . '</a>]
[<a href="' .  PHP_SELF_ABS . '?mode=admin">' . S_ADMIN . '</a>]
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
<a href="#top" /></a>
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
		$msg .= "<div class=\"theader\">" . S_POSTING . "</div>\n";
	}
	if ( $admin ) {
		$hidden = "<input type=hidden name=admin value=\"" . PANEL_PASS . "\">";
		$msg    = "<em>" . S_NOTAGS . "</em>";
	}
	$dat .= $msg . '<div align="center"><div class="postarea">';
	/*if ($mobileClient) {
		$dat .= '<form id="contribform" style="display:none;" action="' . PHP_SELF_ABS . '" method="post" name="contrib" enctype="multipart/form-data">';
	} else {*/
		$dat .= '<form id="contribform" action="' . PHP_SELF_ABS . '" method="post" name="contrib" enctype="multipart/form-data">';
	//}
	$dat .= '<input type="hidden" name="mode" value="regist" />
' . $hidden . '
<input type="hidden" name="MAX_FILE_SIZE" value="' . $maxbyte . '" />';
	if ( $no ) {
		$dat .= '<input type="hidden" name="resto" value="' . $no . '" />';
	}

		$dat .= '<table>'; 
        if (!FORCED_ANON) 
            $dat .= '<tr><td class="postblock" align="left">' . S_NAME . '</td><td align="left"><input type="text" name="name" size="28" /></td></tr>';
        
        if (!$resno) {
            $dat .=  '<tr><td class="postblock" align="left">' . S_EMAIL . '</td><td align="left"><input type="text" name="email" size="28" /></td></tr>
                        <tr><td class="postblock" align="left">' . S_SUBJECT . '</td><td align="left"><input type="text" name="sub" size="35" /><input type="submit" value="' . S_SUBMIT . '" /></td></tr>';
        } else {
            $dat .=  '<tr><td class="postblock" align="left">' . S_EMAIL . '</td><td align="left"><input type="text" name="email" size="28" /><input type="submit" value="' . S_SUBMIT . '" /></td></tr>';
        }
        
        $dat .=  '<tr><td class="postblock" align="left">' . S_COMMENT . '</td><td align="left"><textarea name="com" cols="48" rows="4"></textarea></td></tr>';

        if(BOTCHECK ) {
            if (!$admin)
                $dat .= '<tr><td class="postblock" align="left"><img src="' . PLUG_PATH . '/php_captcha.php" /></td><td align="left"><input type="text" name="num" size="28" /></td></tr>';
        }

        $dat .= '<tr><td class="postblock" align="left">' . S_UPLOADFILE . '</td><td><input type="file" name="upfile" accept="image/*" size="35" />';
        
        if ( NOPICBOX && !SPOILERS ) {
            $dat .= '[<label><input type="checkbox" name="textonly" value="on" />' . S_NOFILE . '</label>]</td></tr>';
        } 
        
        if (SPOILERS) {
            $dat .= '[<label><input type=checkbox name=spoiler value=on>' . S_SPOILERS . '</label>]</td></tr>';
        } else {
            $dat .= '</td></tr>';
        }


	if ( $admin ) {
		$dat .= '<tr><td align="left" class="postblock" align="left">
            Options</td><td align="left">
            Sticky: <input type="checkbox" name="isSticky" value="isSticky" />
            Lock:<input type="checkbox" name="isLocked" value="isLocked" />
            Capcode:<input type="checkbox" name="showCap" value="showCap" />
            <tr><td class="postblock" align="left">' . S_RESNUM . '</td><td align="left"><input type="text" name="resto" size="28" /></td></tr>';
    }
    
	$dat .= '<tr><td align="left" class="postblock" align="left">' . S_DELPASS . '</td><td align="left"><input type="password" name="pwd" size="8" maxlength="8" value="" />' . S_DELEXPL . '</td></tr>';
    
    if (!$admin) 
        $dat .= '<tr><td colspan="2"><div align="left" class="rules">' . S_RULES . '</div></td></tr></table></form></div></div><hr />';
    else 
        $dat .= '</table></form></div></div>';
    
    if (!$resno && !$admin)
        $news = file_get_contents(GLOBAL_NEWS);
        if ($news != '')
            $dat .= "<div class=\"globalnews\"/>" . file_get_contents(GLOBAL_NEWS) . "</div><br /><hr />";
    
    //Top thread navigation bar
    if ( $resno ) {
		$dat .= "<div class=\"threadnav\" /> [<a href=\"" . PHP_SELF2_ABS . "\">" . S_RETURN . "</a>] [<a href=\""  . $no . PHP_EXT . "#bottom\"/>Bottom</a>] </div> \n<hr />";
	}
    
	if ( USE_ADS2 ) {
		$dat .= '' . ADS2 . '<hr />';
	}
}


/* Footer */
function foot( &$dat )
{
	$dat .= '
<span class="boardlist">' . file_get_contents(BOARDLIST) . '</span>
<div class="footer">' . S_FOOT . '</div>
<a href="#bottom" /></a>
 
</body></html>';
}


function error( $mes, $dest = '' )
{
	global $upfile_name, $path;
	if ( is_file( $dest ) )
		unlink( $dest );
	head( $dat );
	echo $dat;
	if ( $mes == S_BADHOST ) {
		die( "<html><head><meta http-equiv=\"refresh\" content=\"0; url=banned.php\"></head></html>" );
	} else {
		echo "<br /><br /><hr size=1><br /><br />
		   <center><font color=blue size=5>$mes<br /><br /><a href=" . PHP_SELF2_ABS . ">" . S_RELOAD . "</a></b></font></center>
		   <br /><br /><hr size=1>";
		die( "</body></html>" );
	}
}
/* Auto Linker */
function normalize_link_cb($m)
{
		$subdomain = $m[1];
		$original  = $m[0];
		$board     = strtolower($m[2]);
		$m[0]      = $m[1] = $m[2] = '';
		for ($i = count($m) - 1; $i > 2; $i--) {
				if ($m[$i]) {
						$no = $m[$i];
						break;
				}
		}
		if ($subdomain == 'www' || $subdomain == 'static' || $subdomain == 'content')
				return $original;
		if ($board == BOARD_DIR)
				return "&gt;&gt;$no";
		else
				return "&gt;&gt;&gt;/$board/$no";
}
function normalize_links($proto)
{
		// change http://xxx.[[site]/board/res/no links into plaintext >># or >>>/board/#
		if (strpos($proto, SITE_ROOT) === FALSE)
				return $proto;
		
		$proto = preg_replace_callback('@http://([A-za-z]*)[.]' . SITE_ROOT. '[.]' . SITE_SUFFIX . '/(\w+)/(?:res/(\d+)[.]html(?:#q?(\d+))?|\w+.php[?]res=(\d+)(?:#(\d+))?|)(?=[\s.<!?,]|$)@i', 'normalize_link_cb', $proto);
		// rs.[site].info to >>>rs/query+string
		$proto = preg_replace('@http://rs[.]' . SITE_ROOT. '[.]' . SITE_SUFFIX . '/\?s=([a-zA-Z0-9$_.+-]+)@i', '&gt;&gt;&gt;/rs/$1', $proto);
		return $proto;
}

function intraboard_link_cb($m)
{
		global $intraboard_cb_resno, $log;
		$no    = $m[1];
		$resno = $intraboard_cb_resno;
		if (isset($log[$no])) {
				$resto  = $log[$no]['resto'];
				$resdir = ($resno ? '' : RES_DIR);
				$ext    = PHP_EXT;
				if ($resno && $resno == $resto) // linking to a reply in the same thread
						return "<a href=\"#$no\" class=\"quotelink\" onClick=\"replyhl('$no');\">&gt;&gt;$no</a>";
				elseif ($resto == 0) // linking to a thread
						return "<a href=\"$resdir$no$ext#$no\" class=\"quotelink\">&gt;&gt;$no</a>";
				else // linking to a reply in another thread
						return "<a href=\"$resdir$resto$ext#$no\" class=\"quotelink\">&gt;&gt;$no</a>";
		}
		return $m[0];
}
function intraboard_links($proto, $resno)
{
		global $intraboard_cb_resno;
		
		$intraboard_cb_resno = $resno;
		
		$proto = preg_replace_callback('/&gt;&gt;([0-9]+)/', 'intraboard_link_cb', $proto);
		return $proto;
}

function interboard_link_cb($m)
{
		// on one hand, we can link to imgboard.php, using any old subdomain, 
		// and let apache & imgboard.php handle it when they click on the link
		// on the other hand, we can use the database to fetch the proper subdomain
		// and even the resto to construct a proper link to the html file (and whether it exists or not)
		
		// for now, we'll assume there's more interboard links posted than interboard links visited.
		$url = DATA_SERVER . $m[1] . '/' . PHP_SELF . ($m[2] ? ('?res=' . $m[2]) : "");
		return "<a href=\"$url\" class=\"quotelink\">{$m[0]}</a>";
}
function interboard_rs_link_cb($m)
{
		// $m[1] might be a url-encoded query string, or might be manual-typed text
		// so we'll normalize it to raw text first and then re-encode it
		$lsearchquery = urlencode(urldecode($m[1]));
		return "<a href=\"http://rs." . SITE_ROOT . "./?s=$lsearchquery\" class=\"quotelink\">{$m[0]}</a>";
}

function interboard_links($proto)
{
		$boards    = "an?|cm?|fa|fit|gif|h[cr]?|[bdefgkmnoprstuvxy]|wg?|ic?|y|cgl|c[ko]|mu|po|t[gv]|toy|test2|trv|jp|r9k|sp";
		$proto     = preg_replace_callback('@&gt;&gt;&gt;/(' . $boards . ')/([0-9]*)@i', 'interboard_link_cb', $proto);
		$proto     = preg_replace_callback('@&gt;&gt;&gt;/rs/([^\s<>]+)@', 'interboard_rs_link_cb', $proto);
		return $proto;
}

function auto_link($proto, $resno)
{
		$proto = normalize_links($proto);
		
		// auto-link remaining URLs if they're not part of HTML
		if (strpos($proto, SITE_ROOT) !== FALSE) {
				$proto = preg_replace('/(http:\/\/(?:[A-Za-z]*\.)?)(' . SITE_ROOT . ')(\'' . SITE_SUFFIX . ')(\/)([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%&\/~\+#])?/i', "<a href=\"\\0\" target=\"_blank\">\\0</a>", $proto);
				$proto = preg_replace('/([<][^>]*?)<a href="((http:\/\/(?:[A-Za-z]*\.)?)(' . SITE_ROOT . ')(\'' . SITE_SUFFIX .')(\/)([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%&\/~\+#])?)" target="_blank">\\2<\/a>([^<]*?[>])/i', '\\1\\3\\4\\5\\6\\7\\8', $proto);
		}
		
		$proto = intraboard_links($proto, $resno);
		$proto = interboard_links($proto);
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
	
	if ( $pwd == PANEL_PASS )
		$admin = $pwd;
	if ( $admin != PANEL_PASS || !valid() )
		$admin = '';
	$mes = "";
	
	if (valid('moderator')) {
		$moderator = 1;
		if (valid('admin'))
			$moderator = 2;
        if (valid('manager'))
            $moderator = 3;
	}
    
    if ( SPOILERS == 1 ) {
		$com = spoiler_parse( $com );
	}
	
    if ( isset($_POST['isSticky']) || isset($_POST['isLocked']) && valid('moderator') )  {
        if (isset($_POST['isSticky']))
            $stickied = 1;
        if (isset($_POST['isLocked']))
            $locked = 1;
    }
    
	if ( !$upfile && !$resto ) { // allow textonly threads for moderators!
		if ( valid( 'textonly' ) )
			$textonly = 1;
	}
		
		// time
		$time = time();
		$tim  = $time . substr( microtime(), 2, 3 );
		
		// check closed
		$resto = (int) $resto;
		if ( $resto ) {
			if ( !$cchk = mysql_call( "select locked from " . SQLLOG . " where no=" . $resto ) ) {
				echo S_SQLFAIL;
			}
			list( $locked ) = mysql_fetch_row( $cchk );
			if ( $locked == 1 && !$admin )
				error( "You can't reply to this thread anymore.", $upfile );
			mysql_free_result( $cchk );
		}
		
		// upload processing
		
		$has_image = $upfile && file_exists( $upfile );
		
if ( $has_image ) {
		// check image limit
		if ( $resto ) {
			if ( !$result = mysql_call( "select COUNT(*) from " . SQLLOG . " where resto=$resto and fsize!=0" ) ) {
				echo S_SQLFAIL;
			}
			$countimgres = mysql_result( $result, 0, 0 );
			if ( $countimgres > MAX_IMGRES )
				error( "Max limit of " . MAX_IMGRES . " image replies has been reached.", $upfile );
			mysql_free_result( $result );
		}
		
		//upload processing
		$dest = tempnam( substr( $path, 0, -1 ), "img" );
		//$dest = $path.$tim.'.tmp';
		if ( OEKAKI_BOARD == 1 && $_POST['oe_chk'] ) {
			rename( $upfile, $dest );
			chmod( $dest, 0644 );
			if ( $pchfile )
				rename( $pchfile, "$dest.pch" );
		} else
			move_uploaded_file( $upfile, $dest );
		
		clearstatcache(); // otherwise $dest looks like 0 bytes!
		
		$upfile_name = CleanStr( $upfile_name );
		$fsize       = filesize( $dest );
		if ( !is_file( $dest ) )
			error( S_UPFAIL, $dest );
		if ( !$fsize || $fsize > MAX_KB * 1024 )
			error( S_TOOBIG, $dest );
		
		// PDF processing
		if ( ENABLE_PDF == 1 && strcasecmp( '.pdf', substr( $upfile_name, -4 ) ) == 0 ) {
			$ext = '.pdf';
			$W   = $H = 1;
			$md5 = md5_of_file( $dest );
			// run through ghostscript to check for validity
			if ( pclose( popen( "/usr/local/bin/gs -q -dSAFER -dNOPAUSE -dBATCH -sDEVICE=nullpage $dest", 'w' ) ) ) {
				error( S_UPFAIL, $dest );
			}
		} else {
			$size = getimagesize( $dest );
			if ( !is_array( $size ) )
				error( S_NOREC, $dest );
			$md5 = md5_of_file( $dest );
			
			//chmod($dest,0666);
			$W = $size[0];
			$H = $size[1];
			switch ( $size[2] ) {
				case 1:
					$ext = ".gif";
					break;
				case 2:
					$ext = ".jpg";
					break;
				case 3:
					$ext = ".png";
					break;
				case 4:
					$ext = ".swf";
					error( S_UPFAIL, $dest );
					break;
				case 5:
					$ext = ".psd";
					error( S_UPFAIL, $dest );
					break;
				case 6:
					$ext = ".bmp";
					error( S_UPFAIL, $dest );
					break;
				case 7:
					$ext = ".tiff";
					error( S_UPFAIL, $dest );
					break;
				case 8:
					$ext = ".tiff";
					error( S_UPFAIL, $dest );
					break;
				case 9:
					$ext = ".jpc";
					error( S_UPFAIL, $dest );
					break;
				case 10:
					$ext = ".jp2";
					error( S_UPFAIL, $dest );
					break;
				case 11:
					$ext = ".jpx";
					error( S_UPFAIL, $dest );
					break;
				case 13:
					$ext = ".swf";
					error( S_UPFAIL, $dest );
					break;
				default:
					$ext = ".xxx";
					error( S_UPFAIL, $dest );
					break;
			}
			if ( GIF_ONLY == 1 && $size[2] != 1 )
				error( S_UPFAIL, $dest );
		} // end processing -else
		
		// Picture reduction
		if ( !$resto ) {
			$maxw = MAX_W;
			$maxh = MAX_H;
		} else {
			$maxw = MAXR_W;
			$maxh = MAXR_H;
		}
		if ( defined( 'MIN_W' ) && MIN_W > $W )
			error( S_UPFAIL, $dest );
		if ( defined( 'MIN_H' ) && MIN_H > $H )
			error( S_UPFAIL, $dest );
		if ( defined( 'MAX_DIMENSION' ) ) {
			$maxdimension = MAX_DIMENSION;
		} else {
			$maxdimension = 5000;
        }
		if ( $W > $maxdimension || $H > $maxdimension ) {
			error( S_TOOBIGRES, $dest );
		} elseif ( $W > $maxw || $H > $maxh ) {
			$W2 = $maxw / $W;
			$H2 = $maxh / $H;
			( $W2 < $H2 ) ? $key = $W2 : $key = $H2;
			$TN_W = ceil( $W * $key );
			$TN_H = ceil( $H * $key );
		}
		$mes = $upfile_name . ' ' . S_UPGOOD;
	}
	
		if ( $_FILES["upfile"]["error"] > 0 ) {
			if ( $_FILES["upfile"]["error"] == UPLOAD_ERR_INI_SIZE )
				error( S_TOOBIG, $dest );
			if ( $_FILES["upfile"]["error"] == UPLOAD_ERR_FORM_SIZE )
				error( S_TOOBIG, $dest );
			if ( $_FILES["upfile"]["error"] == UPLOAD_ERR_PARTIAL )
				error( S_UPFAIL, $dest );
			if ( $_FILES["upfile"]["error"] == UPLOAD_ERR_CANT_WRITE )
				error( S_UPFAIL, $dest );
		}
		
		if ( $upfile_name && $_FILES["upfile"]["size"] == 0 ) {
			error( S_TOOBIGORNONE, $dest );
		}
	
	//The last result number
	$lastno = mysql_result( mysql_call( "select max(no) from " . SQLLOG ), 0, 0 );
	
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
	
	/*	foreach ( $badstring as $value ) {
	if ( ereg( $value, $com ) || ereg( $value, $sub ) || ereg( $value, $name ) || ereg( $value, $email ) ) {
	error( S_STRREF, $dest );
	}
	;
	}*/
	if ( $_SERVER["REQUEST_METHOD"] != "POST" )
		error( S_UNJUST, $dest );
	// Form content check
	if ( !$name || ereg( "^[ |&#12288;|]*$", $name ) )
		$name = "";
	if ( !$com || ereg( "^[ |&#12288;|\t]*$", $com ) )
		$com = "";
	if ( !$sub || ereg( "^[ |&#12288;|]*$", $sub ) )
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
	$host  = $_SERVER["REMOTE_ADDR"];
	$badip = mysql_call( "SELECT ip FROM " . SQLBANLOG . " WHERE ip = '$host' and banlength <> 0 " );
	
    if ($moderator) 
        $host = '###.###.###.###'; // Don't store mod/admin ips
    
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
	$yd     = $youbi[date( "w", $time )];
	if ( SHOW_SECONDS == 1 ) {
		$now = date( "m/d/y", $time ) . "(" . (string) $yd . ")" . date( "H:i:s", $time );
	} else {
		$now = date( "m/d/y", $time ) . "(" . (string) $yd . ")" . date( "H:i", $time );
	}
	
    if ( DISP_ID ) {
        //$rand = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
        //$color = '#'.$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)];
		$color = "inherit"; // Until unique IDs between threads get sorted out
        $idhtml = "<span id=\"posterid\" style=\"background-color:" . $color . "; border-radius:10px;font-size:8pt;\" />";
        mysql_real_escape_string($idhtml);
        
        if ( $email && DISP_ID == 1 ) {
			$now .= " (ID:" .$idhtml . " Heaven </span>)" ;
		} else {
           /* if ( !$resto) {
                //holy hell there has to be a better way to do this. i swear ill think of it soon
                $idsalt = mysql_result( mysql_call( "select max(no) from " . SQLLOG ), 0, 0 ) + 1; //In the year 2054 A.D., your op number is known before you even post it
            } else {
                $idsalt = $resto;
            }*/
            $idsalt = 'id';
			$now .= " (ID:" . $idhtml . substr( crypt( md5( $_SERVER["REMOTE_ADDR"] . 'id' . date( "Ymd", $time ) ), $idsalt ), +3 ) . "</span>)";
		}
	}
    
    if (COUNTRY_FLAGS) {
		include("geoiploc.php");
		$country = getCountryFromIP($host, "CTRY");
		$now .= " <img src=" . CSS_PATH . "flags/" . strtolower($country) . ".png /> ";
	}
    
	$c_name  = $name;
	$c_email = $email;
	
	//Text plastic surgery (rorororor)
	$email = CleanStr( $email );
	$email = ereg_replace( "[\r\n]", "", $email );
	$sub   = CleanStr( $sub );
	$sub   = ereg_replace( "[\r\n]", "", $sub );
	$url   = CleanStr( $url );
	$url   = ereg_replace( "[\r\n]", "", $url );
	$resto = CleanStr( $resto );
	$resto = ereg_replace( "[\r\n]", "", $resto );
	$com   = CleanStr( $com, 1 );
	
	if ( SPOILERS == 1 && $spoiler ) {
		$sub = "SPOILER<>$sub";
	}
	// Standardize new character lines
	$com = str_replace( "\r\n", "\n", $com );
	$com = str_replace( "\r", "\n", $com );
	//$com = preg_replace("/\A([0-9A-Za-z]{10})+\Z/", "!s8AAL8z!", $com);
	// Continuous lines
	$com = ereg_replace( "\n((&#12288;| )*\n){3,}", "\n", $com );
	
	if ( !$admin && substr_count( $com, "\n" ) > MAX_LINES )
		error( "Error: Too many lines.", $dest );
	
	$com = nl2br( $com ); //br is substituted before newline char
	
	$com = str_replace( "\n", "", $com ); //\n is erased
	// Continuous lines
	$com = ereg_replace( "\n((&#12288;| )*\n){3,}", "\n", $com );
	
	if ( !$admin && substr_count( $com, "\n" ) > MAX_LINES )
		error( "Error: Too many lines.", $dest );
	
	$name  = ereg_replace( "[\r\n]", "", $name );
	$names = iconv( "UTF-8", "CP932//IGNORE", $name ); // convert to Windows Japanese #&#65355;&#65345;&#65357;&#65353;
	
	//start new tripcode crap
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
			if ( FORTUNE_TRIP == 1 && $trip == "fortune" ) {
				$fortunes   = array(
					 "Bad Luck",
					"Average Luck",
					"Good Luck",
					"Excellent Luck",
					"Reply hazy, try again",
					"Godly Luck",
					"Very Bad Luck",
					"Outlook good",
					"Better not tell you now",
					"You will meet a dark handsome stranger",
					"&#65399;&#65408;&#9473;&#9473;&#9473;&#9473;&#9473;&#9473;(&#65439;&#8704;&#65439;)&#9473;&#9473;&#9473;&#9473;&#9473;&#9473; !!!!",
					"&#65288;&#12288;´_&#12445;`&#65289;&#65420;&#65392;&#65437; ",
					"Good news will come to you by mail",
					"Hope you're insured",
					"Great things await",
					"Don't leave the house today." 
				);
				$fortunenum = rand( 0, sizeof( $fortunes ) - 1 );
				$fortcol    = "#" . sprintf( "%02x%02x%02x", 127 + 127 * sin( 2 * M_PI * $fortunenum / sizeof( $fortunes ) ), 127 + 127 * sin( 2 * M_PI * $fortunenum / sizeof( $fortunes ) + 2 / 3 * M_PI ), 127 + 127 * sin( 2 * M_PI * $fortunenum / sizeof( $fortunes ) + 4 / 3 * M_PI ) );
				$com        = "<font color=$fortcol><b>Your fortune: " . $fortunes[$fortunenum] . "</b></font><br /><br />" . $com;
				$trip       = "";
				if ( $sectrip == "" ) {
					if ( $name == "</span>" && $sectrip == "" )
						$name = S_ANONAME;
					else
						$name = str_replace( "</span>", "", $name );
				}
			} else if ( $trip == "fortune" ) {
				//remove fortune even if FORTUNE_TRIP is off
				$trip = "";
				if ( $sectrip == "" ) {
					if ( $name == "</span>" && $sectrip == "" )
						$name = S_ANONAME;
					else
						$name = str_replace( "</span>", "", $name );
				}
				
			} else {
				
				$salt = strtr( preg_replace( "/[^\.-z]/", ".", substr( $trip . "H.", 1, 2 ) ), ":;<=>?@[\\]^_`", "ABCDEFGabcdef" );
				$trip = substr( crypt( $trip, $salt ), -10 );
				$name .= " <span class=\"postertrip\">!" . $trip;
			}
		}
		
		
		if ( $sectrip != "" ) {
			$salt = "LOLLOLOLOLOLOLOLOLOLOLOLOLOLOLOL"; #this is ONLY used if the host doesn't have openssl
			#I don't know a better way to get random data
			if ( file_exists( SALTFILE ) ) { #already generated a key
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
				$name .= " <span class=\"postertrip\" text-color=#117743>";
			$name .= "!!" . $sha;
		}
	}
	
	if ( $email == 'noko' ) {
		$noko  = 1;
		$email = '';
	} else if ( $email == 'nokosage' ) {
		$noko  = 1;
		$email = 'sage';
	}
	
	if ($moderator) {
		if ($moderator == 1 && isset($_POST['showCap']))
			$name = '<b><font color="770099">Anonymous ## Mod </font></b>';
		if ($moderator == 2 && isset($_POST['showCap']))
			$name = '<b><font color="FF101A">Anonymous ## Admin  </font></b>';
		if ($moderator == 3 && isset($_POST['showCap']))
			$name = '<b><font color="2E2EFE">Anonymous ## Manager  </font></b>';
	}
			
	
	if ( !$name )
		$name = S_ANONAME;
	if ( !$com )
		$com = S_ANOTEXT;
	if ( !$sub )
		$sub = S_ANOTITLE;
	
	if ( FORCED_ANON == 1 ) {
		$name = "</span>$now<span>";
		$sub  = '';
		$now  = '';
	}
	$com = wordwrap2( $com, 100, "<br />" );
	$com = preg_replace( "!(^|>)(&gt;[^<]*)!", "\\1<font class=\"unkfunc\">\\2</font>", $com );
	
	$is_sage = stripos( $email, "sage" ) !== FALSE;
	
	
	$may_flood = valid( 'floodbypass' );
	
	if ( !$may_flood ) {
		if ( $com ) {
			// Check for duplicate comments
			$query  = "select count(no)>0 from " . SQLLOG . " where com='" . mysql_real_escape_string( $com ) . "' " . "and host='" . mysql_real_escape_string( $host ) . "' " . "and time>" . ( $time - RENZOKU_DUPE );
			$result = mysql_call( $query );
			if ( mysql_result( $result, 0, 0 ) )
				error( S_RENZOKU, $dest );
			mysql_free_result( $result );
		}
		
		if ( !$has_image ) {
			// Check for flood limit on replies
			$query  = "select count(no)>0 from " . SQLLOG . " where time>" . ( $time - RENZOKU ) . " " . "and host='" . mysql_real_escape_string( $host ) . "' and resto>0";
			$result = mysql_call( $query );
			if ( mysql_result( $result, 0, 0 ) )
				error( S_RENZOKU, $dest );
			mysql_free_result( $result );
		}
		
		if ( $is_sage ) {
			// Check flood limit on sage posts
			$query  = "select count(no)>0 from " . SQLLOG . " where time>" . ( $time - RENZOKU_SAGE ) . " " . "and host='" . mysql_real_escape_string( $host ) . "' and resto>0 and permasage=1";
			$result = mysql_call( $query );
			if ( mysql_result( $result, 0, 0 ) )
				error( S_RENZOKU, $dest );
			mysql_free_result( $result );
		}
		
		if ( !$resto ) {
			// Check flood limit on new threads
			$query  = "select count(no)>0 from " . SQLLOG . " where time>" . ( $time - RENZOKU3 ) . " " . "and host='" . mysql_real_escape_string( $host ) . "' and root>0"; //root>0 == non-sticky
			$result = mysql_call( $query );
			if ( mysql_result( $result, 0, 0 ) )
				error( S_RENZOKU3, $dest );
			mysql_free_result( $result );
		}
	}
	
	// Upload processing
	if ( $has_image ) {
		if ( !$may_flood ) {
			$query  = "select count(no)>0 from " . SQLLOG . " where time>" . ( $time - RENZOKU2 ) . " " . "and host='" . mysql_real_escape_string( $host ) . "' and resto>0";
			$result = mysql_call( $query );
			if ( mysql_result( $result, 0, 0 ) )
				error( S_RENZOKU2, $dest );
			mysql_free_result( $result );
		}
		
		//Duplicate image check
		if ( DUPE_CHECK ) {
			$result = mysql_call( "select no,resto from " . SQLLOG . " where md5='$md5'" );
			if ( mysql_num_rows( $result ) ) {
				list( $dupeno, $duperesto ) = mysql_fetch_row( $result );
				if ( !$duperesto )
					$duperesto = $dupeno;
				error( '<a href="' . DATA_SERVER . BOARD_DIR . "/res/" . $duperesto . PHP_EXT . '#' . $dupeno . '">' . S_DUPE . '</a>', $dest );
			}
			mysql_free_result( $result );
		}
	}
	
	$rootqu = $resto ? "0" : "now()";
	if ($stickied)
        $rootqu = '20270727070707';
	//Bump processing
	if ( $resto ) { //sage or age action
		$resline  = mysql_call( "select count(no) from " . SQLLOG . " where resto=" . $resto );
		$countres = mysql_result( $resline, 0, 0 );
		mysql_free_result( $resline );
		$resline = mysql_call( "select sticky,permasage from " . SQLLOG . " where no=" . $resto );
		list( $sticky, $permasage ) = mysql_fetch_row( $resline );
		mysql_free_result( $resline );
		if ( ( stripos( $email, 'sage' ) === FALSE && $countres < MAX_RES && $sticky != "1" && $permasage != "1" ) || ( $admin && $age && $sticky != "1" ) ) {
			$query = "update " . SQLLOG . " set root=now() where no=$resto"; //age
			mysql_call( $query );
		}
	}
    
	//Main insert
	$query = "insert into " . SQLLOG . " (now,name,email,sub,com,host,pwd,ext,w,h,tn_w,tn_h,tim,time,md5,fsize,fname,sticky,permasage,locked,root,resto) values (" . "'" . $now . "',"
    . "'" . mysql_real_escape_string( $name ) . 
    "'," . "'" . mysql_real_escape_string( $email ) . 
    "'," . "'" . mysql_real_escape_string( $sub ) . 
    "'," . "'" . mysql_real_escape_string( $com ) . 
    "'," . "'" . mysql_real_escape_string( $host ) . 
    "'," . "'" . mysql_real_escape_string( $pass ) . 
    "'," . "'" . $ext . 
    "'," . (int) $W . 
    "," . (int) $H . 
    "," . (int) $TN_W . 
    "," . (int) $TN_H . 
    "," . "'" . $tim . 
    "'," . (int) $time . 
    "," . "'" . $md5 . 
    "'," . (int) $fsize . 
    "," . "'" . mysql_real_escape_string( $upfile_name ) . 
    "'," . (int) $stickied . 
    "," . (int) $permasage . 
    "," . (int) $locked . 
    "," . $rootqu . 
    "," . (int) mysql_real_escape_string($resto) . ")";
    
	if ( !$result = mysql_call( $query ) ) {
		echo S_SQLFAIL;
	} //post registration
	
	$cookie_domain = '.' . SITE_ROOT . '';
	//Cookies
	setrawcookie( "" . SITE_ROOT . "_name", rawurlencode( $c_name ), time() + ( $c_name ? ( 7 * 24 * 3600 ) : -3600 ), '/', $cookie_domain );
	if ( ( $c_email != "sage" ) && ( $c_email != "age" ) ) {
		setcookie(  "" . SITE_ROOT . "_email", $c_email, time() + ( $c_email ? ( 7 * 24 * 3600 ) : -3600 ), '/', $cookie_domain ); // 1 week cookie expiration
	}
	setcookie(  "" . SITE_ROOT . "_pass", $c_pass, time() + 7 * 24 * 3600, '/', $cookie_domain ); // 1 week cookie expiration
	
	
	if ( !$resto )
		prune_old();
	
	// thumbnail
	if ( $has_image ) {
		rename( $dest, $path . $tim . $ext );
		if ( USE_THUMB ) {
			$tn_name = thumb( $path, $tim, $ext, $resto );
			if ( !$tn_name && $ext != ".pdf" ) {
				error( S_UNUSUAL );
			}
		}
	}
	
	$static_rebuild = defined( "STATIC_REBUILD" ) && ( STATIC_REBUILD == 1 );
    
    //Finding the last entry number
	if ( !$result = mysql_call( "select max(no) from " . SQLLOG ) ) {
        echo S_SQLFAIL;
	}
	$hacky    = mysql_fetch_array( $result );
	$insertid = (int) $hacky[0];
    mysql_free_result( $result );
    
	$deferred       = false;
	// update html
	if ( $resto ) {
		$deferred = updatelog( $resto, $static_rebuild );
	} else {
		$deferred = updatelog( $insertid, $static_rebuild );
	}
	
	if ( $noko && !$resto ) {
		$redirect = DATA_SERVER . BOARD_DIR . "/res/" . $insertid . PHP_EXT;
	} else if ( $noko == 1 ) {
		$redirect = DATA_SERVER . BOARD_DIR . "/res/" . $resto . PHP_EXT . '#' . $insertid;
	} else {
		$redirect = PHP_SELF2_ABS;
	}
	
	if ( $deferred ) {
		echo "<html><head><META HTTP-EQUIV=\"refresh\" content=\"2;URL=$redirect\"></head>";
		echo "<body>$mes " . S_SCRCHANGE . "<br>Your post may not appear immediately.<!-- thread:$resto,no:$insertid --></body></html>";
	} else {
		echo "<html><head><META HTTP-EQUIV=\"refresh\" content=\"1;URL=$redirect\"></head>";
		echo "<body>$mes " . S_SCRCHANGE . "<!-- thread:$resto,no:$insertid --></body></html>";
	}
	
}

// word-wrap without touching things inside of tags
function wordwrap2( $str, $cols, $cut )
{
	// if there's no runs of $cols non-space characters, wordwrap is a no-op
	if ( strlen( $str ) < $cols || !preg_match( '/[^ <>]{' . $cols . '}/', $str ) ) {
		return $str;
	}
	$sections = preg_split( '/[<>]/', $str );
	$str      = '';
	for ( $i = 0; $i < count( $sections ); $i++ ) {
		if ( $i % 2 ) { // inside a tag
			$str .= '<' . $sections[$i] . '>';
		} else { // outside a tag
			$words = explode( ' ', $sections[$i] );
			foreach ( $words as &$word ) {
				$word  = wordwrap( $word, $cols, $cut, 1 );
				// fix utf-8 sequences (XXX: is this slower than mbstring?)
				$lines = explode( $cut, $word );
				for ( $j = 1; $j < count( $lines ); $j++ ) { // all lines except the first
					while ( 1 ) {
						$chr = substr( $lines[$j], 0, 1 );
						if ( ( ord( $chr ) & 0xC0 ) == 0x80 ) { // if chr is a UTF-8 continuation...
							$lines[$j - 1] .= $chr; // put it on the end of the previous line
							$lines[$j] = substr( $lines[$j], 1 ); // take it off the current line
							continue;
						}
						break; // chr was a beginning utf-8 character
					}
				}
				$word = implode( $cut, $lines );
				
			}
			$str .= implode( ' ', $words );
		}
	}
	return $str;
}

function modify_post ( $no, $action = 'none')
{
if (!valid('moderator'))
	die('Action on post ' . $no .' failed...');
	
    switch ($action) {
        case 'lock':
            mysql_call( 'UPDATE ' . SQLLOG . " SET locked='1' WHERE no='" . mysql_real_escape_string( $no ) . "'" );
            break;
        case 'permasage':
            mysql_call( 'UPDATE ' . SQLLOG . " SET permasage='1' WHERE no='" . mysql_real_escape_string( $no ) . "'" );
            break;
        case 'sticky':
            $rootnum = "202707070000";
            mysql_call( 'UPDATE ' . SQLLOG . " SET sticky='1' , root='" . $rootnum . " WHERE no='" . mysql_real_escape_string( $no ) . "'" );
            break;
        case 'unlock':
            mysql_call( 'UPDATE ' . SQLLOG . " SET locked='0' WHERE no='" . mysql_real_escape_string( $no ) . "'" );
            break;
        case 'unsticky':
            $rootnum = "now()";
            mysql_call( 'UPDATE ' . SQLLOG . " SET sticky='0' , root='" . $rootnum . " WHERE no='" . mysql_real_escape_string( $no ) . "'" );
            break;
    }
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
			/*
			//Legacy gif processing, requires gif2png to be present in the board dir. You can find the file here: http://freecode.com/projects/gif2png
			
			if ( !is_executable( realpath( "gif2png" ) ) || !function_exists( "ImageCreateFromPNG" ) )
				return;
			@exec( realpath( "gif2png" ) . " $fname", $a );
			if ( !file_exists( $path . $tim . '.png' ) )
				return;
			$im_in = ImageCreateFromPNG( $path . $tim . '.png' );
			unlink( $path . $tim . '.png' );
			if ( !$im_in )
				return;
			break;*/
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
    	ImageAlphaBlending( $im_out, false );
    	ImageSaveAlpha( $im_out, true );
	// copy resized original
	ImageCopyResampled( $im_out, $im_in, 0, 0, 0, 0, $out_w, $out_h, $size[0], $size[1] );
	// thumbnail saved
	 if ( $ext == ".gif" || $ext == ".png" ) 
        	ImagePNG( $im_out, $outpath, 6);
	 else
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

// deletes a post from the database
// imgonly: whether to just delete the file or to delete from the database as well
// automatic: always delete regardless of password/admin (for self-pruning)
// children: whether to delete just the parent post of a thread or also delete the children
// die: whether to die on error
// careful, setting children to 0 could leave orphaned posts.
function delete_post($resno, $pwd, $imgonly = 0, $automatic = 0, $children = 1, $die = 1)
{
		global $log, $path;
		log_cache();
		$resno = intval($resno);
		
		// get post info
		if (!isset($log[$resno])) {
				if ($die)
						error("Can't find the post $resno.");
		}
		$row = $log[$resno];
		
		// check password- if not ok, check admin status (and set $admindel if allowed)
		$delete_ok = ($automatic || (substr(md5($pwd), 2, 8) == $row['pwd']) || ($row['host'] == $_SERVER['REMOTE_ADDR']));
		if (valid('janitor_board')) {
				$delete_ok = $admindel = valid('delete', $resno);
		}
		if (!$delete_ok)
				error(S_BADDELPASS);
		
		// check ghost bumping
		if (!isset($admindel) || !$admindel) {
				if (BOARD_DIR == 'a' && (int) $row['time'] > (time() - 25) && $row['email'] != 'sage') {
						$ghostdump = var_export(array(
								'server' => $_SERVER,
								'post' => $_POST,
								'cookie' => $_COOKIE,
								'row' => $row
						), true);
						//file_put_contents('ghostbump.'.time(),$ghostdump);
				}
		}
		
		if (isset($admindel) && $admindel) { // extra actions for admin user
				$auser   = mysql_real_escape_string($_COOKIE['saguaro_auser']);
				$adfsize = ($row['fsize'] > 0) ? 1 : 0;
				$adname  = str_replace('</span> <span class="postertrip">!', '#', $row['name']);
				if ($imgonly) {
						$imgonly = 1;
				} else {
						$imgonly = 0;
				}
				$row['sub']      = mysql_real_escape_string($row['sub']);
				$row['com']      = mysql_real_escape_string($row['com']);
				$row['filename'] = mysql_real_escape_string($row['filename']);
				mysql_call("INSERT INTO " . SQLDELLOG . " (imgonly,postno,board,name,sub,com,img,filename,admin) values('$imgonly','$resno','" . SQLLOG . "','$adname','{$row['sub']}','{$row['com']}','$adfsize','{$row['filename']}','$auser')");
		}
		
		if ($row['resto'] == 0 && $children && !$imgonly) // select thread and children
				$result = mysql_call("select no,resto,tim,ext from " . SQLLOG . " where no=$resno or resto=$resno");
		else // just select the post
				$result = mysql_call("select no,resto,tim,ext from " . SQLLOG . " where no=$resno");
		
		while ($delrow = mysql_fetch_array($result)) {
				// delete
				$delfile  = $path . $delrow['tim'] . $delrow['ext']; //path to delete
				$delthumb = THUMB_DIR . $delrow['tim'] . 's.jpg';
				if (is_file($delfile))
						unlink($delfile); // delete image
				if (is_file($delthumb))
						unlink($delthumb); // delete thumb
				if (OEKAKI_BOARD == 1 && is_file($path . $delrow['tim'] . '.pch'))
						unlink($path . $delrow['tim'] . '.pch'); // delete oe animation
				if (!$imgonly) { // delete thread page & log_cache row
						if ($delrow['resto'])
								unset($log[$delrow['resto']]['children'][$delrow['no']]);
						unset($log[$delrow['no']]);
						$log['THREADS'] = array_diff($log['THREADS'], array(
								$delrow['no']
						)); // remove from THREADS
						mysql_call("DELETE FROM reports WHERE no=" . $delrow['no']); // clear reports
						if (USE_GZIP == 1) {
								@unlink(RES_DIR . $delrow['no'] . PHP_EXT);
								@unlink(RES_DIR . $delrow['no'] . PHP_EXT . '.gz');
						} else {
								@unlink(RES_DIR . $delrow['no'] . PHP_EXT);
						}
				}
		}
		
		//delete from DB
		if ($row['resto'] == 0 && $children && !$imgonly) // delete thread and children
				$result = mysql_call("delete from " . SQLLOG . " where no=$resno or resto=$resno");
		elseif (!$imgonly) // just delete the post
				$result = mysql_call("delete from " . SQLLOG . " where no=$resno");
		
		return $row['resto']; // so the caller can know what pages need to be rebuilt
}

/* user image deletion */
function usrdel( $no, $pwd )
{
	global $path, $pwdc, $onlyimgdel;
	$host    = $_SERVER["REMOTE_ADDR"];
	$delno   = array();
    $rebuildindex = !(defined("STATIC_REBUILD") && STATIC_REBUILD);
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
    $rebuild = array(); // keys are pages that need to be rebuilt (0 is index, of course)
	for ( $i = 0; $i < $countdel; $i++ ) {
		/*if ( !$result = mysql_call( "select no,ext,tim,pwd,host from " . SQLLOG . " where no=" . $delno[$i] ) ) {
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
		}*/
        $resto = delete_post($delno[$i], $pwd, $onlyimgdel, 0, 1, $countdel == 1); // only show error for user deletion, not multi
			if ($resto)
				$rebuild[$resto] = 1;
    }
	/*if ( !$flag )
		error( S_BADDELPASS );*/
    log_cache();
	//mysql_board_call("UNLOCK TABLES");  
	foreach ($rebuild as $key => $val) {
		updatelog($key, 1); // leaving the second parameter as 0 rebuilds the index each time!
	}
	if ($rebuildindex)
		updatelog(); // update the index page last
}

function login($usernm, $passwd)
{
    $ip = $_SERVER['REMOTE_ADDR'];
    $usernm = mysql_real_escape_string($usernm);
    $passwd = mysql_real_escape_string($passwd);
    
    $query = mysql_call( "SELECT user,password FROM " . SQLMODSLOG . " WHERE user='$usernm' and password='$passwd'" );

    if ($query == 0 or $query == FALSE) {
        mysql_call("INSERT INTO loginattempts (userattempt,passattempt,board,ip,attemptno) values('$usernm','$passwd','" . BOARD_DIR . "','$ip','1')");
        error( S_WRONGPASS );
    }
    
	$hacky    = mysql_fetch_array( $query );
	$usernm = $hacky[0];
    $passwd = $hacky[1];
    
    setcookie('saguaro__auser', $usernm, 0);
    setcookie('saguaro__apass', $passwd, 0);
    
    echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF_ABS . "?mode=admin" . "\">";
}

/*password validation */
function oldvalid( $pass )
{
	
/*    if ( $pass && $pass != PANEL_PASS )
		error( S_WRONGPASS );*/
    
    if (valid('janitor_board')) {
        head( $dat );
        echo $dat;
        echo "[<a href=\"" . PHP_SELF2 . "\">" . S_RETURNS . "</a>]\n";
        echo "[<a href=\"" . PHP_SELF . "\">" . S_LOGUPD . "</a>]\n";  
        if (valid('moderator')) {
            echo "[<a href=\"" . PHP_SELF . "?mode=rebuild\">Rebuild</a>]\n";
            echo "[<a href=\"" . PHP_SELF . "?mode=rebuildall\">Rebuild all</a>]\n";   
        }
        echo "[<a href=\"" . PHP_SELF . "?mode=logout\">" . S_LOGOUT . "</a>]\n";  
        echo "<div class=\"passvalid\">" . S_MANAMODE . "</div>\n";
    }
	echo "<p><form action=\"" . PHP_SELF . "\" method=\"post\">\n";
	// Mana login form
	if ( !valid('janitor_board' ) ) {
		echo "<div class=\passvalid\" align=\"center\" vertical-align=\"middle\" >";
		echo "<input type=hidden name=mode value=admin>\n";
        echo "<input type=text name=usernm size=20><br />";
		echo "<input type=password name=passwd size=20><br />";
		echo "<input type=submit value=\"" . S_MANASUB . "\"></form></div>\n";
        if (isset($_POST['usernm']) && isset($_POST['passwd']))
                login($_POST['usernm'], $_POST['passwd']);
		die( "</body></html>" );
	}
}


function ban( $ip, $pubreason, $staffreason, $banlength )
{
    if (valid('moderator')) {
        $placedOn = time();
        $query    = mysql_query( "SELECT ip FROM " . SQLBANLOG . " WHERE ip = '$ip' AND banlength != 0" );
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
            $sql = "INSERT INTO " . SQLBANLOG . " (ip, pubreason, staffreason, banlength, placedOn, board) VALUES ('$ip', '$pubreason', '$staffreason', '$banset', '$placedOn', '" . BOARD_DIR . "')";
            
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
    } else {
        die('You do not have permission to do that! IP: ' . $_SERVER['REMOTE_ADDR'] . " logged.");
    }
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
				/*if ( array_search( $no, $delno ) ) { //only a picture is deleted
					$delfile = $path . $tim . $ext; //only a picture is deleted
					if ( is_file( $delfile ) )
						unlink( $delfile ); //delete
					if ( is_file( THUMB_DIR . $tim . 's.jpg' ) )
						unlink( THUMB_DIR . $tim . 's.jpg' ); //delete
				}*/
                delete_post($no, $pwd, 1, 1, 1, 0);
            } else {
				if ( array_search( $no, $delno ) ) { //It is empty when deleting
                    delete_post($no, $pwd, 0, 1, 1, 0);

					/*$find = TRUE;
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
						unlink( THUMB_DIR . $tim . 's.jpg' ); //Delete*/
				}
			}
		}
/*		mysql_free_result( $result );
		if ( $find ) { //log renewal
		}*/
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
			$truncname = substr( $name, 0, 9 ) . "...";
        else 
            $truncname = $name;
		if ( strlen( $sub ) > 10 )
			$truncsub = substr( $sub, 0, 9 ) . "...";
        else 
            $truncsub = $sub;
		if ( $email )
			$name = "<a href=\"mailto:$email\">$name</a>";
		$com = str_replace( "<br />", " ", $com );
		$com = htmlspecialchars( $com );
		if ( strlen( $com ) > 20 )
			$trunccom = substr( $com, 0, 18 ) . "...";
        else 
            $trunccom = $com;
        if ( strlen( $fname ) > 10 ) 
            $truncfname = substr( $fname, 0, 40 ) . "..." . $ext;
        else 
            $truncfname = $fname;
		// Link to the picture
		if ( $ext && is_file( $path . $tim . $ext ) ) {
			$img_flag = TRUE;
			$clip     = "<a class=\"thumbnail\" target=\"_blank\" href=\"" . IMG_DIR . $tim . $ext . "\">" /*. $tim . $ext . "<span><img src=\"" . THUMB_DIR . $tim . 's.jpg' . "\" width=\"100\" height=\"100\" />"*/ . "</span></a><br />";
			if ( $fsize >= 1048576 ) {
				$size  = round( ( $fsize / 1048576 ), 2 ) . " M";
				$fsize = $asize;
			} else if ( $fsize >= 1024 ) {
				$size  = round( $fsize / 1024 ) . " K";
				$fsize = $asize;
			} else {
				$size  = $fsize . " ";
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
		
        $resdo = '';
		if ( $resto == '0' ) 
			$resdo = '<b>Orig. post(<a href="' . DATA_SERVER . BOARD_DIR . "/" . RES_DIR . $no . PHP_EXT .'#' . $no . '" target="_blank" />' . $no. '</a>)</b>';
        else 
            $resdo = '<a href="' . DATA_SERVER . BOARD_DIR . "/" . RES_DIR . $resto . PHP_EXT .'#' . $no . '" target="_blank" />' . $resto. '</a>';
        $warnSticky = '';
		if ( $sticky == '1' ) 
			$warnSticky = "<b><font color=\"FF101A\">(Sticky)</font></b>";
        if (valid('janitor_board') && !valid('moderator')) //Hide IPs from janitors
            $host = '###.###.###.###';
        
		/*echo "<tr class=$class><td><input type=checkbox name=\"$no\" value=delete>$warnSticky</td>";
		echo "<td>$no</td><td>$resdo</td><td>$now</td><td>$truncsub</td>";
		echo "<td>$truncname</b></td><td>$trunccom</td>";
		echo "<td>$host</td><td>$clip($size)</td><td>$md5</td><td>$truncfname</td><td>" . calculate_age( $time ) . "</td>\n";
		echo "</tr>\n";*/
		echo "<tr class=$class><td><input type=checkbox name=\"$no\" value=delete>$warnSticky</td>";
		echo "<td>$no</td><td>$resdo</td><td>$now</td><td>$truncsub</td>";
		echo "<td>$truncname</b></td><td>$trunccom</td>";
		echo "<td>$host</td><td>" . calculate_age( $time ) . "</td>\n";
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


function rebuild($all = 0)
{
    if (!valid('moderator'))
        die('Update failed...');
    
		header("Pragma: no-cache");
		echo "Rebuilding ";
		if ($all) {
				echo "all";
		} else {
				echo "missing";
		}
		echo " replies and pages... <a href=\"" . PHP_SELF2_ABS . "\">Go back</a><br><br>\n";
		ob_end_flush();
		$starttime = microtime(true);
		if (!$treeline = mysql_call("select no,resto from " . SQLLOG . " where root>0 order by root desc")) {
				echo S_SQLFAIL;
		}
		log_cache();
		echo "Writing...\n";
		if ($all || !defined('CACHE_TTL')) {
				while (list($no, $resto) = mysql_fetch_row($treeline)) {
						if (!$resto) {
								updatelog($no, 1);
								echo "No.$no created.<br>\n";
						}
				}
				updatelog();
				echo "Index pages created.<br>\n";
		} else {
				$posts = rebuildqueue_take_all();
				foreach ($posts as $no) {
						$deferred = (updatelog($no, 1) ? ' (deferred)' : '');
						if ($no)
								echo "No.$no created.$deferred<br>\n";
						else
								echo "Index pages created.$deferred<br>\n";
				}
		}
		$totaltime = microtime(true) - $starttime;
		echo "<br>Time elapsed (lock excluded): $totaltime seconds", "<br>Pages created.<br><br>\nRedirecting back to board.\n<META HTTP-EQUIV=\"refresh\" content=\"10;URL=" . PHP_SELF2 . "\">";
}


//Called when someone tries to visit imgboard.php?res=[[[postnumber]]]
function resredir( $res )
{
	$res = (int) $res;

	if ( !$redir = mysql_call( "select no,resto from " . SQLLOG . " where no=" . $res ) ) {
		echo S_SQLFAIL;
	}
	list( $no, $resto ) = mysql_fetch_row( $redir );
	if ( !$no ) {
		$maxq = mysql_call( "select max(no) from " . SQLLOG . "" );
		list( $max ) = mysql_fetch_row( $maxq );
		if ( !$max || ( $res > $max ) )
			header( "HTTP/1.0 404 Not Found" );
		else // res < max, so it must be deleted!
			header( "HTTP/1.0 410 Gone" );
		error( S_NOTHREADERR, $dest );
	}
	
	if ( $resto == "0" ) // thread
		$redirect = DATA_SERVER . BOARD_DIR . "/res/" . $no . PHP_EXT . '#' . $no;
	else
		$redirect = DATA_SERVER . BOARD_DIR . "/res/" . $resto . PHP_EXT . '#' . $no;
	
	
	echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=$redirect\">";
	if ( $resto == "0" )
		log_cache();

	
	if ( $resto == "0" ) { // thread
		updatelog( $res );
	}
}
/*

function adduser($_POST['addusername'], $_POST['adduserpass']) 
{
    if ( !valid('manager') ) 
        die('"AUTOBAN ME NOW PLEASE" - you');
    
    if ( $password != PANEL_PASS )
        die('"AUTOBAN ME NOW PLEASE" - you right now');
    
    
    if ( isset($_POST['addusername']) && isset ( $_POST['adduserpass'] ) && isset($_POST['accessLevel']) ) 
        mysql_call("INSERT INTO " . SQLMODSLOG . " ('username', 'password', 'allowed', 'denied' 
    
}*/

/*-----------Main-------------*/
switch ( $mode ) {
	case 'regist':
		regist( $name, $email, $sub, $com, '', $pwd, $upfile, $upfile_name, $resto, $num );
		break;
	case 'admin':
		oldvalid( $pass );
        form( $post, $res, 1 );
        echo $post;
        echo "<form action=\"" . PHP_SELF . "\" method=\"post\">
        <input type=hidden name=admin value=del checked>";
        admindel( $pass );
		die( "</body></html>" );
		break;
    case 'rebuild':
		rebuild();
		break;
    /*case 'bake':
        /*login($_POST['usernm'],$_POST['passwd']);
        setcookie('' . SITE_ROOT . '_auser', 'admin', 0);
        setcookie('' . SITE_ROOT . '_apass', 'guest', 0);
        echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF . "?mode=admin\">";
        break;*/
    case 'logout':
        setcookie('saguaro_apass', '0', 1);
        setcookie('saguaro_auser', '0', 1);
        echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF2_ABS . "\">";
        break;
    case 'zmdlog':
        login($_POST['usernm'], $_POST['passwd']);
        break;
/*    case 'users':
        if (!valid('manager'))
            die('"PLS AUTOBAN ME" - you');
        adduser($_POST['addusername'], $_POST['adduserpass']);
        remuser($_POST['remusername'], $_POST['remuserpass']);
	   break;*/
	case 'rebuildall':
		rebuild(1);
        break;
	case 'debug':
		echo $_SERVER['DOCUMENT_ROOT'];
		break;
	case 'banish':
		$ip          = $_POST['ip_to_ban'];
		$pubreason   = mysql_real_escape_string( $_POST['pubreason'] );
		$staffreason = mysql_real_escape_string( $_POST['staffreason'] );
		$banlength   = mysql_real_escape_string( $_POST['timebannedfor'] );
		ban( $ip, $pubreason, $staffreason, $banlength );
		echo '<br/ > <a href="' . PHP_SELF . '?mode=admin" />Return</a>';
		break;
	case 'usrdel':
		usrdel( $no, $pwd );
	default:
		if ( $res ) {
			resredir( $res );
			echo "<META HTTP-EQUIV=\"refresh\" content=\"10;URL=" . PHP_SELF2_ABS . "\">";
		} else {
			echo "Updating index...\n";
			updatelog();
			echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF2_ABS . "\">";
		}
}
?>
