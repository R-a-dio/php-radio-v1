<?php
include_once("../res/common.php");
/*
0: no one
1: can accept pending songs/edit music database
2: can dj?
3: can post news
4: can add/edit users
5: dev access

login
1+: main						main.php
1+: logout						logout.php
1+: change password				chpwd.php
1+: accept pending				pending.php
1+: view db						viewdb.php
3+: news						news.php/addnews.php
4+: edit users/set dj info		users.php/newuser.php/djedit.php
*/

/* HTTPS forced for logins. */
if ($_SERVER['SERVER_PORT'] != 443) {
  header("Location: https://r-a-d.io/x_admin/");
}

if($_SESSION['login'] == 1) {
	header('Location: /x_admin/main.php');
        die();
	return;
}
$extras = "";
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
	
	$userdata = mysql_query("SELECT * FROM `users` WHERE `user`='$user';");
	if(mysql_num_rows($userdata) > 0) {
		$dbpass = mysql_result($userdata, 0, "pass");
		
		if(PassHash::Compare($pass, $dbpass)) {
			$_SESSION['login'] = 1;
			$_SESSION['user'] = mysql_result($userdata, 0, "user");
			$_SESSION['userid'] = mysql_result($userdata, 0, "id");
			$_SESSION['privileges'] = mysql_result($userdata, 0, "privileges");
			mysql_close();
			header("Location: /x_admin/main.php");
			return;
		}
		else {
                    $extras = <<<EXTRA
                <div class="alert alert-error">
                    Username or Password incorrect.
                </div>
EXTRA;

		}
	}
	else {
		$extras = <<<EXTRA
                <div class="alert alert-error">
                    Username or Password incorrect.
                </div>
EXTRA;
	}
	
	if($insert_row !== "") { // login must have failed
		mysql_query("INSERT INTO `failedlogins` (ip, time, username) VALUES ('$REMOTE_ADDR', NOW(), '$user');");
	}
	
	mysql_close();
}
/*
$site = <<<SITESTR
<!DOCTYPE html><html><head>
	<meta charset="utf-8" />
	<title>prove your worth</title>
	<style>
html,body,#cnt,#cnt tr,#cnt td {
	width: 100%;
	height: 100%;
	padding: 0;
	margin: 0;
	font-family: sans-serif;
	text-align: center;
	color: #000;
	background::#333;
}
html {
	line-height: 2em;
}
</style></head><body><table id="cnt"><tr><td>
	{$extras}
	<form action="" method="POST">
		USER <input name="username" type="text" maxlength="40"><br>
		PASS <input name="password" type="password" maxlength="60"><br>
		<input type="submit" value="Log in">
	</form>
</td></tr></table></body></html>
SITESTR;

echo $site;
*/
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>XBOX GO HOME</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
	<link href="/css/bootstrap-2.2.2.css" rel="stylesheet">
        <link href="/css/bootstrap-2.2.2-responsive.css" rel="stylesheet">
	<style type="text/css">
      .form-signin {
        max-width: 300px;
        padding: 19px 29px 29px;
        margin: 0 auto 20px;
        background-color: #fff;
        border: 1px solid #e5e5e5;
        -webkit-border-radius: 5px;
           -moz-border-radius: 5px;
                border-radius: 5px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
      }
	</style>

</head>
<body>
	<hr>
        <div class="container">
            <form class="form-signin" method="POST">
                <h2 class="form-signin-heading">Here Be Dragons</h2>
		<?= $extras ?>
                <div class="input-prepend">
                    <span class="add-on"></span>
                    <input type="text" class="input-block-level" placeholder="Username" name="username">
                </div>
                <div class="input-prepend">
                    <span class="add-on"></span>
                    <input type="password" class="input-block-level" placeholder="Password" name="password">
                </div>
                <hr>
                <button class="btn btn-large btn-primary" type="submit" style="width:40%">Sign in</button>
            </form>
        </div> 
</body>
</html>
