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

if(!isset($_GET['uid'])) {
	header("Location: /x_admin/users.php");
	return;
}

mysql_connect($dbip, $dbuser, $dbpass);
@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");



$insert_row = "";

$uid = mysql_real_escape_string($_GET['uid']);
$user = mysql_query("SELECT * FROM `users` WHERE `id`=$uid;");

if(mysql_num_rows($user) == 0) {
	header("Location: /x_admin/users.php");
	mysql_close();
	exit();
}

mysql_close();

if(isset($_POST['subbut'])) {
	if($_POST['subbut'] == "Save") {
		//*******
		if($_FILES['djimage']['error'] == 0) {
			$filename = strtolower($_FILES['djimage']['name']);
			if(substr(strrchr($filename, '.'), 1) == "jpg" || substr(strrchr($filename, '.'), 1) == "png" || substr(strrchr($filename, '.'), 1) == "jpeg" || substr(strrchr($filename, '.'), 1) == "gif") {
				if(substr(strrchr($filename, '.'), 1) == "jpg" || substr(strrchr($filename, '.'), 1) == "jpeg")
					$ext = ".jpeg";
				else if(substr(strrchr($filename, '.'), 1) == "gif")
					$ext = ".gif";
				else
					$ext = ".png";
				
				$newfilename = str_rand(6, 'alphanum') . $ext;
				$newfilepath = $_SERVER["DOCUMENT_ROOT"] . "/res/img/dj/" . $newfilename;
				
				move_uploaded_file($_FILES["djimage"]["tmp_name"], $newfilepath);
			}
		}
		//*******
		mysql_connect($dbip, $dbuser, $dbpass);
		@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
		
		
		
		$djname = mysql_real_escape_string(urldecode($_POST['djname']));
		$djtext = mysql_real_escape_string(urldecode($_POST['djtext']));
		$vis = mysql_real_escape_string(urldecode($_POST['vis']));
		$prio = mysql_real_escape_string(urldecode($_POST['prio']));
		$css = mysql_real_escape_string(urldecode($_POST['css']));
		
		if(!is_numeric($prio) || $prio < 0) {
			$insert_row = '<tr><td colspan="2" align="center">Invalid priority.</td></tr>';
		}
		else if(!($vis == "0" || $vis == "1")) {
			$insert_row = '<tr><td colspan="2" align="center">Invalid visibility. (how\'d you do that?)</td></tr>';
		}
		else {		
			if(is_null(mysql_result($user, 0, "djid"))) { // do INSERT
				if(!isset($newfilename)) {
					$insert_row = '<tr><td colspan="2" align="center">You need to upload an image.</td></tr>';
				}
				else {
					mysql_query("INSERT INTO `djs` (djname, djtext, djimage, visible, priority, css) VALUES ('$djname', '$djtext', '$newfilename', '$vis', '$prio', '$css');");
					$last = mysql_query("SELECT * FROM `djs` ORDER BY `id` DESC LIMIT 1");
					$the_id = mysql_result($last, 0, "id");
					mysql_query("UPDATE `users` SET `djid`='$the_id' WHERE `id`=$uid;");
					
					mysql_close();
					header("Location: /x_admin/djedit.php?uid=$uid");
					exit();
				}
			}
			else { // do UPDATE
				$djid = mysql_result($user, 0, "djid");
				$extra_set = "";
				if(isset($newfilename)) {
					$extra_set = ", `djimage`='$newfilename'";
				}
				mysql_query("UPDATE `djs` SET `djname`='$djname', `djtext`='$djtext', `visible`='$vis', `priority`='$prio', `css`='$css' $extra_set WHERE `id`=$djid");

				mysql_close();
				header("Location: /x_admin/djedit.php?uid=$uid");
				exit();
			}
		}
		mysql_close();
		
	}
	else if($_POST['subbut'] == "Delete") {
		mysql_connect($dbip, $dbuser, $dbpass);
		@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
		
		
		
		if(is_null(mysql_result($user, 0, "djid"))) {
			header("Location: /x_admin/users.php");
			mysql_close();
			exit();
		}
		else {
			$djid = mysql_result($user, 0, "djid");
			$djinfo = mysql_query("SELECT * FROM `djs` WHERE `id`=$djid;");
			unlink(mysql_result($djinfo, 0, "djimage"));
			mysql_query("DELETE FROM `djs` WHERE `id`=$djid;");
			mysql_query("UPDATE `users` SET `djid`=NULL WHERE `id`=$uid;");
			
			mysql_close();
			header("Location: /x_admin/djedit.php?uid=$uid");
			exit();
		}
	}
}

mysql_connect($dbip, $dbuser, $dbpass);
@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");



$djid = mysql_result($user, 0, "djid");

if(is_null($djid)) {
	$dj_table = <<<DJTABLE
		<table border="0" cellspacing="0" cellpadding="2" style="width:550px !important;">
		<form action="djedit.php?uid=$uid" method="post" enctype="multipart/form-data">
			<tr>
				<td colspan="2" align="center">Edit DJ info</td>
			</tr>
$insert_row
			<tr>
				<td>DJ name:</td>
				<td><input type="text" name="djname" value="" /></td>
			</tr>
			<tr>
				<td>DJ text:</td>
				<td><textarea name="djtext" cols="30" rows="4"></textarea></td>
			</tr>
			<tr>
				<td>Image:</td>
				<td><input type="file" name="djimage" /></td>
			</tr>
			<tr>
				<td>Visible on DJ page:</td>
				<td>
					<input type="radio" name="vis" value="1" /> Yes<br/>
					<input type="radio" name="vis" value="0" checked /> No<br/>
				</td>
			</tr>
			<tr>
				<td>DJ page priority:</td>
				<td><input type="text" name="prio" value="200" /></td>
			</tr>
			<tr>
				<td>Custom CSS:</td>
				<td><input type="text" name="css" value="" /></td>
			</tr>
			<tr>
				<td align="center" colspan="2"><input type="submit" name="subbut" value="Save" /><input type="submit" name="subbut" value="Delete" /></td>
			</tr>
		</form>
		</table>
DJTABLE;
}
else {
	$djinfo = mysql_query("SELECT * FROM `djs` WHERE `id`=$djid;");
	
	$djname = htmlspecialchars(mysql_result($djinfo, 0, "djname"));
	$djtext = htmlspecialchars(mysql_result($djinfo, 0, "djtext"));
	$vis = htmlspecialchars(mysql_result($djinfo, 0, "visible"));
	$prio = htmlspecialchars(mysql_result($djinfo, 0, "priority"));
	$css = htmlspecialchars(mysql_result($djinfo, 0, "css"));

	if($vis == "1") {
		$p1c = "checked";
		$p0c = "";
	}
	if($vis == "0") {
		$p1c = "";
		$p0c = "checked";
	}
	
	$dj_table = <<<DJTABLE
		<table border="0" cellspacing="0" cellpadding="2" style="width:550px !important;">
		<form action="djedit.php?uid=$uid" method="post" enctype="multipart/form-data">
			<tr>
				<td colspan="2" align="center">Edit DJ info</td>
			</tr>
$insert_row
			<tr>
				<td>DJ name:</td>
				<td><input type="text" name="djname" value="$djname" /></td>
			</tr>
			<tr>
				<td>DJ text:</td>
				<td><textarea name="djtext" cols="30" rows="4">$djtext</textarea></td>
			</tr>
			<tr>
				<td>Image:</td>
				<td><input type="file" name="djimage" /></td>
			</tr>
			<tr>
				<td>Visible on DJ page:</td>
				<td>
					<input style="width:auto" type="radio" name="vis" value="1" $p1c /> Yes<br>
					<input type="radio" name="vis" value="0" $p0c /> No<br>
				</td>
			</tr>
			<tr>
				<td>DJ page priority:</td>
				<td><input type="text" name="prio" value="$prio" /></td>
			</tr>
			<tr>
				<td>Custom CSS:</td>
				<td><input type="text" name="css" value="$css" /></td>
			</tr>
			<tr>
				<td align="center" colspan="2"><input type="submit" name="subbut" value="Save" /><input type="submit" name="subbut" value="Delete" /></td>
			</tr>
		</form>
		</table>
DJTABLE;
	
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
		<a href="/x_admin/users.php">Back</a><br/>
		Please keep the images 150x150, there is nothing that checks this when uploading.<br/>
$dj_table
	</body>
</html>
SITESTR;

echo $site;

?>
