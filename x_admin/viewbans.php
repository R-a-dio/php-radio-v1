<?php
include_once("../res/common.php");
include_once("adminc.php");

if($_SESSION['login'] == 0) {
	header("Location: /x_admin");
	return;
}
if($_SESSION['privileges'] < 4) {
	header("Location: /x_admin/main.php");
	return;
}

header("Cache-Control: no-cache");

if(isset($_POST['subbut'])) {
	mysql_connect($dbip, $dbuser, $dbpass);
	@mysql_select_db($dbname);
	mysql_query("SET NAMES 'utf8';");
	
	if($_POST['subbut'] == "Ban") {
		$ip = mysql_real_escape_string($_POST['ip']);
		$date = strtotime($_POST['date']);
		$reason = mysql_real_escape_string($_POST['reason']);
		if($date === FALSE) {
			$message = "Incorrect date format.";
			mysql_close();
		}
		else {
			$date = $date - 3600*24*7; // this is such a fucking hack, jesus christ
			mysql_query("INSERT INTO `failedlogins` (ip, time, username) VALUES ('$ip', FROM_UNIXTIME($date - 2), '');");
			mysql_query("INSERT INTO `failedlogins` (ip, time, username) VALUES ('$ip', FROM_UNIXTIME($date - 1), '');");
			mysql_query("INSERT INTO `failedlogins` (ip, time, username) VALUES ('$ip', FROM_UNIXTIME($date), '$reason');");
			
			mysql_close();
			header("Location: /x_admin/viewbans.php");
			exit();
		}
	}
	else if($_POST['subbut'] == "Unban") {
		$ids = explode(",", $_POST['ids']);
		foreach($ids as $id) {
			$id = mysql_real_escape_string($id);
			mysql_query("DELETE FROM `failedlogins` WHERE `id`=$id;");
		}
		mysql_close();
		header("Location: /x_admin/viewbans.php");
		exit();
	}
	

}


mysql_connect($dbip, $dbuser, $dbpass);
@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");

$ips = mysql_query("SELECT DISTINCT ip FROM failedlogins;");
$count = mysql_num_rows($ips);
$i = 0;

$banlist = array();

while($i < $count) {
	$loginip = mysql_result($ips, $i, "ip");
	$entries = mysql_query("SELECT id, username, unix_timestamp(time) as utime FROM `failedlogins` WHERE `ip`='$loginip' ORDER BY `utime` DESC LIMIT 3;");
	
	if(mysql_num_rows($entries) == 3) {
		$time1 = (int)mysql_result($entries, 0, "utime");
		$time3 = (int)mysql_result($entries, 2, "utime");
		$now = time();
		if($time1 - $time3 <= 3600 && $now - $time1 <= 3600*24*7) {
			$ids = array(mysql_result($entries, 0, "id"), mysql_result($entries, 1, "id"), mysql_result($entries, 2, "id"));
			$ids = implode(",", $ids);
			$u1 = mysql_result($entries, 0, "username");
			$u2 = mysql_result($entries, 1, "username");
			$u3 = mysql_result($entries, 2, "username");
			$ip = htmlspecialchars($loginip);
			$fulluser = htmlspecialchars(implode(', ', array($u1, $u2, $u3)));
			if($u1 === "" && $u2 === "")
				$fulluser = htmlspecialchars($u3);
			$unbantime = date("Y-m-d H:i T", $time1 + 3600*24*7);
			$banlist[] = <<<BAN
<form method="POST" action="">
<input type="hidden" name="ids" value="$ids" />
<tr>
	<td>$ip</td>
	<td>$fulluser</td>
	<td>$unbantime</td>
	<td><input type="submit" name="subbut" value="Unban" /></td>
</tr>
</form>
BAN;
		}
	}
	
	
	$i++;
}

$banlist = implode("\n", $banlist);

$sitestr = <<<SITESTR
<!DOCTYPE html>
<html>
	<head>
		<title>Admin</title>
                <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
                <link rel="stylesheet" href="style.css" type="text/css" />

                <link rel="stylesheet" href="/css/bootstrap.min.css" type="text/css" />

	</head>
	<body>
		$main_menu
		<br/>
		<form action="" method="POST">
		Ban ip: <input type="text" name="ip" maxlength="45" /><br>
		Until: <input type="text" name="date" /> (YYYY-MM-DD)<br>
		Reason: <input type="text" name="reason" maxlength="100" /><br>
		<input type="submit" name="subbut" value="Ban" /><br>
		</form><br>
		<table border="0" cellspacing="0" cellpadding="2">
			<tr>
				<th width="300px">IP</th>
				<th width="300px">Usernames used/Ban reason</th>
				<th width="300px">Unbanned at</th>
				<th>Unban</th>
			</tr>
$banlist
		</table>
	</body>
</html>
SITESTR;

echo $sitestr;
?>
