<?php
include_once("../res/common.php");

$site_text = "";

mysql_connect($dbip, $dbuser, $dbpass);

@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
if(isset($_POST['songid'])) {
	$songid = $_POST['songid'];
	if(!is_numeric($songid)) {
		$site_text = "Incorrect parameter.";
	}
	else {
		$songid = (int)$songid;
		
		$afk = mysql_query("SELECT `isafkstream` FROM `streamstatus`;");
		if(mysql_num_rows($afk) > 0) {
			if(mysql_result($afk, 0, "isafkstream") == 1) {
				$canrequest_afk = TRUE;
			}
			else {
				$canrequest_afk = FALSE;
			}
		}
		else {
			$canrequest_afk = FALSE;
		}
		
		//TEEEEEEMPORARY!!!!!!!!!!!!!!!!
		$canrequest_afk = TRUE;
		//TEMMMMMMMPORRRARY!!!!!!!
		
		$ip_lr = mysql_query("SELECT * FROM `requesttime` WHERE `ip`='$REMOTE_ADDR' LIMIT 1;");
		if(mysql_num_rows($ip_lr) >= 1) {
			$iptime = strtotime(mysql_result($ip_lr, 0, "time"));
		}
		else {
			$iptime = 0;
		}
		$now = time();
		
		if($now - $iptime > $ip_req_delay) {
			$canrequest_ip = TRUE;
		}
		else {
			$canrequest_ip = FALSE;
		}
		
		$lp_song = mysql_query("SELECT * FROM `tracks` WHERE `id`=$songid LIMIT 1;");
		$lptime = strtotime(mysql_result($lp_song, 0, "lastrequested"));
		if(!is_numeric($lptime))
			$lptime = 0;
			
		if($now - $lptime > $song_req_delay) {
			$canrequest_song = TRUE;
		}
		else {
			$canrequest_song = FALSE;
		}
		
		if(!$canrequest_afk || !$canrequest_ip || !$canrequest_song) {
			if(!$canrequest_afk) {
				$site_text = "You can't request songs at the moment.";
			}
			else if(!$canrequest_ip) {
				$site_text = "You need to wait longer before requesting again.";
			}
			else if(!$canrequest_song) {
				$site_text = "You need to wait longer before requesting this song.";
			}
		}
		else {
			$site_text = "Thank you for making your request!";
			
			$songid = mysql_real_escape_string($songid);
			
			mysql_query("INSERT INTO `requests` (`trackid`, `time`, `ip`) VALUES ($songid, NOW(), '$REMOTE_ADDR');");
			
			if(mysql_num_rows($ip_lr) >= 1) {
				mysql_query("UPDATE `requesttime` SET `time`=NOW() WHERE `ip`='$REMOTE_ADDR';");
			}
			else {
				mysql_query("INSERT INTO `requesttime` (`ip`) VALUES ('$REMOTE_ADDR');");
				echo mysql_error();
			}
			
			mysql_query("UPDATE `tracks` SET `lastrequested`=NOW() WHERE `id`=$songid;");
		}
	}
}
else {
	$site_text = "Incorrect parameter.";
}


mysql_close();

$submit_site = <<<SUBMIT
<html>
	<head>
		<title>r/a/dio</title>
		<meta http-equiv="refresh" content="5;url=/search/">
		<link rel="shortcut icon" href="/favicon.ico" />
	</head>
	<body>
		<center><h2>$site_text</h2><center><br/>
		<center><h3>You will be redirected shortly.</h3></center>
	</body>
</html>
SUBMIT;

echo $submit_site;
echo mysql_error();

?>