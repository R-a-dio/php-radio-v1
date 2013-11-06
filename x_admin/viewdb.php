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

function compress_hex($hash_str) {
    $bin_str = hex2bin($hash_str);
    $b64_str = base64_encode($bin_str);
    return rtrim(str_replace(array("+", "/"), array("-", "_"), $b64_str), "=");
}



if(isset($_POST['subbut'])) {
	if($_POST['subbut'] == "Save") {
		mysql_connect($dbip, $dbuser, $dbpass);
		@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
		
		
		$songid = mysql_real_escape_string(urldecode($_POST['t_id']));
		$artist = mysql_real_escape_string(urldecode($_POST['t_artist']));
		$title = mysql_real_escape_string(urldecode($_POST['t_title']));
		$album = mysql_real_escape_string(urldecode($_POST['t_album']));
		$tags = mysql_real_escape_string(urldecode($_POST['t_tags']));
		$user = mysql_real_escape_string($_SESSION['user']);
		if(isset($_POST['reupload'])) {
			$reupload = '1';
		}
		else {
			$reupload = '0';
		}
		
		mysql_query("UPDATE `tracks` SET `hash`=SHA1(LOWER(TRIM(IF('$artist' <> '', CONCAT_WS(' - ', '$artist', '$title'), '$title')))), `artist`='$artist', `track`='$title', `album`='$album', `tags`='$tags', `lasteditor`='$user', `need_reupload`=$reupload WHERE `id`=$songid;");
		
		$qs = http_build_query($_GET);

		mysql_close();
		header("Location: /x_admin/viewdb.php?$qs#datatable");
		exit();		
	}
	else if($_POST['subbut'] == "Delete") {	
		mysql_connect($dbip, $dbuser, $dbpass);
		@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
		
		if($_SESSION['privileges'] >= 4 || $_SESSION['userid'] == 30) { // what a hack
			
			$songid = mysql_real_escape_string($_POST['t_id']);
			
			$trackdata = mysql_query("SELECT * FROM `tracks` WHERE `id`=$songid;");
			
			unlink("/radio/www/music/" . mysql_result($trackdata, 0, "path"));
			unlink("/radio/www/music/tagged/" . mysql_result($trackdata, 0, "path"));
			mysql_query("DELETE FROM `tracks` WHERE `id`=$songid;");
			
		}
			
		$qs = http_build_query($_GET);
		
		
		mysql_close();
		header("Location: /x_admin/viewdb.php?$qs#datatable");
		exit();
	}
	else if($_POST['subbut'] == "Request" && $_SESSION['privileges'] >= 4) {
		mysql_connect($dbip, $dbuser, $dbpass);
		@mysql_select_db($dbname);
		mysql_query("SET NAMES 'utf8';");
		
		$songid = $_POST['t_id'];
		
		mysql_query("INSERT INTO `requests` (`trackid`, `time`, `ip`) VALUES ($songid, NOW(), '$REMOTE_ADDR');");
		
		$qs = http_build_query($_GET);
		
		mysql_close();
		header("Location: /x_admin/viewdb.php?$qs#datatable");
		exit();
	}
}

$f_s = $ar_s = $t_s = $al_s = $ta_s = $gr_s = $u1 = $u2 = $u3 = $r1 = $r2 = $r3 = $query = $rescount = $result_table = $pagenav = "";

if(isset($_GET['fullsearch'])) {
	$get = $_GET;

	$fullsearch = "";
	$artistsearch = "";
	$titlesearch = "";
	$albumsearch = "";
	$tagssearch = "";
	$usesearch = "";
	$replacesearch = "";
	$greensearch = "";

	if(isset($get['fullsearch'])) {
		$fullsearch = trim($get['fullsearch']);
	}
	if(isset($get['artist'])) {
		$artistsearch = trim($get['artist']);
	}
	if(isset($get['title'])) {
		$titlesearch = trim($get['title']);
	}
	if(isset($get['album'])) {
		$albumsearch = trim($get['album']);
	}
	if(isset($get['tags'])) {
		$tagssearch = trim($get['tags']);
	}
	if(isset($get['usability'])) {
		$usesearch = trim($get['usability']);
	}
	if(isset($get['replace'])) {
		$replacesearch = trim($get['replace']);
	}
	if(isset($get['green'])) {
		$greensearch = trim($get['green']);
	}

	if(isset($_GET['page']) && ctype_digit($_GET['page'])) {
		$page = $_GET['page'];
	}
	else {
		$page = 1;
	}

	mysql_connect($dbip, $dbuser, $dbpass);
	@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");



	$queryarray = array();

	if($fullsearch != "") {
		$s = mysql_real_escape_string($fullsearch);
		$queryarray[] = "MATCH (tags, artist, track, album) AGAINST ('$s')";
	}

	if($artistsearch != "") {
		$s = mysql_real_escape_string($artistsearch);
		$queryarray[] = "MATCH (artist) AGAINST ('$s' IN BOOLEAN MODE)";
	}

	if($titlesearch != "") {
		$s = mysql_real_escape_string($titlesearch);
		$queryarray[] = "MATCH (track) AGAINST ('$s' IN BOOLEAN MODE)";
	}

	if($albumsearch != "") {
		$s = mysql_real_escape_string($albumsearch);
		$queryarray[] = "MATCH (album) AGAINST ('$s' IN BOOLEAN MODE)";
	}

	if($tagssearch != "") {
		$s = mysql_real_escape_string($tagssearch);
		$queryarray[] = "MATCH (tags) AGAINST ('$s' IN BOOLEAN MODE)";
	}

	if($greensearch != "") {
		$s = mysql_real_escape_string($greensearch);
		$queryarray[] = "MATCH (accepter) AGAINST ('$s' IN BOOLEAN MODE)";
	}

	if($usesearch == "1") {
		$usequery = "`usable`='1'";
	}
	else if($usesearch == "0") {
		$usequery = "`usable`='0'";
	}
	else {
		$usequery = "";
	}
	if($replacesearch == "1") {
		$replacequery = "`need_reupload`='1'";
	}
	else if($replacesearch == "0") {
		$replacequery = "`need_reupload`='0'";
	}
	else {
		$replacequery = "";
	}

	$imploded = implode(" AND ", $queryarray);
	
	$queries = array();
	if(!empty($queryarray))
		$queries[] = '('.$imploded.')';
	if($usequery != "")
		$queries[] = $usequery;
	if($replacequery != "")
		$queries[] = $replacequery;
	
	$queries_str = implode(" AND ", $queries);
	
	if($queries_str === "") {
		$query = "SELECT * FROM `tracks` ORDER BY `id` ASC;";
	}
	else {
		// if you want me to i'll add an "order" combobox but please don't hardcode it - Vin
		$query = "SELECT * FROM `tracks` WHERE $queries_str;"; 
	}
	
	// if(empty($queryarray) && $usequery == "" && $replacequery == "") {
		// $query = "SELECT * FROM `tracks` ORDER BY `id` ASC;";
	// }
	// else if(empty($queryarray) && $replacequery == "") {
		// $query = "SELECT * FROM `tracks` WHERE $usequery;";
	// }
	// else if(empty($queryarray) && $usequery == "") {
		// $query = "SELECT * FROM `tracks` WHERE $replacequery;";
	// }
	// else if(empty($queryarray)) {
		// $query = "SELECT * FROM `tracks` WHERE $usequery AND $replacequery;";
	// }
	// else if($usequery == "") {
		// $query = "SELECT * FROM `tracks` WHERE $imploded;";
	// }
	// else {
		// $query = "SELECT * FROM `tracks` WHERE ($imploded) AND $usequery;";
	// }

	$page_query = array();

	$results = mysql_query($query);
	$query = htmlspecialchars($query); //make it page-safe

	$rescount = mysql_num_rows($results);

	$pagecount = ceil($rescount / 30);

	$resstart = ($page - 1) * 30;
	$count = 0;
	
	if($page > $pagecount) {
		$page = $pagecount - 1;
	}	

	$i = 1;
	while($i <= $pagecount) {
		$page_query[$i] = $get;
		$page_query[$i]['page'] = $i;
		$page_query[$i] = http_build_query($page_query[$i]);
		$i = $i + 1;
	}

	$f_s = htmlspecialchars($fullsearch);
	$ar_s = htmlspecialchars($artistsearch);
	$t_s = htmlspecialchars($titlesearch);
	$al_s = htmlspecialchars($albumsearch);
	$ta_s = htmlspecialchars($tagssearch);
	$gr_s = htmlspecialchars($greensearch);

	$u1 = $u2 = $u3 = "";
	$r1 = $r2 = $r3 = "";

	if($usesearch == "0") {
		$u2 = 'selected="selected"';
	}
	else if($usesearch == "1") {
		$u3 = 'selected="selected"';
	}
	else {
		$u1 = 'selected="selected"';
	}
	if($replacesearch == "0") {
		$r2 = 'selected="selected"';
	}
	else if($replacesearch == "1") {
		$r3 = 'selected="selected"';
	}
	else {
		$r1 = 'selected="selected"';
	}
	
	$i = 1;
	$pagenav = array();
	while($i <= $pagecount) {
		if($i == (int)$page) {
			$pnav = "$i";
		}
		else {
			$pnav = "<a href=\"viewdb.php?$page_query[$i]#datatable\">$i</a>";
		}
		
		$pagenav[] = $pnav;
		$i = $i + 1;
	}
	
	$pagenav = implode(" | ", $pagenav);
	
	$afk = mysql_query("SELECT `isafkstream` FROM `streamstatus`;");
	if(mysql_num_rows($afk) > 0) {
		if(mysql_result($afk, 0, "isafkstream") == 1) {
			$canrequest_afk = '';
		}
		else {
			$canrequest_afk = 'disabled="disabled"';
		}
	}
	else {
		$canrequest_afk = 'disabled="disabled"';
	}
	
	if($_SESSION['privileges'] >= 4 || $_SESSION['userid'] == 30) { // more hack
		$candelete = '';
		$canrequest = '';
	}
	else {
		$candelete = 'disabled="disabled"';
		$canrequest = 'disabled="disabled"';
	}
	
	$result_table = "";
	
	while($count < 30 && $resstart < $rescount) {
		$songid = mysql_result($results, $resstart, "id");
		$artist = htmlspecialchars(mysql_result($results, $resstart, "artist"));
		$track = htmlspecialchars(mysql_result($results, $resstart, "track"));
		$album = htmlspecialchars(mysql_result($results, $resstart, "album"));
		$tags = htmlspecialchars(mysql_result($results, $resstart, "tags"));
		$use = mysql_result($results, $resstart, "usable");
		$accept = htmlspecialchars(mysql_result($results, $resstart, "accepter"));
		$lasteditor = htmlspecialchars(mysql_result($results, $resstart, "lasteditor"));
		$searchprio = mysql_result($results, $resstart, "priority");
		$requestvalue = mysql_result($results, $resstart, "requestcount");
		$reupload = mysql_result($results, $resstart, "need_reupload");
		$songhash = compress_hex(mysql_result($results, $resstart, "hash"));
		if($use == "1") 
			$use = "Yes";
		else
			$use = "No";
		if($accept == "")
			$accept = "&nbsp;";
		if($lasteditor == "")
			$lasteditor = "&nbsp;";
		
		$lastplayed = "Never";
		$lastrequested = "Never";
		if(strtotime(mysql_result($results, $resstart, "lastplayed")) > 0)
			$lastplayed = date("Y-m-d H:i T", strtotime(mysql_result($results, $resstart, "lastplayed")));
		if(strtotime(mysql_result($results, $resstart, "lastrequested")) > 0)
			$lastrequested = date("Y-m-d H:i T", strtotime(mysql_result($results, $resstart, "lastrequested")));
		
		//$fullpath = mysql_result($results, $resstart, "path");
		//$relpath = relativate($fullpath, $_SERVER['DOCUMENT_ROOT']);
		//if(substr($relpath, 0, 1) != "/") 
		//	$relpath = "/".$relpath;
		$relpath = "/res/music/" . mysql_result($results, $resstart, "path");
		
		if($reupload == 1) {
			$reupload = '<input type="checkbox" name="reupload" value="1" checked="checked" />';
		}
		else {
			$reupload = '<input type="checkbox" name="reupload" value="1" />';
		}
		
		$query_url = http_build_query($get);
		
		$tablerow = <<<TABLEROW
			<form action="viewdb.php?$query_url" method="post">
			<input type="hidden" name="t_id" value="$songid" />
			<tr>
				<td align="center">$songid</td>
				<td align="center"><input type="submit" name="subbut" value="Save" /></td>
				<td><input type="text" name="t_artist" value="$artist" /></td>
				<td><input type="text" name="t_title" value="$track" /></td>
				<td><input type="text" name="t_album" value="$album" /></td>
				<td><input type="text" name="t_tags" value="$tags" /></td>
				<td>$lastplayed</td>
				<td>$lastrequested</td>
				<td>$use</td>
				<td>$searchprio</td>
				<td>$requestvalue</td>
				<td>$accept</td>
				<td>$lasteditor</td>
				<td>$reupload</td>
				<td align="center" style="text-align:center"><a href="#" class="btn play" value="$relpath">Play</a><br><a href="/song/$songhash" target="_blank">File</a></td>
				<!-- td align="center"><input type="submit" name="subbut" value="Request" $canrequest_afk $canrequest /></td -->
				<td align="center"><input type="submit" name="subbut" value="Delete" $candelete /></td>
			</tr>
			</form>
TABLEROW;
		
		$result_table = $result_table . "\n" . $tablerow;
		
		$count = $count + 1;
		$resstart = $resstart + 1;
	}
	
	mysql_close();
	
}

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
	</head>
	<body>
		$main_menu
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
		<form action="" method="GET">
		<table cellspacing="0" cellpadding="2" class="edwardian-table compactix">
			<tr>
				<th colspan="2">Search</th>
			</tr>
			<tr>
				<td>Full search:</td>
				<td><input type="text" name="fullsearch" value="$f_s" /></td>
			</tr>
			<tr>
				<td>Subsearches:</td>
				<td>
					<table cellspacing="0" cellpadding="2" class="edwardian-table compactix">
						<tr>
							<td>Artist:</td>
							<td><input type="text" name="artist" value="$ar_s" />
						</tr>
						<tr>
							<td>Title:</td>
							<td><input type="text" name="title" value="$t_s" />
						</tr>
						<tr>
							<td>Album:</td>
							<td><input type="text" name="album" value="$al_s" />
						</tr>
						<tr>
							<td>Tags:</td>
							<td><input type="text" name="tags" value="$ta_s" />
						</tr>
						<tr>
							<td>Accept:</td>
							<td><input type="text" name="green" value="$gr_s" />
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>Usability:</td>
				<td>
					<select name="usability">
						<option value="na" $u1 >N/A</option>
						<option value="0" $u2 >Not usable</option>
						<option value="1" $u3 >Usable</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Needs reupload:</td>
				<td>
					<select name="replace">
						<option value="na" $r1 >N/A</option>
						<option value="0" $r2 >Does not need reupload</option>
						<option value="1" $r3 >Needs reupload</option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<input type="submit" name="subbut" value="Search" />
					<input type="button" onclick="ecomodo();" value="eco-mode" />
					<input type="button" onclick="clearViewDB();" value="Clear" />
				</td>
			</tr>
		</table>
		</form>
		<table cellspacing="0" cellpadding="2"  class="edwardian-table compactix">
			<tr>
				<td>Query:</td>
				<td><code>$query</code></td>
			</tr>
			<tr>
				<td>Results:</td>
				<td>$rescount</td>
			</tr>
		</table>
		<br/>
		<script>
function ecomodo() {
	var tab = document.getElementById('dbrestab');
	var row = tab.getElementsByTagName('tr');
	for (var r = 0; r < row.length; r++)
	{
		var col = row[r].getElementsByTagName('td');
		if (col.length < 7)
		{
			col = row[r].getElementsByTagName('th');
		}
		if (col.length > 7)
		{
			col[0].innerHTML = '';
			col[1].innerHTML = '';
			col[6].innerHTML = '';
			col[7].innerHTML = '';
			col[8].innerHTML = '';
			col[9].innerHTML = '';
			col[10].innerHTML = '';
			col[11].innerHTML = '';
			col[12].innerHTML = '';
			col[13].innerHTML = '';
			col[15].innerHTML = '';
		}
	}
	//document.write('<style>.play{display:none!important}</style>');


var css = '.play { display: none !important; }',
	head = document.getElementsByTagName('head')[0],
	style = document.createElement('style');
style.type = 'text/css';
if (style.styleSheet)
{
	style.styleSheet.cssText = css;
} else {
	style.appendChild(document.createTextNode(css));
}
head.appendChild(style);




}
		</script>
		<a name="datatable"></a>
		<table id="dbrestab" cellspacing="0" cellpadding="2" class="zebra-striped condensed-table edwardian-table">
			<tr>
				<th>#</th>
				<th>yea</th>
				<th>Artist</th>
				<th>Title</th>
				<th>Album</th>
				<th>Tags</th>
				<th>Last played (AFK)</th>
				<th>Last requested (AFK)</th>
				<th>AFK?</th>
				<th>Search priority</th>
				<th>Request count</th>
				<th>Accepted by</th>
				<th>Last edit by</th>
				<th>Need replacement</th>
				<th>File</th>
				<!-- th>Request track</th -->
				<th>OH NO</th>
			</tr>
$result_table
		</table><br/>
Page: $pagenav
	<br><br><br><br>&nbsp; <!-- padding for player bar -->
	</body>
</html>
SITESTR;

echo $site;

?>

