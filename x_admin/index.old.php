<?php
include_once("../res/common.php");
/*
0: no one
1: can accept pending songs/edit music database
2: can dj?
3: can post news
4: can add/edit users

login
1+: main						main.php
1+: logout						logout.php
1+: change password				chpwd.php
1+: accept pending				pending.php
1+: view db						viewdb.php
3+: news						news.php/addnews.php
4+: edit users/set dj info		users.php/newuser.php/djedit.php
*/

$insert_row = "";

if($_SESSION['login'] == 1) {
	header('Location: /x_admin/main.php');
	return;
}

if(isset($_POST['username'])) {
	mysql_connect($dbip, $dbuser, $dbpass);

	@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
	
	
	
	$user = urldecode($_POST['username']);
	$pass = urldecode($_POST['password']);
	if(get_magic_quotes_gpc()) {
		$user = stripslashes($user);
	}
	$user = mysql_real_escape_string($user);
	
	$pass = sha1($pass);
	
	$userdata = mysql_query("SELECT * FROM `users` WHERE `user`='$user';");
	if(mysql_num_rows($userdata) > 0) {
		$dbpass = mysql_result($userdata, 0, "pass");
		
		if($dbpass == $pass) {
			$_SESSION['login'] = 1;
			$_SESSION['user'] = mysql_result($userdata, 0, "user");
			$_SESSION['userid'] = mysql_result($userdata, 0, "id");
			$_SESSION['privileges'] = mysql_result($userdata, 0, "privileges");
			mysql_close();
			header("Location: /x_admin/main.php");
			return;
		}
		else {
			$insert_row = "<tr><td colspan=\"2\" align=\"center\">Invalid username or password</td></tr>";
		}
	}
	else {
		$insert_row = "<tr><td colspan=\"2\" align=\"center\">Invalid username or password</td></tr>";
	}
	
	mysql_close();
}

$site = <<<SITESTR
<html>
	<head>
		<title>Admin</title>
	</head>
	<body>
		<center>
			<form action="" method="POST">
			<table border="0">
				<tr>
					<th colspan="2" align="center">Login</th>
				</tr>
$insert_row
				<tr>
					<td>Username:</td>
					<td><input name="username" type="text" maxlength="40" /></td>
				</tr>
				<tr>
					<td>Password:</td>
					<td><input name="password" type="password" maxlength="60" /></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><input type="submit" value="Log in" /></td>
				</tr>
			</table>
			</form>
		</center>
	</body>
</html>
SITESTR;

echo $site;

?>