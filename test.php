<?php
$min_php = '4.2.0';
$min_gd = '2.0.0';

$tests = [];

//Return true if PHP is at or above $min_php, false otherwise.
$tests["PHP Version"] =
    [
        "current" => phpversion(),
        "valid" => version_compare(phpversion(), $min_php, '>='),
        "min" => $min_php
    ];

//Return true if GD is at or above $min_gd, false otherwise.
$tests["GD Version"] =
    [
        "current" => gd_info()["GD Version"],
        "valid" => version_compare(gd_info()["GD Version"], $min_gd, '>='),
        "min" => $min_php
    ];

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