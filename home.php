<?php
	define('ALASKAN_PIPELINE', false); // <-- HERE
	include_once("res/common.php");

	mysql_connect($dbip, $dbuser, $dbpass);
	@mysql_select_db($dbname);
	mysql_query("SET NAMES 'utf8';");
	
	$rmdio = '';
	
	$streamstat = mysql_query("SELECT * FROM `streamstatus`;");
	$num = mysql_num_rows($streamstat);
	$result = array();
	if($num == 0) {
		$status = 0;
	}
	else {
		$status = 1;

		$np = mysql_result($streamstat, 0, "np");
		if(!(strpos($np, "\n") === FALSE)) {
			$np = substr($np, 0, strpos($np, "\n"));
		}
		$nowplaying = $np;

		$listeners = mysql_result($streamstat, 0, "listeners");
		$bitrate = mysql_result($streamstat, 0, "bitrate");

		//LOL FIXD
		$bitrate = 192;

		$djid = mysql_result($streamstat, 0, "djid");

		if($djid == 0) {
			$djname = "Unknown";
			$djimg = "/res/img/dj/none.png";
			$djtext = "Nothing";
		}
		else {
			$dj = mysql_query("SELECT * FROM `djs` WHERE `id`=$djid;");
			$djname = mysql_result($dj, 0, "djname");
			$djimg = "/res/img/dj/".mysql_result($dj, 0, "djimage");
			$djtext = mysql_result($dj, 0, "djtext");
		}
		// if (!defined('MODE')) {
			// define('MODE', $djid);
		// }

		$lps = null;
		$lastplayed = mysql_query("SELECT esong.meta, unix_timestamp(eplay.dt) AS lastplayed FROM eplay JOIN esong ON esong.id = eplay.isong ORDER BY eplay.id DESC LIMIT 5;");

		$count = mysql_num_rows($lastplayed);
		$i = 0;
		$num = 5;

		if (MODE==6 || MODE==25) $num = 10; //eggmun // this doesn't do anything... - vin

		while($i < $num) {
			if($i < $count) {
				$lp_time = date("H:i", mysql_result($lastplayed, $i, "lastplayed"));
				$lp_song = htmlspecialchars(mysql_result($lastplayed, $i, "meta"));
				$lps[] = "<tr><th>$lp_time</th><td>$lp_song</td></tr>";
			}
			$i = $i + 1;
		}
		$lastplayed = implode($lps, "");

		$qs = array();
		$queue = mysql_query("SELECT *, UNIX_TIMESTAMP(time) as utime FROM `queue` ORDER BY `time` ASC LIMIT 5;");

		$count = mysql_num_rows($queue);
		$i = 0;
		$num = 5;

		if (MODE==6 || MODE==20) $num = 0; //eggmun, claud

		while($i < $num) {
			if($i < $count) {
				$qs_time = date("H:i", mysql_result($queue, $i, "utime"));
				$qs_song = htmlspecialchars(mysql_result($queue, $i, "meta"));
				if(mysql_result($queue, $i, "type") == 1)
					$qs_song = '<b>' . $qs_song . '</b>';
				$qs[] = "<tr><th>$qs_time</th><td>$qs_song</td></tr>";
			}
			$i = $i + 1;
		}
		$queue = implode($qs, "");

		$news = mysql_query("SELECT * FROM `news` ORDER BY `time` DESC LIMIT 3;");
		$rows = mysql_num_rows($news);

		$i = 0;
		$nw = array();
		while ($i < $rows) {
			$n = mysql_result($news, $i, "id");
			$header = htmlspecialchars(mysql_result($news, $i, "header"));
			$time = date("D j M, H:i", strtotime(mysql_result($news, $i, "time")));
			$text = str_replace("\n", "<br/>", mysql_result($news, $i, "newstext"));
			$trunc = strpos($text, "TRUNCATE");
			if($trunc !== FALSE) {
				$text = substr($text, 0, $trunc);
			}
			$text = str_replace("TRUNCATE", "", $text);
			$nw[] = "<div class=\"span-one-third\"><h5><a alt=\"$n\" href=\"/news/99\">$header</a></h5><div class=\"border\"><h6>$time</h6><span class=\"newstext\">$text</span></div></div>";
			$i = $i + 1;
		}
		$news = implode($nw, "");
	}
	$message = "";
	if(isset($_GET['x']) && $_GET['x'] == "redirect") {
		$message = '<div class="alert-message warning">It seems you\'re coming from <strong>r-a-dio.com</strong>. Our new domain is <strong>r-a-d.io</strong>! Update your bookmarks!</div>';
	}

	// ed was here on a mission from kuma-kun
	// Wessie cleaning up
	$q = mysql_query("select value from radvars where name='curthread' limit 1;");
	$threadmsg = 'From time to time we have threads on /a/. This, however, is no such time. Come back later.';
	if (mysql_num_rows($q)) {
		$uri = mysql_result($q, 0, 'value');
		$uri = str_replace(array('"', '<', '>'), array('\"', '%3C', '%3E'), $uri); //fuck
		if ((strpos($uri, "http://") == 0) || (strpos($uri, "https://") == 0)) {
			$threadmsg = 'We currently have a thread up. <a target="_blank" href="'.$uri.'">Please join us!</a>';
		}
	}
	// IT'S NOT OVER!! there are further (ed)its later down

	// ed was here on a mission from eggmun
	// vin moved it to header.php

	if(MODE == 27 || MODE == 30) { // r/m/dio
		$rmdio = 'm';
	}
	
	$twatter = '';
	if(MODE==6) {
		$twatter = <<<TWATTER
<script charset="utf-8" src="//widgets.twimg.com/j/2/widget.js"></script>
<script>
new TWTR.Widget({
  version: 2,
  type: 'profile',
  rpp: 5,
  interval: 30000,
  width: 250,
  height: 300,
  features: {
    scrollbar: false,
    loop: false,
    live: true,
    behavior: 'all'
  }
}).render().setUser('DJ_Eggmun').start();
</script>
<link rel="stylesheet" href="/css/eggmun.css" />
TWATTER;
	}
	
/*$snow = '';
if(MODE==25 || MODE == 20) {
	$snow = <<<NEWDOM2
<div id="Div1" class="snow"></div>
 
	
 <script type="text/javascript" src="/css/kilim/ThreeCanvas.js">
</script>
 <script type="text/javascript" src="/css/kilim/Snow.js">
</script>
<script src="/css/kilim/3DSnowBox.js"
 type="text/javascript"></script>
 
<script>
      init('Div1');
  </script>

NEWDOM2;

}*/


$alaskan_pipeline = '';
if (ALASKAN_PIPELINE) $alaskan_pipeline = <<<YEAH
<div style="z-index:9020;position:absolute;left:0;right:0;top:0;bottom:0;background:#fff;font-size:3em;text-align:center;padding:4em 3em;background:#f7f7f7 url('/res/img/fallout.jpg') center no-repeat;background-size:cover">
	<h1 style="font-size:1.8em">Something broke</h1>
	<hr />
	<h2>The stream is temporarily on <a href="http://195.5.121.132/">http://195.5.121.132/</a></h2>
	<hr />
	<h6>devs:&nbsp; 2nd line in home.php,&nbsp; set FALSE to disable this</h6>
	<h6 style="font-size:.25em;margin-top:-2em">leave the rest of the code in though, I mean we'll probably need it</h6>
</div>
YEAH;

	// look out for further (ed)its in the other files  ( ﾟ ヮﾟ)
	
	mysql_close();
	echo <<<CONTAINER
			
			$alaskan_pipeline
			<div class="row content-top">
				$message
				<div class="span3" id="logo-image-div">
					
					<!--<img id="logo-image" src="/res/img/logo_image_small.png" alt="Radio logo" />-->
				</div>
				<div class="span10">
					<div class="row info" id="thatbox">
						<div class="span10 row" id="introcontainer">
							<div id="intro" class="span5">
								<img src="/res/img/logotitle_2{$rmdio}.png" alt="Radio" />
							</div>
							<div id="soundmanager-debug"></div>
							<div id="streamconnect" class="span4">
								
								<div class="span4 browplay-jp">
									<button rel="twipsy" class="fill btn info jp-play" disabled="disabled" data-original-title="Play the stream directly in your browser. Uses HTML5 if possible, else a Flash fallback.">Play in browser</button>
									<div class="fill btn info active jp-pause" style="display: none; width: 90%; text-align: center; text-shadow: 0 1px 0 rgba(0, 0, 0, 0.25);"><div class="progress jp-volume-bar success" style="height: 10px; margin: 0px;"><div class="bar jp-volume-bar-value" style="width:10%"></div></div>Stop</div>
								</div>
								<a href="#" id="expand-panel">More stream options</a>

							</div>
						</div>
						<div id="streamconnect-panel" class="span10 row">
							<div class="span4" id="panel-links">
								<h4>Stream links</h4>
								<a rel="twipsy" class="fill btn info" href="/main.mp3.m3u" data-original-title="Downloadable playlist file that can be opened with most media players.">Playlist File (.m3u)</a><br>
								<a class="fill btn info" href="/listen.pls" data-original-title="Downloadable playlist file that can be opened with most media players, in case the m3u doesn't work.">Playlist File (.pls)</a><br>
								<a rel="twipsy" class="fill btn info" href="//r-a-d.io/main.mp3" data-original-title="Direct link to the stream. Works over SSL in Most Players! (If not make it http)">Direct Stream Link</a>
							</div>
							<div class="span4" id="panel-info">
								<p>You can also listen to r/a/dio in most music players. All you have to do is copy one of the links to the left into your player and you're good to go!</p>
							</div>
							
							
						</div>
						
						
						
						<!--<div id="links" class="row">
							<div class="span4" style="margin-left: 70px;">
								<a rel="twipsy" class="fill btn info" href="http://r-a-d.io:1130/main.mp3.m3u" data-original-title="Downloadable playlist file that can be opened with most media players.">Playlist File (.m3u)</a>
							</div>
							<div class="span4">
								<a class="fill btn info" href="http://r-a-d.io/listen.pls" data-original-title="Downloadable playlist file that can be opened with most media players, in case the m3u doesn't work.">Playlist File (.pls)</a>
							</div>
							<div class="span4 browplay-jp">
								<button rel="twipsy" class="fill btn info jp-play" disabled="disabled" data-original-title="Play the stream directly in your browser. Uses HTML5 if possible, else a Flash fallback.">Play in browser</button>
								<div class="fill btn info active jp-pause" style="display: none; width: 90%; text-align: center; text-shadow: 0 1px 0 rgba(0, 0, 0, 0.25);"><div class="progress jp-volume-bar success" style="height: 10px; margin: 0px;"><div class="bar jp-volume-bar-value"></div></div>Stop</div>
							</div>
							<div class="span4 browplay-vis">
								<a rel="twipsy" id="edplay" class="fill btn info" href="#" data-original-title="Play the stream directly in your browser. ed version: now with 100% more eyecandy">(please wait)</a>
							</div>
						</div>-->
						<div id="main-streamdata" class="row">
							<div id="nowplaying" class="span10">
								<div class="nowplaying">
									<h2>$nowplaying</h2>
								</div>
							</div>
							<div id="progress" class="span10">
								<div class="progress danger">
									<div class="bar" style="width: 100%;"></div>
								</div>
							</div>
							<div id="stream_info" class="span10">
								<div class="span2 block-inline listeners">
									Listeners: <span class="value">$listeners</span>
								</div>
								<div class="span4 block-inline"><span id="current_pos"></span>/<span id="current_len"></span></div>
								<div class="span2 block-inline bitrate">
									<!-- Bitrate: <span class="value">$bitrate</span>kbps -->
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="span3">
					<ul class="media-grid">
						<li>
							<span class="djmedia" rel="twipsy" data-original-title="$djtext">
								<img class="thumbnail djimg" width="150" height="150" border="0" src="$djimg">
								<span id="dj">$djname</span>
							</span>
						</li>
					</ul>
				</div>
			</div>
			<div class="row">
				<div id="last-played-container" class="span8">
					<table class="zebra-striped" id="last-played">
						<thead>
							<tr>
								<th colspan="2">Last Played</th>
							</tr>
						</thead>
						<tbody>
							$lastplayed
						</tbody>
					</table>
				</div>
				$twatter
				<div id="queue-container" class="span8">
					<table class="zebra-striped" id="queue">
						<thead>
							<tr>
								<th colspan="2">Queue</th>
							</tr>
						</thead>
						<tbody>
							$queue
						</tbody>
					</table>
				</div>
			</div>

			<!-- ed was here on a mission from kuma-kun -->
			<div class="row seperator">
				<div class="seperator span16">
				</div>
			</div>
			<div id="thread" style="position:relative;top:-.6em;text-align:center">
				$threadmsg
			</div>
			<!-- end of (ed)its -->

			<div class="row seperator">
				<div class="seperator span16">
				</div>
			</div>
			<div class="row news">
				$news
			</div>
<!-- div style="position:absolute;z-index:2;width:100%;height:100%;left:0;right:0;top:0;bottom:0;background:rgba(128,0,0,0.7) url('/css/commie.png') bottom no-repeat">&nbsp;</div -->
<!-- style>html{background:url('/css/commie.png') bottom right no-repeat !important}</style -->
CONTAINER;



if (isset($_GET['itest'])) { ?>
<iframe id="newdj" src="/?mode=6" style="width:100%;height:100%;position:absolute;top:0;left:0;opacity:0"></iframe>
<script>
var opac = 0;
var iframe = document.getElementById('newdj');
function fadechange()
{
	opac += 0.05;
	if (opac > 1) return;
	iframe.style.opacity = opac;
	setTimeout('fadechange()', 100);
	document.title = iframe.style.opacity;
}
setTimeout('fadechange();', 3000);
</script>
<? } ?>
