<?php

/*

    Tests if the server meets basic minimum requirements.

*/

$config = "config.php";
include($config);
$config_good = false;

$min_php = '4.2.0';
$min_gd = '2.0.0';
$min_mysql = '4.0.0';
$mysql_good = false;

$success = "<span style='color:green;font-weight:bold;'>SUCCESS</span><br>";
$fail = "<span style='color:red;font-weight:bold;'>FAIL</span><br>";

$tests = [];

//Check to see if $config was included properly.
$out = "<strong>\"$config\"</strong> from the same directory failed to be included properly, some tests may fail.<br>";
foreach (get_included_files() as $val) {
    if (strrpos($val, $config)) { $out = "Successfully loaded <strong>\"$config\"</strong> from the same directory.<br>"; $config_good = true; }
}
echo $out;

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
        echo "Failed to connect to the MySQL server, version cannot be obtained. <strong>mysql_connect_errno:</strong> " . mysqli_connect_errno() . " <a href='//dev.mysql.com/doc/refman/5.6/en/error-messages-client.html'>(Client)</a> <a href='//dev.mysql.com/doc/refman/5.6/en/error-messages-server.html'>(Server)</a><br>";
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

        mysqli_close($mysqli);
    }
}
$tests["MySQL version"] = $out;

echo "<br><hr><br>Saguaro testing & installation utility:<br><br>";

foreach ($tests as $key => $results) {
    $temp = "<strong>$key:</strong> ";
    $color = ($results['valid']) ? "green" : "red";
    $msg = ($results['valid']) ? "PASS" : "FAIL";

    $debug = $results['current'] . (($results['valid']) ? " >= " : " < ") . $results['min'];

    $temp .= "<span style='color:$color;font-weight:bold;'>$msg</span> ($debug)<br>";

    echo $temp;
}

echo "<br><hr><br>";

if (!$config_good) {
    echo "Config was not loaded, cannot initialize MySQL data.";
} else {
    //MySQL stuff;
    //$mysql_good
    echo "MySQL Stuff";
}

echo "<br><br><hr><br>";

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

//print_r($tests);

?>