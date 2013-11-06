<?php
include_once("../res/common.php");
include_once("adminc.php");

if($_SESSION['login'] == 0) {
	header("Location: /x_admin");
	return;
}
if($_SESSION['privileges'] < 1) {
	header("Location: /x_admin/main.php");
	return;
}

header("Cache-Control: no-cache");

//print_r($_POST);

if(isset($_POST['subbut'])) {
	if($_POST['subbut'] == "Accept") {		
		mysql_connect($dbip, $dbuser, $dbpass);
		@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
		
		
		$pendid = mysql_real_escape_string($_POST['pendid']);
		if(isset($_POST['good']) && $_POST['good'] === "on")
			$good = '1';
		else
			$good = '0';
		$penddata = mysql_query("SELECT * FROM `pending` WHERE `id`='$pendid';");
		
		
		
		if(mysql_num_rows($penddata) > 0) {
			if(!is_null(mysql_result($penddata, 0, "replacement"))) {
				$trackid = mysql_result($penddata, 0, "replacement");
			}
			if(isset($_POST['force_replace']) && ctype_digit($_POST['force_replace'])) {
				$x = $_POST['force_replace'];
				$testing = mysql_query("SELECT id FROM `tracks` WHERE `id`=$x;");
				if(mysql_num_rows($testing) > 0) {
					$trackid = (int)$x;
				}
			}
			if(isset($trackid)) {
				$trackdata = mysql_query("SELECT * FROM `tracks` WHERE `id`=$trackid;");
				if(mysql_num_rows($trackdata) > 0) {
					$path = mysql_result($trackdata, 0, "path");
					$curlocation = "/home/www/r-a-dio.com/res/music/pend/" . mysql_result($penddata, 0, "path");
					$newlocation = "/home/www/r-a-dio.com/res/music/" . $path;					
					rename($curlocation, $newlocation);
					
					$artist = mysql_real_escape_string(mysql_result($trackdata, 0, "artist"));
					$track = mysql_real_escape_string(mysql_result($trackdata, 0, "track"));
					
					mysql_query("UPDATE `tracks` SET usable=0 WHERE `id`='$trackid';");
					$subip = mysql_real_escape_string(mysql_result($penddata, 0, "submitter"));
					mysql_query("INSERT INTO `postpending` (trackid, meta, ip, accepted, time, good_upload) VALUES ($trackid, TRIM(IF('$artist' <> '', CONCAT_WS(' - ', '$artist', '$track'), '$track')), '$subip', 2, NOW(), '$good');");
					mysql_query("DELETE FROM `pending` WHERE `id`='$pendid';");
				}				
			}
			else {
				$artist = mysql_real_escape_string($_POST['artist']);
				$track = mysql_real_escape_string($_POST['title']);
				$album = mysql_real_escape_string($_POST['album']);
				$tags = mysql_real_escape_string($_POST['tags']);
				
				$curlocation = "/home/www/r-a-dio.com/res/music/pend/" . mysql_result($penddata, 0, "path");
				$filename = basename($curlocation);
				$newlocation = "/home/www/r-a-dio.com/res/music/" . $filename;				
				rename($curlocation, $newlocation);
				
				$newlocation = mysql_real_escape_string($filename);
				$accept = mysql_real_escape_string($_SESSION['user']);
				
				mysql_query("INSERT INTO `tracks` (hash, artist, track, album, path, tags, accepter) VALUES (SHA1(LOWER(TRIM(IF('$artist' <> '', CONCAT_WS(' - ', '$artist', '$track'), '$track')))), '$artist', '$track', '$album', '$newlocation', '$tags', '$accept');");
				$idq = mysql_query("SELECT `id` FROM `tracks` WHERE `path`='$newlocation';");
				$tid = mysql_result($idq, 0, "id");
				$subip = mysql_real_escape_string(mysql_result($penddata, 0, "submitter"));
				mysql_query("INSERT INTO `postpending` (trackid, meta, ip, accepted, time, good_upload) VALUES ($tid, TRIM(IF('$artist' <> '', CONCAT_WS(' - ', '$artist', '$track'), '$track')), '$subip', 1, NOW(), $good);");
				mysql_query("DELETE FROM `pending` WHERE `id`='$pendid';");
			}
		}
		
		
		mysql_close();
		header("Location: /x_admin/pending.php");
		return;
	}
	else if($_POST['subbut'] == "Decline") {
		mysql_connect($dbip, $dbuser, $dbpass);
		@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
		
		
		
		$pendid = mysql_real_escape_string($_POST['pendid']);
		$reason = mysql_real_escape_string($_POST['reason']);
		$pending = mysql_query("SELECT `artist`, `track`, `path`, `submitter`, `origname`, `replacement` FROM `pending` WHERE `id`='$pendid';");
		if(mysql_num_rows($pending) > 0) {
			$path = "/home/www/r-a-dio.com/res/music/pend/" . mysql_result($pending, 0, "path");
			unlink($path);
			$meta = mysql_real_escape_string(mysql_result($pending, 0, "artist") === "" ? mysql_result($pending, 0, "track") : mysql_result($pending, 0, "artist") . " - " . mysql_result($pending, 0, "track"));
			if($meta === "") {
				$meta = mysql_real_escape_string(mysql_result($pending, 0, "origname"));
			}
			$subip = mysql_real_escape_string(mysql_result($pending, 0, "submitter"));
			mysql_query("INSERT INTO `postpending` (trackid, meta, ip, accepted, time, reason) VALUES (NULL, '$meta', '$subip', '0', NOW(), '$reason');");
			$replace = mysql_result($pending, 0, "replacement");
			if(!is_null($replace))
				mysql_query("UPDATE `tracks` SET `need_reupload`=1 WHERE `id`=$replace;");
		}
		
		mysql_query("DELETE FROM `pending` WHERE `id`='$pendid';");
		
		mysql_close();
		header("Location: /x_admin/pending.php");
		return;
	}	
}

$table_rows = "";

mysql_connect($dbip, $dbuser, $dbpass);
@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");



$pending = mysql_query("SELECT * FROM `tracks` LIMIT 50;");
$num = mysql_num_rows($pending);
$i = 0;

while($i < $num) {
	$id = mysql_result($pending, $i, "id");
	$artist = htmlspecialchars(mysql_result($pending, $i, "artist"));
	$track = htmlspecialchars(mysql_result($pending, $i, "track"));
	$album = htmlspecialchars(mysql_result($pending, $i, "album"));
	$tags = htmlspecialchars(mysql_result($pending, $i, "tags"));
	//$linkpath = relativate($_SERVER['DOCUMENT_ROOT'], mysql_result($pending, $i, "path"));
	//if(substr($linkpath, 0, 1) != "/") 
	//	$linkpath = "/".$linkpath;
	$linkpath = "/res/music/" . mysql_result($pending, $i, "path");
	//$comment = htmlspecialchars(mysql_result($pending, $i, "comment"));
	//$origname_ = htmlspecialchars(mysql_result($pending, $i, "origname"));
	//$submitter = mysql_result($pending, $i, "submitter");
	//$submitted = date("Y-m-d H:i T", strtotime(mysql_result($pending, $i, "submitted")));
	//$filesize = filesize("/home/www/r-a-dio.com" . $linkpath);
	//$size_str = round($filesize / 1048576, 1) . "MB";
	//if($filesize > 15728640) {
	//	$size_str = '<span style="color:red;font-weight:bold">' . $size_str . '</span>';
	//}
	//$size_str = '<a href="http://r-a-d.io' .$linkpath. '">' .$size_str .'</a>';
	//$dupe_flag = mysql_result($pending, $i, "dupe_flag");
	//$origname = $origname_;
	//if($dupe_flag == 1)
	//	$origname = '<span style="color:red;font-weight:bold">' . $origname_ . '</span>';
	
	//$substats = mysql_query("SELECT COUNT(*) as c FROM `postpending` WHERE accepted=1 AND good_upload=1 AND ip='$submitter';");
	//$good_accepts = mysql_result($substats, 0, "c");
	//$substats = mysql_query("SELECT COUNT(*) as c FROM `postpending` WHERE accepted=0 AND ip='$submitter';");
	//$declines = mysql_result($substats, 0, "c");
	//$submitter .= "<br>";
	//if($good_accepts > 0) {
	//	$submitter .= " (<span style=\"color:#008000\">$good_accepts</span>)";
	//}
	//if($declines > 0) {
	//	$submitter .= " (<span style=\"color:#800000\">$declines</span>)";
	//}
	//$replacement = mysql_result($pending, $i, "replacement");
	//if(!is_null($replacement)) {
	//	$repl = mysql_query("SELECT * FROM `tracks` WHERE `id`=$replacement;");
	//	if(mysql_num_rows($repl) > 0) {
	//		$replartist = htmlspecialchars(mysql_result($repl, 0, "artist"));
	//		$repltrack = htmlspecialchars(mysql_result($repl, 0, "track"));
	//		$replmeta = $replartist . " - " . $repltrack;
	//		if($replartist === "")
	//			$replmeta = $repltrack;
	//		$origname = '<span style="color:#00AAAA;font-weight:bold">' . $origname_ . " (Replaces " . $replmeta . ')</span>';
	//	}		
	//}
	
	$table_row = <<<TABLEROW
			<form action="" method="POST">
			<tr>
				<td colspan="5"><input type="text" value="$tags" name="tags" maxlength="200" /></td>
			</tr>
			<tr class="fuckpadding">
				<input type="hidden" name="pendid" value="$id" />
				<td><input type="text" value="$artist" name="artist" maxlength="200" /></td>
				<td><input type="text" value="$track" name="title" maxlength="200" /></td>
				<td><input type="text" value="$album" name="album" maxlength="200" /></td>
				<td align="center"><a href="#" class="btn play" value="$linkpath">Play</a></td>
				<td>
					<input class="btn primary" type="submit" name="subbut" value="Accept" /><br>
				</td>
			</tr></form>
TABLEROW;
/*
					<td><ul class="nav nav-pills"><li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Options</a>
				<ul class="dropdown-menu">
					<li><input class="btn primary" type="submit" name="subbut" value="Accept" /></li>
					<li><input class="btn danger" type="submit" name="subbut" value="Decline" /></li>
				</ul></li></ul>
TABLEROW;*/
	$table_rows = $table_rows . $table_row . "\n";
	
	$i = $i + 1;
}




mysql_close();

$site = <<<SITESTR
<html>
	<head>
		<title>Admin</title>
		<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
		<link rel="stylesheet" href="style.css" type="text/css" />
		<link rel="stylesheet" href="/css/bootstrap.min.css" type="text/css" />
		<link rel="stylesheet" href="/css/x_admin/pending.css" type="text/css" />
		<script src="/js/jquery.min.js" type="text/javascript"></script>
		<script src="/js/jquery.jplayer.min.js" type="text/javascript"></script>
		<script src="/js/x_admin/pending.js" type="text/javascript"></script>
		<script src="/js/bootstrap-plugins.js" type="text/javascript"></script>
		<script src="/js/jquery.form.js" type="text/javascript"></script>
		<style>tr.fuckpadding > td { padding-bottom: 40px; }</style>
	</head>
	<body>
		$main_menu
		<br/>
		Tagging left to do:<b> $num</b>
		<br/>
		Please help edit our search tags by adding two apastrophes to words that form a single tag.
		Thus if a track has the tags "tales from earthsea studio ghibli" you need to change it to 
		"tales''from''earthsea studio''ghibli" by grouping the words that form one tag together with
		double apastrophes ('').
		<br/>
		<div id="jPlayer"></div>
		<div class="jp-gui jp-interface" id="jp_container_1">
			<div class="controls">
				<a href="#" class="jp-play btn primary">Play</a>
				<a href="#" class="jp-pause btn">Pause</a>
				<a href="#" class="jp-stop btn">Stop</a>
				<a href="#" class="jp-mute btn">Mute</a>
				<a href="#" class="jp-unmute btn">Unmute</a>
				<div class="jp-volume-bar progress" id="volume-bar">
					<div class="jp-volume-bar-value bar"></div>
				</div>
				<div class="time">
					<span class="jp-current-time"></span>/<span class="jp-duration"></span>
				</div>
			</div>
			<div class="jp-progress progress">
				<div class="jp-seek-bar">
					<div class="jp-play-bar bar"></div>
				</div>
			</div>
		</div>
		<table class="zebra-striped condensed-table" border="0" cellspacing="0" cellpadding="2">
			<tr>
				<th style="min-width: 150px;">Artist</th>
				<th style="min-width: 150px;">Title</th>
				<th style="min-width: 150px;">Album</th>
				<th width="60px">File</th>
				<th width="90px">Accept</th>
			</tr>
$table_rows
			<tr><td>dicks</td></tr>
		</table>
		<!-- <script> // oh hi
			function yeahdude(para)
			{
				var e = $(para);
				e.addClass('slider')
				.css('color', '#fff')
				//.css('position', 'relative')
				.css('text-shadow', '0 1px 0 #000');
				//.css('transition', 'all 2.4s ease-in-out')
				//.css('-o-transition', 'all 2.4s ease-in-out')
				//.css('-moz-transition', 'all 2.4s ease-in-out')
				//.css('-webkit-transition', 'all 2.4s ease-in-out')
				//.animate({
				//	'top' : '-10em',
				//	'left' : '-10em',
				//	'font-size' : '3em'
				//}, 3000);
				//$('body').append('<style>body:hover .slider{top:-10em;left:-10em;font-size:3em}</style>');
			}
			$('.primary').click(function(){
				var row = $(this).first().parent().parent();
				var id = row.find('[name="pendid"]').attr('value');
				row.find('input').each(
					function (index, para) {
						$(para).addClass('partyanimal');
						$(para).removeClass('btn play primary danger');
						//yeahdude(para);
					}
				);
				row.find('a').each(
					function (index, para) {
						//$(para).addClass('partyanimal');
						$(para).removeClass('btn play primary danger');
						yeahdude(para);
					}
				);
				partyparty();
			});
			var parties;
			function partyparty() {
				parties = $('.partyanimal');
				//parties.length = 4;
				party();
			}
			var partyd = 4; //up
			var partyc = 0; //hue
			var partya = 0; //saturation
			function party() {
				partya += 1 * partyd;
				if (partya > 100)
				{
					partyd = -0.1;
					partya = 100;
				}
				if (partyd < 0 &&
					partya < 70)
				{
					partyd = -0.5;
				}
				if (partya < 0)
				{
					return;
				}
				for (var a=0; a<parties.length; a++) {
					var bg = partyc + a*30;
					if (bg > 360) {
						bg -= 360;
					}
					$(parties[a]).css('background',
						'hsl(' + bg + ',' + partya + '%,' + (100-partya/2) + '%)');
				}
				partyc += 5;
				if (partyc > 360) {
					partyc -= 360;
				}
				setTimeout('party()', 10);
			}
		</script> -->
	</body>
</html>
SITESTR;

echo $site;

?>

