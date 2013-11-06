<?php
include_once("../res/common.php");

mysql_connect($dbip, $dbuser, $dbpass);

@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
if(isset($_GET['query']) && trim($_GET['query']) != "") {
	$search = trim($_GET['query']);
}
if(isset($search) && isset($_GET['page']) && ctype_digit($_GET['page'])) {
	$page = $_GET['page'];
}
else if(isset($search)) {
	$page = 1;
}
if(isset($search)) {
	$searchval = htmlspecialchars($search);	
}
else {
	$searchval = "";
}
$canrequest_afk = FALSE;
$canrequest_ip = FALSE;
$iptime = 0;
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
$request_result = $status ? "success" : "danger";
$page_count = 15;
//pageView("search", $_SERVER['QUERY_STRING'], gethostbyaddr($_SERVER['REMOTE_ADDR']), $_SESSION['user']);

// Some testing here
$tablebody = "Testing was here";
$table_rows = Array();
if (!isset($search)) {
	mysql_connect($dbip, $dbuser, $dbpass);

	@mysql_select_db($dbname);
	mysql_query("SET NAMES 'utf8';");
	
	$searchresults = mysql_safe("SELECT * FROM tracks WHERE `usable`=1 AND `need_reupload`=0 ORDER BY id DESC LIMIT 15;");
	
	$rescount = mysql_num_rows($searchresults);
	
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
	$resstart = 0;
	while($resstart < $rescount) {
		$songid = mysql_result($searchresults, $resstart, "id");
		$track = mysql_result($searchresults, $resstart, "track");
		$artist = mysql_result($searchresults, $resstart, "artist");
		
		$lastplayed = "Never";
		$lastrequested = "Never";
		if(strtotime(mysql_result($searchresults, $resstart, "lastplayed")) > 0)
			$lastplayed = date("D j M, H:i", strtotime(mysql_result($searchresults, $resstart, "lastplayed")));
		if(strtotime(mysql_result($searchresults, $resstart, "lastrequested")) > 0)
			$lastrequested = date("D j M, H:i", strtotime(mysql_result($searchresults, $resstart, "lastrequested")));
		
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
			$disable_n = "";
			$btype = "info";
		}
		else {
			$disable_n = 'disabled';
			$btype = "warning";
		}
		if ($artist != "") {
			$artist = $artist . " -";
		}
		$tablerow = <<<TABLEROW
					<tr><td>$artist <b>$track</b></td><td>$lastplayed</td><td>$lastrequested</td><td><button $disable_n class="btn $btype" value="$songid">Request</button></td></tr>
TABLEROW;
		$table_rows[] = $tablerow;
		
		$resstart = $resstart + 1;
	}
	$tablebody = implode($table_rows, "");
	mysql_close();
}


$content = <<<CONTENT
			<div id="search_welcome" class="alert-message success"><p>Welcome to the search page. Use the search field at the top to search.</p></div>
			<div id="request_status" class="alert-message $request_result"><p>$cooldown</p></div>
			<div class="pagination alphanum">
				<ul>
					<li><span>Alphanum index:</span></li>
					<li><a href="#/search/1/alphanum:A">A</a></li>
					<li><a href="#/search/1/alphanum:B">B</a></li>
					<li><a href="#/search/1/alphanum:C">C</a></li>
					<li><a href="#/search/1/alphanum:D">D</a></li>
					<li><a href="#/search/1/alphanum:E">E</a></li>
					<li><a href="#/search/1/alphanum:F">F</a></li>
					<li><a href="#/search/1/alphanum:G">G</a></li>
					<li><a href="#/search/1/alphanum:H">H</a></li>
					<li><a href="#/search/1/alphanum:I">I</a></li>
					<li><a href="#/search/1/alphanum:J">J</a></li>
					<li><a href="#/search/1/alphanum:K">K</a></li>
					<li><a href="#/search/1/alphanum:L">L</a></li>
					<li><a href="#/search/1/alphanum:M">M</a></li>
					<li><a href="#/search/1/alphanum:N">N</a></li>
					<li><a href="#/search/1/alphanum:O">O</a></li>
					<li><a href="#/search/1/alphanum:P">P</a></li>
					<li><a href="#/search/1/alphanum:Q">Q</a></li>
					<li><a href="#/search/1/alphanum:R">R</a></li>
					<li><a href="#/search/1/alphanum:S">S</a></li>
					<li><a href="#/search/1/alphanum:T">T</a></li>
					<li><a href="#/search/1/alphanum:U">U</a></li>
					<li><a href="#/search/1/alphanum:V">V</a></li>
					<li><a href="#/search/1/alphanum:W">W</a></li>
					<li><a href="#/search/1/alphanum:X">X</a></li>
					<li><a href="#/search/1/alphanum:Y">Y</a></li>
					<li><a href="#/search/1/alphanum:Z">Z</a></li>
					<li><a href="#/search/1/alphanum:#">#</a></li>
				</ul>
			</div>
			<table class="zebra-striped bordered-table condensed-table" id="results">
				<thead>
					<tr>
						<th class="h-info">Artist - Title</th>
						<th class="h-last">Last played</th>
						<th class="h-last">Last requested</th>
						<th class="h-request">Request</th>
					</tr>
				</thead>
				<tbody>
					$tablebody
				</tbody>
			</table>
			<div class="pagination page-browse">
				<ul>
				</ul>
			</div>
			<div id="request_info" class="fade modal" style="display: none;" data-backdrop="true" data-keyboard="true">
				<div class="modal-header">
					<a class="close" href="#">×</a>
					<h3>Request</h3>
				</div>
				<div class="modal-body">
				
				</div>
			</div>
CONTENT;
$searchresults = "";
$result = array();
$table_rows = array();
if(isset($search)) {
	mysql_connect($dbip, $dbuser, $dbpass);

	@mysql_select_db($dbname);
	mysql_query("SET NAMES 'utf8';");
	
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
	
	if($rescount != 0) {
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
	
	$table_rows = array();
	
	while($count < $page_count && $resstart < $rescount) {
		$songid = mysql_result($searchresults, $resstart, "id");
		$track = mysql_result($searchresults, $resstart, "track");
		$artist = mysql_result($searchresults, $resstart, "artist");
		
		$lastplayed = "Never";
		$lastrequested = "Never";
		if(strtotime(mysql_result($searchresults, $resstart, "lastplayed")) > 0)
			$lastplayed = date("D j M, H:i", strtotime(mysql_result($searchresults, $resstart, "lastplayed")));
		if(strtotime(mysql_result($searchresults, $resstart, "lastrequested")) > 0)
			$lastrequested = date("D j M, H:i", strtotime(mysql_result($searchresults, $resstart, "lastrequested")));
		
		$priority = (int)mysql_result($searchresults, $resstart, "requestcount");
		
		$lptime = strtotime(mysql_result($searchresults, $resstart, "lastrequested"));
		if(!is_numeric($lptime))
			$lptime = 0;
		
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
			$disable_n = "";
			$btype = "info";
		}
		else {
			$disable_n = 'disabled';
			$btype = "warning";
		}
		if ($artist != "") {
			$artist = $artist . " -";
		}
		$tablerow = <<<TABLEROW
					<tr><td>$artist <b>$track</b></td><td>$lastplayed</td><td>$lastrequested</td><td><button $disable_n class="btn $btype" value="$songid">Request</button></td></tr>
TABLEROW;
		$table_rows[] = $tablerow;
		
		$count = $count + 1;
		$resstart = $resstart + 1;
	}
	$tablebody = implode($table_rows, "");
	mysql_close();
	$content = <<<TABLE
			<div id="request_status" class="alert-message $request_result"><p>$cooldown</p></div>
			<div class="pagination alphanum">
				<ul>
					<li><span>Alphanum index:</span></li>
					<li><a href="#/search/1/alphanum:A">A</a></li>
					<li><a href="#/search/1/alphanum:B">B</a></li>
					<li><a href="#/search/1/alphanum:C">C</a></li>
					<li><a href="#/search/1/alphanum:D">D</a></li>
					<li><a href="#/search/1/alphanum:E">E</a></li>
					<li><a href="#/search/1/alphanum:F">F</a></li>
					<li><a href="#/search/1/alphanum:G">G</a></li>
					<li><a href="#/search/1/alphanum:H">H</a></li>
					<li><a href="#/search/1/alphanum:I">I</a></li>
					<li><a href="#/search/1/alphanum:J">J</a></li>
					<li><a href="#/search/1/alphanum:K">K</a></li>
					<li><a href="#/search/1/alphanum:L">L</a></li>
					<li><a href="#/search/1/alphanum:M">M</a></li>
					<li><a href="#/search/1/alphanum:N">N</a></li>
					<li><a href="#/search/1/alphanum:O">O</a></li>
					<li><a href="#/search/1/alphanum:P">P</a></li>
					<li><a href="#/search/1/alphanum:Q">Q</a></li>
					<li><a href="#/search/1/alphanum:R">R</a></li>
					<li><a href="#/search/1/alphanum:S">S</a></li>
					<li><a href="#/search/1/alphanum:T">T</a></li>
					<li><a href="#/search/1/alphanum:U">U</a></li>
					<li><a href="#/search/1/alphanum:V">V</a></li>
					<li><a href="#/search/1/alphanum:W">W</a></li>
					<li><a href="#/search/1/alphanum:X">X</a></li>
					<li><a href="#/search/1/alphanum:Y">Y</a></li>
					<li><a href="#/search/1/alphanum:Z">Z</a></li>
					<li><a href="#/search/1/alphanum:#">#</a></li>
				</ul>
			</div>
			<table class="zebra-striped bordered-table condensed-table" id="results">
				<thead>
					<tr>
						<th class="h-info">Artist - Title</th>
						<th class="h-last">Last played</th>
						<th class="h-last">Last requested</th>
						<th class="h-request">Request</th>
					</tr>
				</thead>
				<tbody>
					$tablebody
				</tbody>
			</table>
			<div class="pagination page-browse">
				<ul>
				</ul>
			</div>
			<div id="request_info" class="fade modal" style="display: none;" data-backdrop="true" data-keyboard="true">
				<div class="modal-header">
					<a class="close" href="#">×</a>
					<h3>Request</h3>
				</div>
				<div class="modal-body">
				
				</div>
			</div>
TABLE;
}
echo $content;
?>
