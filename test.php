<?php

//ini_set('display_errors',1);

/*

    Tests if the server meets basic minimum requirements.

    Might want to make the autolock user-selectable.
        Redirect back to page with a get to call it?
*/

$autolock = true;
$lockout = "." . basename(__FILE__, ".php") . "_lockout";

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
            .extra { /*width:49%; display:inline-block;*/ } .box { background-color:#D6DAF0; padding:10px; border-radius:10px; margin: 2%; }
            ul { margin: 5px 0px; }";

    //Point of no return.
    echo "<style>$css</style>";

    //Lock out.
    if ($autolock) {
        touch($lockout);
        $log .= "For security, <strong>\"$lockout\"</strong> has been created in the same directory $mydir and this script <strong>will not function again until it is deleted.</strong><br><br>";
    }

    $config_good = false;
    $mysql_good = false;
    $mydir = "(" . dirname(__FILE__) . ")";
    $owner = "<strong>" . get_current_user() ."</strong>";
    $user = "<strong>" . posix_getpwuid(posix_geteuid())['name'] . "</strong>";
    $log .= "This file (<strong>" . basename(__FILE__) . "</strong>) is owned by $owner and running as user $user. Any files/folders created should be owned by $user.<br>";
    include($config);

    $tests = [];

    echo "<div class='box' id='title'>Saguaro Testing and Installation Utility</div>";

    //Check to see if $config was included properly.
    $loga = "<strong>\"$config\"</strong> from the same directory $mydir failed to be included properly, some tests may fail.<br>";
    foreach (get_included_files() as $val) {
        if (strrpos($val, $config)) { $loga = "Successfully loaded <strong>\"$config\"</strong> from the same directory. $mydir<br>"; $config_good = true; }
    }
    $log .= $loga;

    //Check PHP version.
    $tests["PHP version"] =
        [
            "current" => phpversion(),
            "valid" => version_compare(phpversion(), $min_php, '>='),
            "min" => $min_php
        ];

    //Check GD version.
    $gd_ver = preg_match("/(?:\d\.?)+/", gd_info()["GD Version"], $match);
    $gd_ver = $match[0];
    $tests["GD version"] =
        [
            "current" => (function_exists("gd_info")) ? $gd_ver : 0,
            "valid" => (function_exists("gd_info")) ? version_compare($gd_ver, $min_gd, '>=') : 0,
            "min" => $min_gd
        ];

    //Check MySQL version.
    $out = ["current" => 0, "valid" => 0, "min" => $min_mysql];
    if (class_exists('mysqli')) {
        $mysqli = new mysqli(SQLHOST, SQLUSER, SQLPASS);

        if (mysqli_connect_errno()) {
            $log .= "Failed to connect to the MySQL server, version cannot be obtained. <strong>Error:</strong> " . mysqli_connect_errno() . "<br>";
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

    //Output results of tests.
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

    //Create MySQL database and tables.
    if (!$config_good) {
        echo "Config was not loaded, cannot initialize MySQL data.";
    } else {
        if (!$mysql_good) {
            echo "There was a problem with MySQL, cannot initialize MySQL data.";
        } else {
            echo "These SQL queries are executed as <strong>" . SQLUSER . "</strong> on the SQL server <strong>" . SQLHOST . "</strong>.<br>";

            $db = SQLDB;
            //mysqli_query($mysqli, "DROP DATABASE `$db`");
            $has_db = mysqli_select_db($mysqli, $db);

            if (!$has_db) {
                //Create database.

                echo "<strong>$db</strong> database does not exist, creating... ";
                $status = mysqli_query($mysqli, "CREATE DATABASE $db");
                echo ($status) ? $success : $fail;

                if (!$status) {
                    echo "Unable to create <strong>$db</strong> database (error: " . mysqli_errno($mysqli) . "), cannot proceed to initialize MySQL data.";
                } else {
                    $has_db = true;
                }
            } else {
                echo "<strong>$db</strong> database already exists.<br>";
            }

            if ($has_db) {
                mysqli_select_db($mysqli, $db);

                //Create tables.
                $tables = [
                    SQLLOG => "primary key(no), no int not null auto_increment, now text, name text, email text, sub text, com text, host text, pwd text, ext text, w int, h int, tn_w int, tn_h int, tim text, time int, md5 text, fsize int, fname text, sticky int, permasage int, locked int, root  timestamp, resto int, board text",
                    SQLBANLOG => "ip VARCHAR(25) PRIMARY KEY, pubreason VARCHAR(250), staffreason VARCHAR(250), banlength VARCHAR(250), placedOn VARCHAR(50), board VARCHAR(50)",
                    SQLMODSLOG => "user VARCHAR(25) PRIMARY KEY, password  VARCHAR(250), allowed  VARCHAR(250), denied  VARCHAR(250)",
                    SQLDELLOG => "imgonly VARCHAR(25) PRIMARY KEY, postno VARCHAR(250), board VARCHAR(250), name VARCHAR(250), sub VARCHAR(50), com VARCHAR(" . S_POSTLENGTH . "), img VARCHAR(250), filename VARCHAR(250), admin VARCHAR(100)", //Why does S_POSTLENGTH start with S_?
                    "reports" => "no VARCHAR(25) PRIMARY KEY, reason  VARCHAR(250), ip VARCHAR(250), board VARCHAR(250)",
                    "loginattempts" => "userattempt VARCHAR(25) PRIMARY KEY, passattempt VARCHAR(250), board VARCHAR(250), ip VARCHAR(250), attemptno VARCHAR(50)"
                ];

                foreach ($tables as $table => $query) {
                    $sql = "SHOW TABLES LIKE '$table'";
                    $exists = (mysqli_num_rows(mysqli_query($mysqli, $sql)) > 0) ? true : false;

                    if ($exists) {
                        echo "<strong>$table</strong> table already exists.<br>";
                    } else {
                        echo "<strong>$table</strong> table does not exist, creating... ";
                        $status = mysqli_query($mysqli, "CREATE TABLE $table ($query)");
                        echo ($status) ? $success : "(" . mysqli_errno($mysqli) . ") " . $fail;
                    }

                    mysqli_free_result($exists);
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

    //Create working directories.
    if (!$config_good) {
        echo "Config was not loaded, cannot validate install files.";
    } else {
        $folders = [RES_DIR, IMG_DIR, THUMB_DIR];

        foreach ($folders as $dir) {
            $fdir = "<strong>$dir</strong>";

            if (!is_dir($dir)) {
                echo "$fdir does not exist, creating... ";
                $status = mkdir($dir);
                echo ($status) ? $success : $fail;
            } else {
                echo "$fdir already exists.<br>";
            }

            $perms = substr(sprintf('%o', fileperms($dir)), -4);

            if ($perms !== "0777") {
                echo "Changing $fdir permissions from $perms to 0777... ";
                $status = chmod($dir, 0777);
                echo ($status) ? $success : $fail;
            } else {
                echo "$fdir has the right permissions (0777).<br>";
            }

            clearstatcache();
        }
    }

    echo "</div><div class='box'>" .
        "<strong>Additional Resources:</strong><br>MySQL error codes: <ul><li><a href='https://search.oracle.com/search/search?q=Server+Error+codes&group=MySQL' target='_blank'>Server</a> (1000-1999)</li><li><a href='https://search.oracle.com/search/search?q=Client+Error+Codes&group=MySQL' target='_blank'>Client</a> (2000+)</li></ul>" .
        "</div>";
}
?>