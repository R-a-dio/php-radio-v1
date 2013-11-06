<?php
include_once("../res/common.php");
include_once("adminc.php");

if($_SESSION['login'] == 0) {
	header("Location: /x_admin");
	return;
}
if($_SESSION['privileges'] < 2) {
	header("Location: /x_admin/main.php");
	return;
}

if(isset($_POST['setvar'])) {
	mysql_connect($dbip, $dbuser, $dbpass);
	@mysql_select_db($dbname);
	mysql_query("SET NAMES 'utf8';");
	
	$radvar = $_POST['setvar'];
	$value = mysql_real_escape_string(trim($_POST['val']));
	
	if($value == "") {
		mysql_query("UPDATE `radvars` SET `value`=NULL WHERE `name`='$radvar' LIMIT 1;");
	}
	else {
		mysql_query("UPDATE `radvars` SET `value`='$value' WHERE `name`='$radvar' LIMIT 1;");
	}
	
	mysql_close();
	header("Location: /x_admin/settings.php");
	return;
}

if(isset($_GET['a'])) {
	if($_GET['a'] == "cqueue") {
		mysql_connect($dbip, $dbuser, $dbpass);
		@mysql_select_db($dbname);
		mysql_query("SET NAMES 'utf8';");
		
		
		
		mysql_query("DELETE FROM `curqueue`;");
		
		unlink("/home/ed/streamqueue/queue.txt");
		
		mysql_close();
	}
	else if($_GET['a'] == "clp") {
		mysql_connect($dbip, $dbuser, $dbpass);
		@mysql_select_db($dbname);
		mysql_query("SET NAMES 'utf8';");
		
		

		$lpc = mysql_query("SELECT * FROM `lastplayed`;");
		$lp = mysql_num_rows($lpc);
		
		$todel = $lp - 5;
		if($todel < 0)
			$todel = 0;
		
		mysql_query("DELETE FROM `lastplayed` LIMIT $todel;");
		
		mysql_close();
	}	
}

mysql_connect($dbip, $dbuser, $dbpass);
@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");



$lpc = mysql_query("SELECT * FROM `lastplayed`;");
$lp = mysql_num_rows($lpc);

mysql_close();

$site = <<<SITESTR
<!DOCTYPE html>
<html>
	<head>
		<title>Admin</title>
                <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
                <link rel="stylesheet" href="style.css" type="text/css" />

                <link rel="stylesheet" href="/css/bootstrap.min.css" type="text/css" />

	</head>
	<body>
		$main_menu<br/>
		<h2>Set current thread</h2>
		Use this to set the current r/a/dio thread, displayed on the main page.<br>
		<form method="POST" action="">
		<div style="width: 400px !important;">
			Thread: <input style="width: 80% !important; margin: 2px;" type="text" name="val" size="40" /><br>
			<input type="submit" />
			<input type="hidden" name="setvar" value="curthread" />
		</div>
		</form>
		
		<h2>Miscellaneous settings</h2>
		<a href="settings.php?a=cqueue">Clear queue data</a><br/>
		This link will clear the queue table in the database and remove the queue file on the server. Only necessary for when Kuma (or anyone else with SAM Broadcaster, and has the queue thing set up) stops streaming.<br/><br/>
		<a href="settings.php?a=clp">Clear last played</a><br/>
		This link will clear the last played table (except for the five latest entries). Do this occasionally, like when it's over 1000 entries. The table currently has <b>$lp</b> entries.
	</body>
</html>
SITESTR;

echo $site;



?>
