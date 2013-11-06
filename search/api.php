<?php
include_once("../res/common.php");
$page_count = 15;
//pageView("search", $_SERVER['QUERY_STRING'], gethostbyaddr($_SERVER['REMOTE_ADDR']), $_SESSION['user']);

if(isset($_GET['query']) && trim($_GET['query']) != "") {
	$search = trim($_GET['query']);
}
if(isset($search) && isset($_GET['page']) && ctype_digit($_GET['page'])) {
	$page = $_GET['page'];
}
else if(isset($search)) {
	$page = 1;
}
else {
	$page = 1;
}


if (isset($_GET["callback"])) {
    $callback = $_GET['callback'];
} else {
    $callback = false;
}

if(isset($search)) {
	$searchval = htmlspecialchars($search);	
}
else {
	$searchval = "";
}
$searchresults = "";
$result = array();
$table_rows = array();
mysql_connect($dbip, $dbuser, $dbpass);

@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");

if (isset($search)) {
	
	$m = FALSE;
	preg_match("/^alphanum:([A-Z#])$/i", $search, $m);
	if($m && count($m) === 2) {
		$regex = '^[' . strtoupper($m[1]) . ']';
		if($m[1] === "#") {
			$regex = "^[^A-Z]";
		}
		$searchresults = mysql_safe("SELECT * FROM `tracks` WHERE `usable`='1' AND `need_reupload`=0 AND (`track` REGEXP ?) ORDER BY `track` asc;", array($regex));		
	}
	else {
		$exploded = explode(" ", $search);
		$i = 0;
		while($i < count($exploded)) {
			$exploded[$i] = str_replace(array("(", ")", "\"", "*"), array("", "", "", ""), $exploded[$i]);
			while(substr($exploded[$i], 0, 1) == "+" ||
				  substr($exploded[$i], 0, 1) == "-" ||
				  substr($exploded[$i], 0, 1) == ">" ||
				  substr($exploded[$i], 0, 1) == "<" ||
				  substr($exploded[$i], 0, 1) == "~") {
				$exploded[$i] = substr($exploded[$i], 1);
			}

			$exploded[$i] = "+" . $exploded[$i];
			$i = $i + 1;
		}

		$collected = implode(" ", $exploded);

		$searchresults = mysql_safe("SELECT * FROM `tracks` WHERE `usable`='1' AND `need_reupload`=0 AND MATCH (tags, artist, track, album) AGAINST (? IN BOOLEAN MODE) ORDER BY priority DESC, MATCH (tags, artist, track, album) AGAINST (?) DESC;", array($collected, $search));
	}
		
	$rescount = mysql_num_rows($searchresults);
	
	if(isset($_GET['update']) && $_GET['update'] == '1' && $rescount != 0) {
		$cum_prio = 0;
		for($i = 0;$i < $rescount; $i++)
			$cum_prio = $cum_prio + mysql_result($searchresults, $i, "priority");
		
		$div_prio = 0;
		if($rescount > 0)
			$div_prio = (int)($cum_prio / $rescount * 100);
		
		$search_log = mysql_real_escape_string(trim($search));
		mysql_query("INSERT INTO `searchlog` (search, cumulative_prio, divided_prio, res_count, time, ip) VALUES ('$search_log', $cum_prio, $div_prio, $rescount, NOW(), '$REMOTE_ADDR');");
	}
	$pagecount = ceil($rescount / $page_count);

	$resstart = ($page - 1) * $page_count;
}
else {
	$searchresults = mysql_safe("SELECT * FROM tracks WHERE `usable`=1 AND `need_reupload`=0 ORDER BY id DESC LIMIT 15;");
	
	$rescount = mysql_num_rows($searchresults);
	$pagecount = 1;
	$resstart = 0;
}

$count = 0;

$afk = mysql_query("SELECT `isafkstream` FROM `streamstatus`;");
if(mysql_num_rows($afk) > 0) {
	if(mysql_result($afk, 0, "isafkstream") == 1) {
		$canrequest_afk = TRUE;
	}
	else {
		$canrequest_afk = FALSE;
	}
}
else {
	$canrequest_afk = FALSE;
}

$ip_lr = mysql_query("SELECT * FROM `requesttime` WHERE `ip`='$REMOTE_ADDR' LIMIT 1;");
if(mysql_num_rows($ip_lr) >= 1) {
	$iptime = strtotime(mysql_result($ip_lr, 0, "time"));
}
else {
	$iptime = 0;
}
$now = time();

if($now - $iptime > $ip_req_delay) {
	$canrequest_ip = TRUE;
}
else {
	$canrequest_ip = FALSE;
}

$table_rows = "";

while($count < $page_count && $resstart < $rescount) {
	$songid = mysql_result($searchresults, $resstart, "id");
	$track = mysql_result($searchresults, $resstart, "track");
	$artist = mysql_result($searchresults, $resstart, "artist");
	
	$lastplayed = "Never";
	$lastrequested = "Never";
	if(strtotime(mysql_result($searchresults, $resstart, "lastplayed")) > 0)
		$lastplayed = strtotime(mysql_result($searchresults, $resstart, "lastplayed"));
	if(strtotime(mysql_result($searchresults, $resstart, "lastrequested")) > 0)
		$lastrequested = strtotime(mysql_result($searchresults, $resstart, "lastrequested"));
	
	$lptime = strtotime(mysql_result($searchresults, $resstart, "lastrequested"));
	if(!is_numeric($lptime))
		$lptime = 0;
	
	$priority = (int)mysql_result($searchresults, $resstart, "requestcount");
	
	if($now - $lptime > song_delay($priority)) {
		$canrequest_song = TRUE;
	}
	else {
		$canrequest_song = FALSE;
	}
	
	$lptime = strtotime(mysql_result($searchresults, $resstart, "lastplayed"));
	if(!is_numeric($lptime))
		$lptime = 0;

	if($now - $lptime > song_delay($priority)) {
		$canrequest_song = $canrequest_song && TRUE;
	}
	else {
		$canrequest_song = FALSE;
	}

	
	if($canrequest_afk && $canrequest_ip && $canrequest_song) {
		$disable = FALSE;
	}
	else {
		$disable = TRUE;
	}

	$tablerow = Array($artist, $track, $lastplayed, $lastrequested, $songid, $disable);
	
	$table_rows[] = $tablerow;
	
	$count = $count + 1;
	$resstart = $resstart + 1;
}
if (!$canrequest_afk) {
	$status = False;
	$cooldown = "The AFK Streamer is currently not streaming.";
}
elseif (!$canrequest_ip) {
	$status = False;
	$wait = secs_to_h(abs(($now - $iptime) - $ip_req_delay));
	$cooldown = "You have to wait another $wait before requesting again. (Updates every 2 minutes)";
}
else {
	$status = True;
	$cooldown = "You can request a song.";
}
$result['status'] = $status;
$result['cooldown'] = $cooldown;
$result['result'] = $table_rows;
$result['pages'] = $pagecount;
$result['page'] = $page;
mysql_close();

if ($callback) {
    echo htmlspecialchars($callback) . '(' . htmlentities(json_encode($result), ENT_NOQUOTES, 'UTF-8') . ');';
} else {
    echo json_encode($result);
}

?>
