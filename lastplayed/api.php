<?php
include_once("../res/common.php");

mysql_connect($dbip, $dbuser, $dbpass);

@mysql_select_db($dbname);

mysql_query("SET NAMES 'utf8';");
if (isset($_GET['page'])) {
    $page = mysql_real_escape_string($_GET['page']);
	if ($page > 0) {
		$x = ($page - 1) * 20;
		$limit = (string)$x . ", 20";
	}
}
else {
	$limit = "0, 20";
}
if(isset($_GET['sort']) && $_GET['sort'] == "fave") {
	$lps = mysql_query("SELECT DISTINCT (SELECT max(eplay.dt) FROM eplay WHERE eplay.isong = esong.id) AS lastplayed, esong.meta AS metadata, (SELECT count(*) AS playcount FROM eplay WHERE eplay.isong = esong.id) AS playcount, (SELECT count(*) AS favecount FROM efave WHERE efave.isong = esong.id) AS favecount FROM esong JOIN eplay ON esong.id = eplay.isong ORDER BY favecount DESC LIMIT ".$limit.";");
}
else if(isset($_GET['sort']) && $_GET['sort'] == "play") {
	$lps = mysql_query("SELECT DISTINCT (SELECT max(eplay.dt) FROM eplay WHERE eplay.isong = esong.id) AS lastplayed, esong.meta AS metadata, (SELECT count(*) AS playcount FROM eplay WHERE eplay.isong = esong.id) AS playcount, (SELECT count(*) AS favecount FROM efave WHERE efave.isong = esong.id) AS favecount FROM esong JOIN eplay ON esong.id = eplay.isong ORDER BY playcount DESC LIMIT ".$limit.";");
}
else {
	$lps = mysql_query("SELECT eplay.dt AS lastplayed, esong.meta AS metadata, (SELECT COUNT(*) FROM efave WHERE eplay.isong = efave.isong) AS favecount, (SELECT COUNT(*) FROM eplay WHERE eplay.isong = esong.id) AS playcount FROM eplay LEFT JOIN esong USE INDEX FOR JOIN (`PRIMARY`) ON eplay.isong = esong.id ORDER BY eplay.dt DESC LIMIT ".$limit.";");
}
$num = mysql_num_rows($lps);
$i = 0;

$site_lps = array();

while($i < $num) {
	
	$timestamp = strtotime(mysql_result($lps, $i, "lastplayed"));
	if(time() - $timestamp > 24 * 3600) {
		$days = (int)((time() - $timestamp) / (24 * 3600));
		$time = $days . " days ago";
		if($days == 1)
			$time = "1 day ago";
	}
	else {
		$time = $timestamp;
	}
	$title = mysql_result($lps, $i, "metadata");
	$playcount = mysql_result($lps, $i, "playcount");
	/*
	if($title == "Seira Kagami - Super Special" || $title == "Kagami Seira - Super Special"){
		$playcount = "&#8734;";
	}
        */  // one-character comments are fun (the asterisk above)
	$pc_str = $playcount;
	
	$favestring = mysql_result($lps, $i, "favecount");
	if($favestring == 0)
		$fave_str = "None";
	else
		$fave_str = $favestring;
	
	
	$lprow = Array($time, htmlentities($title, ENT_NOQUOTES, 'UTF-8'), $pc_str, $fave_str);
	$site_lps[] = $lprow;
	
	$i = $i + 1;
}

mysql_close();

if (isset($_GET["callback"])) {
	$callback = $_GET["callback"];
	echo $callback . '(' . json_encode($site_lps) . ');';
} else {
	echo json_encode($site_lps);
}
?>
