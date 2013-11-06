<?php
include_once("../res/common.php");
include_once("adminc.php");

if($_SESSION['login'] == 0) {
	header("Location: /x_admin");
	return;
}
if($_SESSION['privileges'] < 1) {
	header("Location: /");
	return;
}
$dp = '';
if($priv >= 4) {
	$dp = "<div id=\"dp\">Today's daypass: ".DAYPASS."</div>";
}

$site = <<<SITESTR
<html>
	<head>
		<title>Admin</title>
		<style>
html {
	color: #000;
	background: #f7f7f7;
	font-family: sans-serif;
}
a {
	color: #fff;
	background: #000;
	width: 46%;
	margin: 1% 2%;
	padding: 3% 0;
	font-size: 1.4em;
	text-align: center;
	text-decoration: none;
	display: inline-block;
	border-radius: 8px;
	text-shadow: 0 1px 0 #000;
}
h3 {
	text-align: center;
	font-size: 3em;
	color: #907;
	margin: .5em !important;
}
#wrap {
	max-width: 900px;
	margin: 0 auto;
}
a:nth-child( 2), a:nth-child( 3) { background: hsl(  0, 100%, 30%)}
a:nth-child( 4), a:nth-child( 5) { background: hsl( 50, 100%, 30%)}
a:nth-child( 6), a:nth-child( 7) { background: hsl(120, 100%, 30%)}
a:nth-child( 8), a:nth-child( 9) { background: hsl(180, 100%, 30%)}
a:nth-child(10), a:nth-child(11) { background: hsl(240, 100%, 30%)}
a:nth-child(12), a:nth-child(13) { background: hsl(300, 100%, 30%)}
#dp {
	text-align: center;
	font-size: 2em;
	color: #555;
	margin: 1em;
}
		</style>
	</head>
	<body><div id="wrap">
		$main_menu
		$dp
	</div></body>
</html>
SITESTR;

echo $site;

?>
