<?php

if ($_SERVER["SERVER_PORT"] == 443) {
	$protocol = "https:";
} else {
	$protocol = "http:";
}

header("Content-Type: audio/mpeg-url");

if (@$_GET["url"] == "listen" or !isset($_GET["url"]))
	$url = "main.mp3";
else {
	$url = $_GET["url"];
}

if (@$_GET["format"] == "pls") {
	$format = "pls";
	$output = <<<PLS
[playlist]
NumberOfEntries=1
File1={$protocol}//r-a-d.io/{$url}
Length1=-1
Title1=R/a/dio
PLS;
} else {
	$format = "m3u";
	$output = "$protocol//r-a-d.io/$url";
}

header("Content-Type: audio/mpeg-url");
header("Content-Disposition: attachment; filename=\"$url.$format\"");

echo $output;
