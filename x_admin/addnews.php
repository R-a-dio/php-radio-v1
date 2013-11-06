<?php
include_once("../res/common.php");
include_once("adminc.php");

if($_SESSION['login'] == 0) {
	header("Location: /x_admin");
	return;
}
if($_SESSION['privileges'] < 3) {
	header("Location: /x_admin/main.php");
	return;
}

$insert_row = "";
$h_text = "";
$t_text = "";

if(isset($_POST['header'])) {
	mysql_connect($dbip, $dbuser, $dbpass);
	@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
	
	
	
	$header = mysql_real_escape_string(urldecode($_POST['header']));
	$text = mysql_real_escape_string(urldecode($_POST['newstext']));
	
	$h_text = htmlspecialchars(urldecode($_POST['header']));
	$t_text = htmlspecialchars(urldecode($_POST['newstext']));

	if($header == "") {
		$insert_row = '<tr><td colspan="2" align="center">You need to enter a header.</td></tr>';
	}
	else {
		if($text == "") {
			$insert_row = '<tr><td colspan="2" align="center">You need to enter a news text.</td></tr>';
		}
		else {
			mysql_query("INSERT INTO `news` (header, newstext) VALUES ('$header', '$text');");
			mysql_close();
			header("Location: /x_admin/news.php");
			return;
		}
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
		<a href="/x_admin/news.php">Back</a>
		<br/>
		<form action="" method="POST">
		<table border="0" cellspacing="0" cellpadding="2">
			<tr>
				<th colspan="2">New news item</th>
			</tr>
			$insert_row
			<tr>
				<td>Header: (50c max)</td>
				<td><input name="header" type="text" value="$h_text" maxlength="50" /></td>
			</tr>
			<tr>
				<td>News text:</td>
				<td><textarea name="newstext" cols="50" rows="5" >$t_text</textarea></td>
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
