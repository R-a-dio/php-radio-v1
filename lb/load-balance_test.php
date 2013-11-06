<?php
require_once("/home/www/r-a-dio.com/res/common.php");
$db = new mysqli("p:".$dbip, $dbuser, $dbpass, $dbname);
$local_port = "8000"; // string
$start = microtime(true);

function error_handle() {
        header("Location: http://stream0.r-a-d.io:{$local_port}/main.mp3");
        die();
}

function header_emit($name, $port, $mount) {
	header("Location: http://{$name}.r-a-d.io:{$port}{$mount}");
    die();
}

function debug_prints($id, $relay) {
	echo "<pre>\n";
	print_r($id);
	echo "\n";
	print_r($relay);
	echo "\n</pre>";
	die();
}

if ($db->connect_error) { error_handle(); }


$array = array();
if ($result = $db->query("select relay_name, port, mount, priority, listeners, listener_limit from relays where format='mp3' and bitrate=192 and active=1 and disabled=0;")) {
        while ($row = $result->fetch_assoc()) {
                array_push($array, $row);
        }
        $result->free(); // the garbage man's here
} else {
        error_handle();
}

$ratio = array();
foreach ($array as $k => $result) {
        if ($result["listeners"] / $result["listener_limit"] < 0.98) {
	    $ratio[$k] = ($result["listeners"] / $result["listener_limit"]) + (1 / mt_rand(15000,20000)) - ($result["priority"] / 1000);
        } else {
	    continue;
	}
}
if (!isset($ratio[0])) { // holy fuck every relay is full
	header("Location: http://r-a-d.io/ed/full.wav");
	$db->close();
	die();
}

$id = array_keys($ratio, min($ratio));
$relay = $array[$id[0]];

if (@$_GET["debug"] == 1) {
	debug_prints($ratio, $relay);
}

header_emit($relay["relay_name"], $relay["port"], $relay["mount"]);

?>


