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

	$tablerows = "";

	while($i < $num) {
		
		$timestamp = strtotime(mysql_result($lps, $i, "lastplayed"));
		if(time() - $timestamp > 24 * 3600) {
			$days = (int)((time() - $timestamp) / (24 * 3600));
			$time = $days . " days ago";
			if($days == 1)
				$time = "1 day ago";
		}
		else {
			$time = date("H:i:s", $timestamp);
		}
		$title = mysql_result($lps, $i, "metadata");
		$playcount = mysql_result($lps, $i, "playcount");
		if($title == "Seira Kagami - Super Special" || $title == "Kagami Seira - Super Special"){
			$playcount = "&#8734;";
		}
		
		$pc_str = $playcount;
		if($playcount == 1)
			$pc_str = $playcount;
		
		$favestring = mysql_result($lps, $i, "favecount");
		if($favestring == 0 || is_null($favestring))
			$fave_str = "None";
		else
			$fave_str = $favestring;
		
		
		$tablerows = $tablerows . "<tr><th>$time</th><td>$title</td><td>$pc_str</td><td>$fave_str</td>";
		
		$i = $i + 1;
	}

	mysql_close();
	
	$display = "";
	$display_error = ' style="display: none;"';
	if ($tablerows == "") {
		$display = ' style="display: none;"';
		$display_error = ' style="display: visible;"';
		// stay frosty r/a/dio devs
	}
	
	echo <<<OUTPUT
		<div class="alert-message info"$display_error>
			<p>There is currently no last played information available.</p>
		</div>
		<div class="row"$display>
			<div class="span16">
				<table class="zebra-striped">
					<thead>
						<tr>
							<th><a href="/lastplayed/">Time</a></th>
							<th class="head">Last played</th>
							<th><a href="/lastplayed/?sort=play">Playcount</a></th>
							<th><a href="/lastplayed/?sort=fave">Favorites</a></th>
						</tr>
					</thead>
					<tbody>
						$tablerows
					</tbody>
				</table>
			</div>
		</div>
		<a id="paginp" onclick="lastplayed_page_inc(-1);">&lt;<br /><span id="paginpn">x</span></a>
		<a id="paginn" onclick="lastplayed_page_inc(+1);">&gt;<br /><span id="paginnn">2</span></a>
OUTPUT;
?>
