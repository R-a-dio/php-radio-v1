<?php

/*
 * Optional filters:
 * (int) min_bitrate: minimum bitrate of the relay
 * (int) bitrate: exact bitrate fo the relay
 * (string) format: format of the relay, mp3 || ogg
 * (int) active: if a relay is active, 0 || 1
 * (int) not_full: if set, filters out relays that are reaching their peak listener count
 */
// PS: i did everything in logical order, feel free to neaten it up in a GUI editor
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
header("Content-Type: application/json; charset=utf-8");
require_once(__DIR__ . "/../res/common.php"); // why do relative paths not work?
function jsonp_encode($input) {
	$x = json_encode($input);
	$c = isset($_GET["callback"]) ? $_GET["callback"] : ''; //nigger_hacking.jpg
	return $c . "(" . $x . ");"; // JSON-P
}
//screw your shit i want to use mysqli w/ persistant connections
$db = new mysqli("p:".$dbip, $dbuser, $dbpass, $dbname); // no need to close this database connection on script end, since the p:
							 // makes $db->close(); do nothing
if ($db->connect_error) {
	die(jsonp_encode(array("errno" => $db->connect_errno(), "error" => $db->connect_error()))); // hello there closeparen
}
//now, onto business
$array = array();
if ($result = $db->query('select id, relay_name, base_name, port, mount, bitrate, format, listeners, listener_limit, active from relays;')) {
	while ($row = $result->fetch_assoc()) {
		array_push($array, $row);
	}
	$result->free(); // the garbage man's here
} else {
	die(jsonp_encode(array("error" => "table connection error (TODO: add error)")));
}
// Owner + auth = personal, credits on the staff page?
//$test = json_encode($array); //done, but the emperor wants me to continue
//filter here first before moving onto ID-specific filtering

function filter(array $source, $fn) {
	$result = array();
	foreach ($source as $key => $value) {
		if ($fn($value)) {
			$result[$key] = $value; //keep item if true
		}
	}
	return $result;
}

// anonymous callbacks

//format: &format=mp3||ogg
$format = function ($value) {
	// cba to santize... it's an API, nothing is at risk, get it right yourself, etc.
	// feel free to add errors for incorrect filters
	if ($value["format"] != $_GET["format"]) {
		return FALSE; // throw away, ID included
	}
	return TRUE;
};
//minimum bitrate: &bitrate=(int)<minbitrate>
$min_bitrate = function ($value) {
	if ($value["bitrate"] < $_GET["min_bitrate"]) {
		return FALSE;
	}
	return TRUE;
};
//active: &active=1||0
$active = function ($value) {
        if ($value["active"] != $_GET["active"]) {
                return FALSE;
        }
        if ($_GET["active"] == 1) {
                if ($value["disabled"] == 1) {
                        return FALSE;
                }
                return TRUE;
        } else {
                if ($value["disabled"] == 1) {
                        return FALSE;
                }
                return TRUE;
        }
        return TRUE;
};

$bitrate = function ($value) {
        if ($value["bitrate"] != $_GET["bitrate"]) {
                return FALSE;
        }
        return TRUE;
};

$not_full = function ($value) {
	if(is_null($value["listener_limit"]) || $value["listeners"] < ($value["listener_limit"] - 10)) {
		return TRUE;
	}
	return FALSE;
};



if (isset($_GET['active'])) {
        $array = filter($array, $active);
}
if (isset($_GET['min_bitrate'])) {
        $array = filter($array, $min_bitrate);
}
if (isset($_GET['bitrate'])) {
	$array = filter($array, $bitrate);
}
if (isset($_GET['format'])) {
        $array = filter($array, $format);
}
if (isset($_GET['not_full'])) {
	$array = filter($array, $not_full);
}




//dangerous here, hmm.
if (isset($_GET["id"])) {
	if (ctype_digit($_GET["id"])) {
		// order is preserved in the array as far as i know - correct me if i'm wrong and i'll read it in as id:{obj}
		// PS: i'm right
		if (array_key_exists($_GET["id"] - 1, $array)) {
			$t = $_GET["id"] - 1;
			echo jsonp_encode($array[$t]);
		} else {
			die(jsonp_encode(array("error" => "id ".$_GET["id"]." does not exist",
				"help" => "perhaps you filtered out the ID with parameters?")));
		}
	} else {
		die(jsonp_encode(array("error" => "malformed ID '".$_GET["id"]."', not ctype int")));
	}
} else {
	echo jsonp_encode($array);
}

?>
