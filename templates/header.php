<?php
include_once(__DIR__ . "/../res/common.php");

$protocol =  (($_SERVER['SERVER_PORT'] == 443) ? "https" : "http");

define('MAINTENANCE', False);

if (MAINTENANCE && !isset($_GET['dev'])) { header("Location: /maintenance.php"); die(); }

mysql_connect($dbip, $dbuser, $dbpass);
@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");

$custom = "\n"; // append to this if stuff has to be customized
$custom2 = ""; // append to this if stuff has to be appeneded to footer

$djid = 0;
$originalcss = true;
if(isset($_GET['mode']) && is_numeric($_GET['mode'])) {
	$djid = (int)($_GET['mode']);
}

$stream = mysql_query("SELECT * FROM `streamstatus` LIMIT 1");

if(mysql_num_rows($stream) == 1) {
	if($djid == 0)
		$djid = mysql_result($stream, 0, "djid");
	$djid_ = mysql_real_escape_string($djid);
	$dj = mysql_query("SELECT css FROM djs WHERE id='$djid_';");
	
	$custom .= "<script>var djmode=$djid;</script>";
	
	if(mysql_num_rows($dj) == 1) {
		$css = mysql_result($dj, 0, "css");
		if($css !== "") {
			$custom .= <<<CSS
	<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Dosis:200" />
	<link rel="stylesheet" href="/css/$css" />
CSS;
		}
	}
}

define('MODE', $djid); // because ed likes this

if (MODE==6) { //eggmun
	if (isset($_COOKIE) && isset($_COOKIE['backdrop']))
	{
		$bd1 = str_replace('\\','/',$_COOKIE['backdrop']);
		$bd2 = str_replace('\\','/',$_COOKIE['backdrop2']);
		$custom .= "<style>html{background:url('file://$bd1/$bd2') fixed bottom right no-repeat !important}</style>\n";
	}
	$custom .= '<link rel="stylesheet" href="/css/twatter.css" />';
}
else if (MODE==7) { //ed
	$custom2 .= '<script src="/css/ed/index.js"></script>';
	//$originalcss = false;  // Vin was here and disabled the css
}
else if (MODE==20) { //claud
}
else if (MODE==4) { //kuma
	$custom .= <<<KUMA
<video id="pacvid">
	<source src="/ed/paccer.mp4" type="video/mp4" />
	<source src="/ed/paccer.webm" type="video/webm" />
</video>
<style>
	#pacvid {
		width: 1px;
		height: 1px;
		position: absolute;
		z-index: 9002;
		left: 0;
		top: 0;
	}
</style>
<script>
var paconeo;
var paconev = 0;
function paconew()
{
	paconev += 0.03;
	if (paconev > 1)
	{
		//alert('OH SHIT');
		var vid = document.getElementById('pacvid');
		paconeo.style.opacity = 1;
		paconeo.innerHTML =
			'<video autoplay="autoplay">' +
				'<source src="/ed/paccer.mp4" type="video/mp4" />' +
				'<source src="/ed/paccer.webm" type="video/webm" />' +
			'</video>';
		vid.style.width = '100%';
		vid.style.height = '100%';
		vid.play();
	}
	else
	{
		paconeo.style.opacity = paconev;
		setTimeout('paconew();', 30);
	}
}
function pacone()
{
	paconeo = document.createElement('div');
	paconeo.style.position = 'absolute';
	paconeo.style.zIndex = '9001';
	paconeo.style.top = '0';
	paconeo.style.left = '0';
	paconeo.style.right = '0';
	paconeo.style.bottom = '0';
	paconeo.style.background = '#000';
	paconeo.style.opacity = '0';
	(document.getElementsByTagName('body')[0]).appendChild(paconeo);
	paconew();
}
var pacpos = [];
var pacobj = [];
for (var n = 0; n < 3; n++)
{
	pacpos[n] = -10;
	pacobj[n] = document.createElement('img');
	pacobj[n].style.position = 'absolute';
	pacobj[n].style.bottom = (2+n*2) + '%';
	pacmov(n);
	pacobj[n].onclick = pacone;
	//{
		//alert('you expected some hot cloning action?');
		//alert('too bad!');
		//alert("it's just me, error");
		//alert('dummy');
	//}
	document.getElementsByTagName('html')[0].appendChild(pacobj[n]);
}
pacobj[0].setAttribute('src','/css/pacs/green.gif');
pacobj[1].setAttribute('src','/css/pacs/orange.gif');
pacobj[2].setAttribute('src','/css/pacs/purple.gif');
pacpos[0] = -14;
pacpos[1] = -10;
pacpos[2] = -12;
function pacmov(i)
{
	if (i != 0) return;
	for (var n=0; n<pacpos.length; n++)
	{
		pacpos[n] += 0.1;
		if (pacpos[n] > 100) pacpos[n] = -5;
		pacobj[n].style.left = pacpos[n] + '%';
	}
	setTimeout('pacmov(0)',70);
}
</script>
KUMA;
}
else if (MODE==140) { //eku
//	$custom2 .= <<<EKU
//	<script src="/jvis/engine.js"></script>
//	<script src="/jvis/jvis.js"></script>
//EKU;
}
mysql_close();

define('CUSTOM_FOOTER', $custom2);

$originalcsstext = <<<ORGCSS
	<link rel="stylesheet" href="/css/bootstrap.min.css" />
	<link rel="stylesheet" href="/css/index.css" />
ORGCSS;
if (!$originalcss) $originalcsstext='';


echo <<<DICKS
<!DOCTYPE html>
<html>
<head>
	<title>R/a/dio</title>
	<meta charset="utf-8" />
	<meta name="description" content="We cater to all of your Anime/Japan-related music tastes. Just point your favorite music player at our stream link." />
	<meta name="keywords" content="japan,music,anime,radio,r/a/dio,r-a-dio,stream,anison,日本,アニソン" />
	<link href="/favicon.ico" type="image/vnd.microsoft.icon" rel="shortcut icon">
	<script src="/js/jquery.min.js"></script>
	<script src="/js/jquery.jplayer.min.js"></script>
	<script>window.swfpath = "/js/Jplayer.swf";</script>
	<script src="//www.google.com/recaptcha/api/js/recaptcha_ajax.js"></script>
	<script src="/js/bootstrap-plugins.js"></script>
	<script src="/js/main.js"></script>
	<script src="/js/jquery.form.js"></script>
	<script src="/js/bootstrap-typeahead.js"></script>
$originalcsstext
$custom
	<!--<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-42658381-1', 'www.r-a-d.io');
	  ga('send', 'pageview');
	</script>-->
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-42658381-1']);
  _gaq.push(['_setDomainName', '.r-a-d.io']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>

</head>
<body>
	<div id="player"></div>
	<div class="topbar">
		<div class="topbar-inner">
			<div class="container" style="position: relative;">
				<h3><a href="#">R/a/dio</a></h3>
				<ul class="nav">
					<li class="go active" id="p-home">
						<a href="/">Home</a>
					</li>
					<li class="go" id="p-news">
						<a href="/news/">News</a>
					</li>
					<li class="go" id="p-lastplayed">
						<a href="/lastplayed/">Last Played</a>
					</li>
					<li class="go" id="p-queue">
						<a href="/queue/">Queue</a>
					</li>
					<li class="go" id="p-favorites">
						<a href="/favorites/">Favorites</a>
					</li>
					<li class="go" id="p-submit">
						<a href="/submit/">Submit track</a>
					</li>
					<li class="go" id="p-staff">
						<a href="/staff/">Staff</a>
					</li>
					<li class="go" id="p-irc">
						<a href="/irc/">IRC</a>
					</li>
					<li class="go" id="p-stats">
						<a href="/stats/">Stats</a>
					</li>
					<li class="go" id="p-search">
						<a href="/search/">Request</a>
					</li>
				</ul>
				<form id="search" class="pull-right" action="/search/" style="position: absolute; right: 0px;">
					<input class="search" type="text" name="query" autocomplete="off" placeholder="Search library">
				</form>
			</div>
<!-- <div class="alert-message danger" style="text-align: center; margin-bottom:40px;">
        <strong>R/a/dio will be offline Sat 15th Dec, 1200UTC for approximately 1 hour, while we upgrade our streamer. You should not notice a thing. The site, stream and IRC bot will all be offline; apologies for any disruption.</strong>
</div> -->

		</div>
	</div>
<noscript class="javascript">
	<div class="container">
		<div class="alert-message danger fade in error-message">
			<strong>You should turn on javascript to get the full functionality of this web page.</strong>
		</div>
	</div>
</noscript>
$newdom
<div class="container error-div" style="display: none;">
	<div class="row">
		<div class="span12 offset2">
		</div>
	</div>
</div>
DICKS;
?>
