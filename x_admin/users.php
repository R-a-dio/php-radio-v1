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
$dev = ($_SESSION['privileges'] >= 5) ? True : False;
header("Cache-Control: no-cache");

if(isset($_POST['subbut'])) {
	if($_POST['subbut'] == "Save") {
		mysql_connect($dbip, $dbuser, $dbpass);
		@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
		
		
		
		$userid = mysql_real_escape_string(urldecode($_POST['uid']));
		$pass = urldecode($_POST['passchange']);
		$newpriv = mysql_real_escape_string(urldecode($_POST['privchange']));
		if ($newpriv > $_SESSION["privileges"]) {
			die("Nice Try.");
		}
		if($pass == "") {
			mysql_query("UPDATE `users` SET `privileges`='$newpriv' WHERE `id`='$userid';");
		}
		else {
			$hashpass = PassHash::Hash($pass);
			mysql_query("UPDATE `users` SET `privileges`='$newpriv', `pass`='$hashpass' WHERE `id`=$userid;");
		}
		
		mysql_close();
		header("Location: /x_admin/users.php");
		return;
	}
	else if($_POST['subbut'] == "Delete") {
		mysql_connect($dbip, $dbuser, $dbpass);
		@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
		
		
		
		$userid = mysql_real_escape_string($_POST['uid']);
		
		mysql_query("DELETE FROM `users` WHERE `id`=$userid;");
		
		mysql_close();
		header("Location: /x_admin/users.php");
		return;
	}
}



mysql_connect($dbip, $dbuser, $dbpass);
@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");



$users = mysql_query("SELECT * FROM `users`");
$num = mysql_num_rows($users);
$i = 0;

$table_rows = "";

while($i < $num) {
	$userid = mysql_result($users, $i, "id");
	$username = htmlspecialchars(mysql_result($users, $i, "user"));
	$priv = mysql_result($users, $i, "privileges");
	$djid = mysql_result($users, $i, "djid");
	
	if($priv > 5)
		$priv = 5;
	
	$sel[0] = $sel[1] = $sel[2] = $sel[3] = $sel[4] = $sel[5] = "";
	if (!$dev) { $sel[5] = "disabled "; }
	$sel[$priv] .= 'selected';

	$priv_sel = <<<PRIVSEL
					<select name="privchange">
						<option $sel[0] value="0">None</option>
						<option $sel[1] value="1">Pending tracks</option>
						<option $sel[2] value="2">DJ</option>
						<option $sel[3] value="3">Newsposter</option>
						<option $sel[4] value="4">Admin (users)</option>
						<option $sel[5] value="5">Admin (devs)</option>
					</select>
PRIVSEL;
	
	if(is_null($djid)) {
		$isdj_field = "No";
		$dj_but = "Add";
	}
	else {
		$isdj_field = "Yes";
		$dj_but = "Edit";
	}

	$dj_row = <<<DJROW
			<form action="" method="POST">
			<input type="hidden" value="$userid" name="uid" />
			<tr>
				<td>$djid&nbsp;</td>
				<td>$username</td>
				<td><input type="text" name="passchange" value="" /></td>
				<td>
$priv_sel
				</td>
				<td>$isdj_field</td>
				<td align="center"><a href="djedit.php?uid=$userid">$dj_but</a></td>
				<td align="center"><input type="submit" name="subbut" value="Save" /></td>
				<td align="center"><input type="submit" name="subbut" value="Delete" /></td>
			</tr>
			</form>
DJROW;
	
	$table_rows = $table_rows . $dj_row . "\n";
	
	$i = $i + 1;
}

mysql_close();

$site = <<<SITESTR
<html>
	<head>
		<title>Admin</title>
                <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
                <link rel="stylesheet" href="style.css" type="text/css" />

                <!-- <link rel="stylesheet" href="/css/bootstrap.min.css" type="text/css" /> -->

	</head>
	<body>
		$main_menu
		<br/>
		<a href="newuser.php">Add new user</a><br/>
		<table border="0" cellspacing="0" cellpadding="2">
			<tr>
				<th width="40px">DJ#</th>
				<th width="150px">Username</th>
				<th width="150px">Change password</th>
				<th width="120px">Privileges</th>
				<th width="120px">Has DJ entry</th>
				<th width="100px">DJ entry</th>
				<th>Save edits</th>
				<th>Delete user</th>
			</tr>
$table_rows
		</table>
	</body>
</html>
SITESTR;

echo $site;

?>
