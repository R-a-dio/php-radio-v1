<?php

require_once("/home/www/r-a-dio.com/res/common.php");

$db = new mysqli($dbip, $dbuser, $dbpass, $dbname);

//print_r(geoip_record_by_name('r-a-d.io'));

$array = array();
if($result = $db->query("SELECT ip FROM playerstats;")) {
	while ($row = $result->fetch_assoc()) {
                array_push($array, $row);
        }
	$result->free();
}
$db->close();

$database = array();


foreach ($array as $k => $arr) {
	array_push($database, geoip_continent_code_by_name($arr["ip"]));
}
$count = count($array);

$values = array_count_values($database);
foreach ($values as $code => $value) {
$values[$code] = array();
$values[$code]["Raw Number"] = $value;
$values[$code]["Percentage"] = round((($value / $count) * 100), 2) . "%";
}
echo "<pre>";
print_r($values);
echo "</pre>";
?>
