<?php
include_once("../res/common.php");

mysql_connect($dbip, $dbuser, $dbpass);

@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");

$queue = mysql_query("SELECT * FROM `curqueue`;");
$num = mysql_num_rows($queue);
$i = 0;

$site_queue = "";

while($i < $num) {
	if($i % 2) {
		$class = "even";
	}
	else {
		$class = "odd";
	}
	
	$timestr = mysql_result($queue, $i, "timestr");
	


	// ed-patch <!--
	$nigger = mysql_result($queue, $i, "track"));

	$fn = '/dev/shm/a.php.tmp';
	$fh = fopen($fn, 'w');
	fwrite($fh, $row);
	fclose($fh);

	$nigger = shell_exec('iconv -f shift-jis -t utf-8 '.$fn); //.' -o '.$fn.'.o');
	unlink($fn);

	$song = htmlspecialchars($nigger);
	// ed-patch -->


	
	$queuerow = <<<QUEUEROW
						<tr class="$class">
							<td width="50px">$timestr</td>
							<td>$song</td>
						</tr>
QUEUEROW;
	
	$site_queue = $site_queue . $queuerow . "\n";
	
	$i = $i + 1;
}

mysql_close();

$site = <<<SITESTR
<html>
	<head>
		<title>r/a/dio</title>
		<link rel="stylesheet" href="../res/style.css" type="text/css" />
		<link rel="stylesheet" href="../res/nav.css" type="text/css" />
		<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	</head>
	<body>
		<div id="mainbox">
$site_menu
			<div id="contentbox">
				<div id="upper">
					<h1 style="padding-bottom:10px;">Stream queue</h1>
					<div>Here you can view all of the upcoming songs on the stream. The listed times are in 4chan time. (EST)</div>
				</div>
				<div id="lower">
					<table class="alttbl" width="100%" cellspacing="1" cellpadding="0" border="0">
$site_queue
					</table>
				</div>
			</div>
		</div>
	</body>
</html>
SITESTR;

echo $site;

?>
