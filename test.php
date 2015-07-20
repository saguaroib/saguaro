<?php

//ini_set('display_errors',1);

/*

    Tests if the server meets basic minimum requirements.

    Might want to make the autolock user-selectable.
        Redirect back to page with a get to call it?
*/

$autolock = true;
$lockout = ".test_lockout";

if (is_file($lockout)) {
    exit();
} else {
    //These should be the only things that should be changed without knowing what you're doing, everything that uses them is automated.
    $config = "config.php";
    $min_php = '4.2.0';
    $min_gd = '2.0.0';
    $min_mysql = '4.0.0';

    $success = "<span style='color:green;font-weight:bold;'>SUCCESS</span><br>";
    $fail = "<span style='color:red;font-weight:bold;'>FAIL</span><br>";

    $css = "body { background-color:#EEF2FF; } #title { font-size:xx-large; text-align:center; font-weight:bold; font-style: italic; }
            .extra { /*width:49%; display:inline-block;*/ } .box { background-color:#D6DAF0; padding:10px; border-radius:10px; margin: 2%; }";

    //Point of no return.
    echo "<style>$css</style>";

    $config_good = false;
    $mysql_good = false;
    $mydir = "(" . dirname(__FILE__) . ")";
    $owner = get_current_user();
    $user = posix_getpwuid(posix_geteuid())['name'];
    $log = "This script is owned by <strong>$owner</strong> and running as user <strong>$user</strong>. Any files/folders created should be owned by <strong>$user</strong>.<br>";
    include($config);

    $tests = [];

    echo "<div class='box' id='title'>Saguaro Testing and Installation Utility</div>";

    //Lock out.
    if ($autolock) {
        touch($lockout);
        $log .= "For security purposes, <strong>\"$lockout\"</strong> has been created in the same directory $mydir and this script <strong>will not function again until it is deleted.</strong><br>";
    }

    //Check to see if $config was included properly.
    $loga = "<strong>\"$config\"</strong> from the same directory $mydir failed to be included properly, some tests may fail.<br>";
    foreach (get_included_files() as $val) {
        if (strrpos($val, $config)) { $loga = "Successfully loaded <strong>\"$config\"</strong> from the same directory. $mydir<br>"; $config_good = true; }
    }
    $log .= $loga;

    //Return true if PHP is at or above $min_php, false otherwise.
    $tests["PHP version"] =
        [
            "current" => phpversion(),
            "valid" => version_compare(phpversion(), $min_php, '>='),
            "min" => $min_php
        ];

    $tests["GD version"] =
        [
            "current" => (function_exists("gd_info")) ? gd_info()["GD Version"] : 0,
            "valid" => (function_exists("gd_info")) ? version_compare(gd_info()["GD Version"], $min_gd, '>=') : 0,
            "min" => $min_gd
        ];

    $out = ["current" => 0, "valid" => 0, "min" => $min_mysql];
    if (class_exists('mysqli')) {
        $mysqli = new mysqli(SQLHOST, SQLUSER, SQLPASS);

        if (mysqli_connect_errno()) {
            $log .= "Failed to connect to the MySQL server, version cannot be obtained. <strong>mysql_connect_errno:</strong> " . mysqli_connect_errno() . " <a href='//dev.mysql.com/doc/refman/5.6/en/error-messages-client.html'>(Client)</a> <a href='//dev.mysql.com/doc/refman/5.6/en/error-messages-server.html'>(Server)</a><br>";
            mysqli_close($mysqli);
        } else {
            $mysql_good = true;
            $mver = mysqli_get_server_info($mysqli);

            $out =
                [
                    "current" => $mver,
                    "valid" => version_compare($mver, $min_mysql, '>='),
                    "min" => $min_mysql
                ];
        }
    }
    $tests["MySQL version"] = $out;

    echo "<div class='box' id='log'>$log</div>";
    echo "<div class='box extra' id='tests'>";

    foreach ($tests as $key => $results) {
        $temp = "<strong>$key:</strong> ";
        $color = ($results['valid']) ? "green" : "red";
        $msg = ($results['valid']) ? "PASS" : "FAIL";

        $debug = $results['current'] . (($results['valid']) ? " >= " : " < ") . $results['min'];

        $temp .= "<span style='color:$color;font-weight:bold;'>$msg</span> ($debug)<br>";

        echo $temp;
    }

    echo "</div><div class='box extra' id='mysql'>";

    if (!$config_good) {
        echo "Config was not loaded, cannot initialize MySQL data.";
    } else {
        if (!$mysql_good) {
            echo "There was a problem with MySQL, cannot initialize MySQL data.";
        } else {
            $db = SQLDB;
            //mysqli_query($mysqli, "DROP DATABASE `$db`");
            $has_db = mysqli_select_db($mysqli, $db);

            if (!$has_db) {
                //Create database.

                echo "<strong>$db</strong> database does not exist, creating... ";
                $status = mysqli_query($mysqli, "CREATE DATABASE $db");
                echo ($status) ? $success : $fail;

                if (!$status) {
                    echo "Unable to create <strong>$db</strong> database (" . mysqli_errno($mysqli) . "), cannot proceed to initialize MySQL data.";
                } else {
                    $has_db = true;
                }
            } else {
                echo "<strong>$db</strong> database already exists.<br>";
            }

            if ($has_db) {
                mysqli_select_db($mysqli, $db);

                //Create tables.
                //mysql_call(  );

                $tables = [
                    SQLLOG => "primary key(no), no int not null auto_increment, now text, name text, email text, sub text, com text, host text, pwd text, ext text, w int, h int, tn_w int, tn_h int, tim text, time int, md5 text, fsize int, fname text, sticky int, permasage int, locked int, root  timestamp, resto int, board text",
                    SQLBANLOG => "ip VARCHAR(25) PRIMARY KEY, pubreason VARCHAR(250), staffreason VARCHAR(250), banlength VARCHAR(250), placedOn VARCHAR(50), board VARCHAR(50)",
                    SQLMODSLOG => "user VARCHAR(25) PRIMARY KEY, password  VARCHAR(250), allowed  VARCHAR(250), denied  VARCHAR(250)",
                    SQLDELLOG => "imgonly VARCHAR(25) PRIMARY KEY, postno VARCHAR(250), board VARCHAR(250), name VARCHAR(250), sub VARCHAR(50), com VARCHAR(" . S_POSTLENGTH . "), img VARCHAR(250), filename VARCHAR(250), admin VARCHAR(100)", //Why does S_POSTLENGTH start with S_?
                    "reports" => "no VARCHAR(25) PRIMARY KEY, reason  VARCHAR(250), ip VARCHAR(250), board VARCHAR(250)",
                    "loginattempts" => "userattempt VARCHAR(25) PRIMARY KEY, passattempt VARCHAR(250), board VARCHAR(250), ip VARCHAR(250), attemptno VARCHAR(50)"
                ];

                foreach ($tables as $table => $query) {
                    //$exists = mysqli_query($mysqli, "SELECT count(*) FROM information_schema.tables WHERE table_schema = '$db' AND table_name = '$table'");
                    $sql = "SHOW TABLES LIKE \"$table\"";
                    $exists2 = mysqli_query($mysqli, $sql);// = mysqli_query($mysqli, $sql);
                    $exists = (mysqli_num_rows($exists2) > 0) ? true : false;

                    if ($exists) {
                        echo "<strong>$table</strong> table already exists.<br>";
                    } else {
                        echo "<strong>$table</strong> table does not exist, creating... ";
                        $status = mysqli_query($mysqli, "CREATE TABLE $table ($query)");
                        echo ($status) ? $success : "(" . mysqli_errno($mysqli) . ") " . $fail;
                    }

                    mysqli_free_result($exists2);
                }
                
                echo "Adding default account, <strong>admin : guest</strong>... ";
                $status = mysqli_query($mysqli, "INSERT INTO " . SQLMODSLOG . " (user, password, allowed, denied) VALUES ('admin', 'guest', 'janitor_board,moderator,admin,manager', 'none')");
                echo ($status) ? $success : "(" . mysqli_errno($mysqli) . ") " . $fail;
            }
        }

        mysqli_close($mysqli);
    }

    echo "</div>";
    echo "<div class='box extra' id='dirs'>";

    if (!$config_good) {
        echo "Config was not loaded, cannot validate install files.";
    } else {
        $folders = [RES_DIR, IMG_DIR, THUMB_DIR];

        foreach ($folders as $dir) {
            if (!is_dir($dir)) {
                echo "<strong>$dir</strong> does not exist, creating... ";
                $status = mkdir($dir);
                echo ($status) ? $success : $fail;
            } else {
                echo "<strong>$dir</strong> already exists.<br>";
            }

            $perms = substr(sprintf('%o', fileperms($dir)), -4);

            if ($perms !== "0777") {
                echo "Changing <strong>$dir</strong> permissions from $perms to 0777... ";
                $status = chmod($dir, 0777);
                echo ($status) ? $success : $fail;
            } else {
                echo "<strong>$dir</strong> has the right permissions (0777).<br>";
            }

            clearstatcache();
        }
    }

    echo "</div>";
}
?>