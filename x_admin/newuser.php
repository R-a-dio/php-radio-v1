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
$dev = ($_SESSION["privileges"] >= 5) ? "" : "disabled";
$insert_row = "";

if(isset($_POST['user'])) {
	mysql_connect($dbip, $dbuser, $dbpass);
	@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
	
	
	
	$user = mysql_real_escape_string(urldecode($_POST['user']));
	$pass = urldecode($_POST['pass']);
	$priv = mysql_real_escape_string(urldecode($_POST['priv']));
	
	if(!is_numeric($priv)) {
		header("Location: /");
		return;
	}
	if ($priv > $_SESSION["privileges"]) {
		die("Sneaky Sneaky.");
	}

	$existtest = mysql_query("SELECT * FROM `users` WHERE `user`='$user';");
	
	if(mysql_num_rows($existtest) == 0) {
		if($user == "") {
			$insert_row = '<tr><td colspan="2" align="center">You need to specify a username.</td></tr>';
		}
		else {
			if($pass == "") {
				$insert_row = '<tr><td colspan="2" align="center">You need to specify a password.</td></tr>';
			}
			else {
				$hashpass = PassHash::Hash($pass);
				mysql_query("INSERT INTO `users` (user, pass, privileges) VALUES ('$user', '$hashpass', $priv);");
				mysql_close();
				header("Location: /x_admin/users.php");
				return;
			}
		}
	}
	else {
		$insert_row = '<tr><td colspan="2" align="center">That username already exists.</td></tr>';
	}
	
	mysql_close();
}



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
		<a href="/x_admin/users.php">Back</a>
		<br/>
		<form action="" method="POST">
		<table border="0" cellspacing="0" cellpadding="2">
			<tr>
				<th colspan="2">New user</th>
			</tr>
			$insert_row
			<tr>
				<td>Username:</td>
				<td><input name="user" type="text" /></td>
			</tr>
			<tr>
				<td>Password:</td>
				<td><input name="pass" type="text" /></td>
			</tr>
			<tr>
				<td>Privileges:</td>
				<td>
					<select name="priv">
						<option value="0">None</option>
						<option value="1">Pending tracks</option>
						<option value="2">DJ</option>
						<option value="3">Newsposter</option>
						<option value="4">Admin (users)</option>
						<option $dev value="5">Admin (devs)</option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center"><input type="submit" value="Create" /></td>
			</tr>
		</table>
		</form>
	</body>
</html>
SITESTR;

echo $site;

?>
