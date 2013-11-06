<?php
include_once("../res/common.php");
include_once("adminc.php");

if($_SESSION['login'] == 0) {
	header("Location: /x_admin");
	return;
}
if($_SESSION['privileges'] < 1) {
	header("Location: /x_admin/main.php");
	return;
}

$insert_row = "";

if(isset($_POST['opass'])) {
	$regex = "/^[a-zA-Z0-9_]*$/";
	$id = $_SESSION['userid'];
	
	$oldpass = urldecode($_POST['opass']);
	$newpass = urldecode($_POST['npass']);
	$newpass2 = urldecode($_POST['npass2']);
	
	mysql_connect($dbip, $dbuser, $dbpass);
	@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
	
	
	
	$stored = mysql_query("SELECT `pass` FROM `users` WHERE `id`='$id';");
	$storedpass = mysql_result($stored, 0, "pass");
	
	if(PassHash::Compare($oldpass, $storedpass)) {
		if($newpass == $newpass2) {
			if(count(preg_match($regex, $newpass)) == 1) {
				$pass = PassHash::Hash($newpass);
				mysql_query("UPDATE `users` SET `pass`='$pass' WHERE `id`='$id';");
				$insert_row = '<tr><td colspan="2">New password set.</td></tr>';
			}
			else {
				$insert_row = '<tr><td colspan="2">The new password was not alphanumeric. (a-z, A-Z, 0-9 and underscore)</td></tr>';
			}
		}
		else {
			$insert_row = '<tr><td colspan="2">The new password did not match the confirmation.</td></tr>';
		}
	}
	else {
		$insert_row = '<tr><td colspan="2">The old password was incorrect.</td></tr>';
	}
	
	mysql_close();
	
}

$site = <<<SITESTR
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
		<table border="0" cellspacing="0" cellpadding="2" style="width: 500px !important;">
$insert_row
			<form action="" method="POST">
			<tr>
				<td>Old password:</td>
				<td><input name="opass" type="password" /></td>
			</tr>
			<tr>
				<td>New password:</td>
				<td><input name="npass" type="password" /></td>
			</tr>
			<tr>
				<td>New password again:</td>
				<td><input name="npass2" type="password" /></td>
			</tr>
			<tr>
				<td colspan="2" align="center"><input value="Submit" type="submit" /></td>
			</tr>
			</form>
		</table>
	</body>
</html>
SITESTR;

echo $site;

?>

