<?php
include_once(__DIR__ . "/res/common.php");
//header('Content-Type: text/javascript; charset=utf-8');
//header('Access-Control-Max-Age: 3628800');
//header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

$_GET = array();
$_GET['q'] = '1';
$_GET['lp'] = '1';

/*if (isset($_GET['modo'])) {
	define('MODO', $_GET['modo']);
}
else {
	define('MODO', 'none');
}*/

//$callback = '';
//if (isset($_GET['callback'])) {
//	$callback = $_GET['callback'];
//}

$sendLastPlayed = false;
if (isset($_GET['lp'])) {
	if ($_GET['lp'] == '0') {
		$sendLastPlayed = false;
	}
	else {
		$sendLastPlayed = true;
	}
}
$sendQueue = false;
if (isset($_GET['q'])) {
	if ($_GET['q'] == '0') {
		$sendQueue = false;
	}
	else {
		$sendQueue = true;
	}
}
mysql_connect($dbip, $dbuser, $dbpass);
@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");

$streamstat = mysql_query("SELECT * FROM `streamstatus`;");
$num = mysql_num_rows($streamstat);
$result = array();
if($num == 0) {
	$result['online'] = 0;
	
	if ($sendLastPlayed) {
		$lastplayed = mysql_query("SELECT esong.meta AS title, UNIX_TIMESTAMP(eplay.dt) AS lastplayed FROM eplay LEFT JOIN esong ON eplay.isong = esong.id ORDER BY eplay.dt DESC LIMIT ".$limes.";");
		
		$limes = 5;
		$lps = array();
		if (MODO == 'egg') {
			$limes = 10;
		}
		$count = mysql_num_rows($lastplayed);
		$i = 0;
		$num = $limes;

		while($i < $num) {
			if($i < $count) {
				$lps[] = array(mysql_result($lastplayed, $i, "lastplayed"), mysql_result($lastplayed, $i, "title"), 0);
			}
			$i = $i + 1;
		}
		$result['lp'] = $lps;
	}
}
else {
	$result['online'] = 1;
	
	//this is a half-hack - Vin
	$djid = mysql_result($streamstat, 0, "djid");
	if($djid == 6 || $djid == 20 || $djid == 16 || $djid == 25 || $djid == 14) { // eggmun
		define('MODO', 'egg');
		$sendQueue = false;
	}
	else {
		define('MODO', 'none');
	}
	
	$np = mysql_result($streamstat, 0, "np");
	if(!(strpos($np, "\n") === FALSE)) {
		$np = substr($np, 0, strpos($np, "\n"));
	}
	$result['np'] = $np;
	
	$result['list'] = mysql_result($streamstat, 0, "listeners");
	$result['kbps'] = mysql_result($streamstat, 0, "bitrate");
	
	//LOL FIXD
	$result['kbps'] = 192;
	
	$djid = mysql_result($streamstat, 0, "djid");
	
	$result['start'] = (int)mysql_result($streamstat, 0, "start_time");
	$result['end'] = (int)mysql_result($streamstat, 0, "end_time");
	$result['cur'] = time();
	if($djid == "0") {
		$djname = "Unknown";
		$djimg = "/res/img/dj/none.png";
		$djtext = "No DJ";
	}
	else {
		$dj = mysql_query("SELECT * FROM `djs` WHERE `id`=$djid;");
		$djname = mysql_result($dj, 0, "djname");
		$djimg = "/res/img/dj/".mysql_result($dj, 0, "djimage");
		$djtext = mysql_result($dj, 0, "djtext");
		$djcolor = mysql_result($dj, 0, "djcolor");
	}
	
	$result['dj'] = $djname;
	$result['djimg'] = $djimg;
	$result['djtext'] = $djtext;
	$result['djcolor'] = $djcolor;

	$q = mysql_query("select value from radvars where name='curthread' limit 1;");
	$result['thread'] = 0;
	if (mysql_num_rows($q)) {
		$uri = mysql_result($q, 0, 'value');
		if ((strpos($uri, "http://") === 0) || (strpos($uri, "https://") === 0)) {
			$result['thread'] = $uri;
		}
	}
	
	$limes = 5;
	$lps = null;
	if (MODO == 'egg') {
		$limes = 10;
	}
	if ($sendLastPlayed) {
		$lastplayed = mysql_query("SELECT esong.meta AS title, UNIX_TIMESTAMP(eplay.dt) AS lastplayed FROM eplay LEFT JOIN esong ON eplay.isong = esong.id ORDER BY eplay.dt DESC LIMIT ".$limes.";");

		$count = mysql_num_rows($lastplayed);
		$i = 0;
		$num = $limes;

		while($i < $num) {
			if($i < $count) {
				$lps[] = array(mysql_result($lastplayed, $i, "lastplayed"), mysql_result($lastplayed, $i, "title"), 0);
			}
			$i = $i + 1;
		}
		$result['lp'] = $lps;
	}
	$qs = null;
	if ($sendQueue) {
		$queue = mysql_query("SELECT meta AS track, UNIX_TIMESTAMP(time) AS timestr, type FROM `queue` ORDER BY `time` ASC LIMIT 5;");

		$count = mysql_num_rows($queue);
		$i = 0;
		$num = 5;

		while($i < $num) {
			if($i < $count) {
				$qs[] = array(mysql_result($queue, $i, "timestr"), mysql_result($queue, $i, "track"), mysql_result($queue, $i, "type"));
			}
			$i = $i + 1;
		}
		$result['queue'] = $qs;
    }
}

//file_put_contents("/home/www/r-a-dio.com/api_generated", htmlentities(json_encode($result), ENT_NOQUOTES, 'UTF-8'), LOCK_EX);
$memcache = memcache_connect('localhost', 11211);
memcache_set($memcache, 'main_page_api_gen', htmlentities(json_encode($result, JSON_PRETTY_PRINT), ENT_NOQUOTES, 'UTF-8'), 0, 2);




mysql_close();

?>
