<?php
include("config.php");

$min_php = '4.2.0';
$min_gd = '2.0.0';
$min_mysql = '4.0.0';



$tests = [];

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
        mysqli_close($mysqli);
    } else {
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



echo "Saguaro testing utility:<br><br>";

foreach ($tests as $key => $results) {
    $temp = "<strong>$key:</strong> ";
    $color = ($results['valid']) ? "green" : "red";
    $msg = ($results['valid']) ? "PASS" : "FAIL";

    $debug = $results['current'] . (($results['valid']) ? " >= " : " < ") . $results['min'];

    $temp .= "<span style='color:$color;font-weight:bold;'>$msg</span> ($debug)<br>";

    echo $temp;
}

//print_r($tests);

?>