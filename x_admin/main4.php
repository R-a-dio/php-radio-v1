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
	width: 33%;
	margin: .15%;
	padding: 4% 0;
	font-size: 1.4em;
	text-align: center;
	text-decoration: none;
	display: inline-block;
	border-radius: 4px;
	text-shadow: 0 1px 0 #000;
	transition: border-radius .7s ease-in-out;
	-o-transition: border-radius .7s ease-in-out;
	-moz-transition: border-radius .7s ease-in-out;
	-webkit-transition: border-radius .7s ease-in-out;
}
h3 {
	text-align: center;
	font-size: 3em;
	color: #907;
	margin: .5em 0 0 0;
	border-bottom: 1px solid #ddd;
}
h4 {
	font-style: italic;
	font-family: serif;
	font-weight: normal;
	text-align: center;
	margin: 0 0 1em 0;
	font-size: 1.7em;
	color: #a60;
}
#wrap {
	-max-width: 1200px;
	margin: 0 auto;
}
#dp {
	text-align: center;
	font-size: 2em;
	color: #555;
	margin: 1em;
}

a { margin-right: -.8em }
h4+a { display: none }
		</style>
	</head>
	<body><div id="wrap">
		$main_menu
		$dp
	</div>
SITESTR;
echo $site;
?>
<script>
var steps = 50;
var step = steps;
var col = []; // 1HSL 2HSL
var o = document.getElementsByTagName('a');
for (var a=0; a<o.length; a++)
{
	col[a] = [0,0,0,0,0,0];
}
function pick()
{
	step = 0;
	for (var a=0; a<o.length; a++)
	{
		col[a][0] = col[a][3];
		col[a][1] = col[a][4];
		col[a][2] = col[a][5];
		var h = Math.random()*360;
		var s = Math.random()*50 + 50;
		var l = Math.random()*10 + 25;
		col[a][3] = Math.round(h*10)/10;
		col[a][4] = Math.round(s*10)/10;
		col[a][5] = Math.round(l*10)/10;
		var r1 = Math.round(Math.random()*1000);
		var r2 = Math.round(Math.random()*1000);
		var rad = r1+'px/'+r2+'px';
		o[a].style.borderRadius = rad;
	}
}
function slide()
{
	if (++step > steps)
	{
		pick();
	}
	for (var a=0; a<o.length; a++)
	{
		var h = col[a][0] + (col[a][3] - col[a][0]) * (step/steps);
		var s = col[a][1] + (col[a][4] - col[a][1]) * (step/steps);
		var l = col[a][2] + (col[a][5] - col[a][2]) * (step/steps);
		o[a].style.background = 'hsl('+h+','+s+'%,'+l+'%)';
	}
	setTimeout('slide();',10);
}
slide();

var ogg = (new Audio()).canPlayType('audio/ogg; codecs=vorbis');
for (var a=0; a<o.length; a++)
{
	o[a].addEventListener('mouseover', function(e)
	{
		new Audio('/ed/weed.'+(ogg?'ogg':'mp3')).play();
	});
}
</script>
</body>
</html>
