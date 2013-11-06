<?php
include_once("../res/common.php");

pageView("lastplayed", $_SERVER['QUERY_STRING'], gethostbyaddr($_SERVER['REMOTE_ADDR']), $_SESSION['user']);

mysql_connect($dbip, $dbuser, $dbpass);

@mysql_select_db($dbname);

mysql_query("SET NAMES 'utf8';");

if(isset($_GET['page']) && $_GET['page'] == "mostfave") {
	$lps = mysql_query("SELECT * FROM `streamsongs` ORDER BY LENGTH(fave) - (LENGTH(REPLACE(fave, '!', ''))) DESC LIMIT 50;");
	$num = mysql_num_rows($lps);
	$i = 0;

	$site_lps = "";

	while($i < $num) {
		
		$timestamp = mysql_result($lps, $i, "lastplayed");
		if(time() - $timestamp > 24 * 3600) {
			$days = (int)((time() - $timestamp) / (24 * 3600));
			$time = $days . " days ago";
			if($days == 1)
				$time = "1 day ago";
		}
		else {
			$time = date("H:i:s", $timestamp);
		}
		$title = htmlspecialchars(mysql_result($lps, $i, "title"));
		$playcount = mysql_result($lps, $i, "playcount");
		$pc_str = $playcount . " times";
		if($playcount == 1)
			$pc_str = $playcount . " time";
		
		$favestring = mysql_result($lps, $i, "fave");
		if(strlen($favestring) > 0 && substr($favestring, 0, 1) == "!")
			$favestring = substr($favestring, 1);
		if($favestring == "")
			$fave_str = "None";
		else {
			$favecount = substr_count($favestring, "!") + 1;
			$fave_str = $favecount . " faves";
			if($favecount == 1)
				$fave_str = $favecount . " fave";
		}
		
		
		$lprow = <<<LPROW
							<tr>
								<td>$time</td>
								<td>$title</td>
								<td>$pc_str</td>
								<td>$fave_str</td>
							</tr>
LPROW;
		
		$site_lps = $site_lps . $lprow . "\n";
		
		$i = $i + 1;
	}
}
else if(isset($_GET['page']) && $_GET['page'] == "mostplay") {
	$lps = mysql_query("SELECT * FROM `streamsongs` WHERE `title`!='' ORDER BY playcount DESC LIMIT 50;");
	$num = mysql_num_rows($lps);
	$i = 0;

	$site_lps = "";

	while($i < $num) {
		
		$timestamp = mysql_result($lps, $i, "lastplayed");
		if(time() - $timestamp > 24 * 3600) {
			$days = (int)((time() - $timestamp) / (24 * 3600));
			$time = $days . " days ago";
			if($days == 1)
				$time = "1 day ago";
		}
		else {
			$time = date("H:i:s", $timestamp);
		}
		$title = htmlspecialchars(mysql_result($lps, $i, "title"));
		$playcount = mysql_result($lps, $i, "playcount");
		$pc_str = $playcount . " times";
		if($playcount == 1)
			$pc_str = $playcount . " time";
		
		$favestring = mysql_result($lps, $i, "fave");
		if(strlen($favestring) > 0 && substr($favestring, 0, 1) == "!")
			$favestring = substr($favestring, 1);
		if($favestring == "")
			$fave_str = "None";
		else {
			$favecount = substr_count($favestring, "!") + 1;
			$fave_str = $favecount . " faves";
			if($favecount == 1)
				$fave_str = $favecount . " fave";
		}
		
		
		$lprow = <<<LPROW
							<tr>
								<td>$time</td>
								<td>$title</td>
								<td>$pc_str</td>
								<td>$fave_str</td>
							</tr>
LPROW;
		
		$site_lps = $site_lps . $lprow . "\n";
		
		$i = $i + 1;
	}
}
else {
	$lps = mysql_query("SELECT * FROM `streamsongs` ORDER BY lastplayed DESC LIMIT 100;");
	$num = mysql_num_rows($lps);
	$i = 0;

	$site_lps = "";

	while($i < $num) {
		
		$time = date("H:i:s", mysql_result($lps, $i, "lastplayed"));
		$title = htmlspecialchars(mysql_result($lps, $i, "title"));
		$playcount = mysql_result($lps, $i, "playcount");
		$pc_str = $playcount . " times";
		if($playcount == 1)
			$pc_str = $playcount . " time";
		
		$favestring = mysql_result($lps, $i, "fave");
		if(strlen($favestring) > 0 && substr($favestring, 0, 1) == "!")
			$favestring = substr($favestring, 1);
		if($favestring == "")
			$fave_str = "None";
		else {
			$favecount = substr_count($favestring, "!") + 1;
			$fave_str = $favecount . " faves";
			if($favecount == 1)
				$fave_str = $favecount . " fave";
		}
		
		
		$lprow = <<<LPROW
							<tr>
								<td>$time</td>
								<td>$title</td>
								<td>$pc_str</td>
								<td>$fave_str</td>
							</tr>
LPROW;
		
		$site_lps = $site_lps . $lprow . "\n";
		
		$i = $i + 1;
	}

}

mysql_close();

$site = <<<SITESTR
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>r/a/dio</title>
		<link rel="stylesheet" href="../res/style.css" type="text/css" />
		<link rel="stylesheet" href="../res/nav.css" type="text/css" />
		<link rel="shortcut icon" type="image/vnd.microsoft.icon" href="/favicon.ico" />
		<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	</head>
	<body>
		<div id="mainbox">
$site_menu
			<div id="contentbox">
				<div id="upper">
					<h1 style="padding-bottom:10px;">Last played songs</h1>
					<div>Here you can view various information about the songs that have played on r/a/dio; the last played songs, the most played songs, and the most favorited songs. Times are in UTC.</div>
					<div style="margin-top:5px"><a style="color:#DDDDDD" href="/lastplayed/">Last played</a> | <a style="color:#DDDDDD" href="/lastplayed/?page=mostplay">Most played</a> | <a style="color:#DDDDDD" href="/lastplayed/?page=mostfave">Most favorited</a></div>
				</div>
				<div id="lower">
					<table class="alttbl" width="100%" cellspacing="1" cellpadding="0" border="0">
						<tr>
							<td align="center" width="90px"><b>Time</b></td>
							<td align="center"><b>Title</b></td>
							<td align="center" width="100px"><b>Playcount</b></td>
							<td align="center" width="100px"><b>IRC faves</b></td>
						</tr>
$site_lps
					</table>
				</div>
			</div>
		</div>
	</body>
</html>
SITESTR;

echo $site;
?>