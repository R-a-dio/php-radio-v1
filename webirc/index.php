<?php
include_once("../res/common.php");

//pageView("webirc", $_SERVER['QUERY_STRING'], gethostbyaddr($_SERVER['REMOTE_ADDR']), $_SESSION['user']);

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

			<div id="navbox">
				<ul id="nav">
					<li>
						<a href="/">Home</a>
					</li>
					<li>
						<a href="/news">News</a>
					</li>

					<li>
						<a href="">Music</a>
						<ul>
							<li><a href="/search">Search</a></li>
							<li><a href="/queue">Queue</a></li>
							<li><a href="/submittrack">Submit track</a></li>
						</ul>

					</li>
					<li>
						<a href="/djs">DJs</a>
					</li>
					<li>
						<a href="/webirc">IRC</a>
					</li>
					
				</ul>

			</div>
			<div id="contentbox">
				<div id="upper">
					<h1>WebIRC</h1>
				</div>
				<div id="lower">
					<iframe src="http://qchat.rizon.net/?channels=r/a/dio&amp;uio=d4" width="776" height="600"></iframe>
				</div>

			</div>
		</div>
<!-- Piwik --> 
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://r-a-dio.com/piwik/" : "http://r-a-dio.com/piwik/");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 1);
piwikTracker.trackPageView();
piwikTracker.enableLinkTracking();
} catch( err ) {}
</script><noscript><p><img src="http://r-a-dio.com/piwik/piwik.php?idsite=1" style="border:0" alt="" /></p></noscript>
<!-- End Piwik Tracking Code -->
	</body>
</html>
SITESTR;

echo $site;

?>