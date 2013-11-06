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

header("Cache-Control: no-cache");

if(isset($_POST['subbut'])) {
	if($_POST['subbut'] == "Save") {
		mysql_connect($dbip, $dbuser, $dbpass);
		@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
		

		$newsid = mysql_real_escape_string($_POST['newsid']);
		$newsheader = mysql_real_escape_string(urldecode($_POST['headedit']));
		$newstext = mysql_real_escape_string(urldecode($_POST['newsedit']));
		
		mysql_query("UPDATE `news` SET `header`='$newsheader', `newstext`='$newstext' WHERE `id`=$newsid;");
		
		mysql_close();
		header("Location: /x_admin/news.php");
		return;
	}
	else if($_POST['subbut'] == "Delete") {
		mysql_connect($dbip, $dbuser, $dbpass);
		@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
		
		
		
		$newsid = mysql_real_escape_string($_POST['newsid']);
		
		mysql_query("DELETE FROM `comments` WHERE nid=$newsid;");
		mysql_query("DELETE FROM `news` WHERE `id`=$newsid;");
		
		mysql_close();
		header("Location: /x_admin/news.php");
		return;
	}
}



mysql_connect($dbip, $dbuser, $dbpass);
@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");



$news = mysql_query("SELECT * FROM `news` ORDER BY `time` DESC;");
$num = mysql_num_rows($news);
$i = 0;

$news_tables = "";

while($i < $num) {
	$id = mysql_result($news, $i, "id");
	$head = htmlspecialchars(mysql_result($news, $i, "header"), ENT_COMPAT);
	$time = date("D j M, H:i", strtotime(mysql_result($news, $i, "time")));
	$newstext = htmlspecialchars(mysql_result($news, $i, "newstext"));
	
	$news_table = <<<NEWSTABLE
		<table border="0" cellspacing="0" cellpadding="2" style="width: 650px !important;">
		<form action="" method="POST">
		<input type="hidden" name="newsid" value="$id" />
			<tr>
				<td style="width:120px">Header: (50c max)</td>
				<td><input type="text" name="headedit" value="$head" maxlength="50" /></td>
			</tr>
			<tr>
				<td>Submitted time:</td>
				<td>$time</td>
			</tr>
			<tr>
				<td>Newstext:</td>
				<td><textarea name="newsedit" cols="250" rows="15" style="width:100%" >$newstext</textarea> </td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" name="subbut" value="Save" /><input type="submit" name="subbut" value="Delete" /></td>
			</tr>
		</form>
		</table>
NEWSTABLE;

	$news_tables = $news_tables . $news_table . "<br/>\n";
	
	$i = $i + 1;
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
		<br>
		<a href="/x_admin/addnews.php">Add news item</a><br>
		Putting "TRUNCATE" (no quotes) in a news post will make the post truncate on the main page.<br>

		All Images and links to the site should be protocol-agnostic (use //r-a-d.io instead of http:/https:)<br>

$news_tables
	</body>
</html>
SITESTR;

echo $site;

?>
