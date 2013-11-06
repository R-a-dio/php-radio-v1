<?php
include_once("../res/common.php");

$page_count = 15;
//pageView("search", $_SERVER['QUERY_STRING'], gethostbyaddr($_SERVER['REMOTE_ADDR']), $_SESSION['user']);
$content = <<<CONTENT
			<div class="alert-message success"><p>Welcome to the search page. Use the search field at the top to search.</p></div>
CONTENT;
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
$searchresults = "";
$result = array();
$table_rows = array();
if(isset($search)) {
	mysql_connect($dbip, $dbuser, $dbpass);

	@mysql_select_db($dbname);
	mysql_query("SET NAMES 'utf8';");
	
	
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

	$searchresults = mysql_safe("SELECT * FROM `tracks` WHERE `usable`='1' AND MATCH (tags, artist, track, album) AGAINST (? IN BOOLEAN MODE) ORDER BY MATCH (tags, artist, track, album) AGAINST (?) DESC;", array($collected, $search));

	$rescount = mysql_num_rows($searchresults);
	
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
	
	$table_rows = "";
	
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
		
		$lptime = strtotime(mysql_result($searchresults, $resstart, "lastrequested"));
		if(!is_numeric($lptime))
			$lptime = 0;
		
		if($now - $lptime > $song_req_delay) {
			$canrequest_song = TRUE;
		}
		else {
			$canrequest_song = FALSE;
		}
		
		$lptime = strtotime(mysql_result($searchresults, $resstart, "lastplayed"));
		if(!is_numeric($lptime))
			$lptime = 0;

		if($now - $lptime > $song_req_delay) {
			$canrequest_song = $canrequest_song && TRUE;
		}
		else {
			$canrequest_song = FALSE;
		}

		
		if($canrequest_afk && $canrequest_ip && $canrequest_song) {
			$disable = FALSE;
			$disable_n = "";
		}
		else {
			$disable = TRUE;
			$disable_n = 'disabled="disabled"';
		}
		if ($artist != "") {
			$artist = $artist . " -";
		}
		$tablerow = <<<TABLEROW
					<tr><td>$songid</td><td>$artist <b>$track</b></td><td>$lastplayed</td><td>$lastrequested</td><td><form method="POST" action="/request/" name="requestform"><input type="hidden" value="$songid" name="songid"><input class="btn info" type="submit" $disable_n value="Request"></form></td></tr>
TABLEROW;
		$table_rows[] = $tablerow;
		
		$count = $count + 1;
		$resstart = $resstart + 1;
		mysql_close();
	}
	$tablebody = implode($table_rows, "");

	$content = <<<TABLE
			<table class="zebra-striped bordered-table condensed-table" id="results">
				<thead>
					<tr>
						<th class="h-songid">ID</th>
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
			<div class="pagination">
				<ul>
					$pagination
				</ul>
			</div>
TABLE;
}
include("../templates/header.php");
echo '<div class="container page" id="page-p-search">';
echo $content;
echo '</div>';
include("../templates/footer.php");
?>
