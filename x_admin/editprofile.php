<?php
include_once("../res/common.php");
include_once("adminc.php");

if($_SESSION['login'] == 0) {
	header("Location: /x_admin");
	return;
}
if($_SESSION['privileges'] < 2) {
	header("Location: /x_admin/main.php");
	return;
}

$uid = $_SESSION['userid'];

mysql_connect($dbip, $dbuser, $dbpass);
@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");



$insert_row = "";

$user = mysql_query("SELECT * FROM `users` WHERE `id`=$uid;");

if(mysql_num_rows($user) == 0) {
	header("Location: /x_admin/main.php");
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
		
		if(is_null(mysql_result($user, 0, "djid"))) { // do INSERT
			if(!isset($newfilename)) {
				$insert_row = '<tr><td colspan="2" align="center">You need to upload an image.</td></tr>';
			}
			else {
				mysql_query("INSERT INTO `djs` (djname, djtext, djimage, visible, priority) VALUES ('$djname', '$djtext', '$newfilename', '0', '200');");
				$last = mysql_query("SELECT * FROM `djs` ORDER BY `id` DESC LIMIT 1");
				$the_id = mysql_result($last, 0, "id");
				mysql_query("UPDATE `users` SET `djid`='$the_id' WHERE `id`=$uid;");
				
				mysql_close();
				header("Location: /x_admin/editprofile.php");
				exit();
			}
		}
		else { // do UPDATE
			$djid = mysql_result($user, 0, "djid");
			$extra_set = "";
			if(isset($newfilename)) {
				$extra_set = ", `djimage`='$newfilename'";
			}
			mysql_query("UPDATE `djs` SET `djname`='$djname', `djtext`='$djtext' $extra_set WHERE `id`=$djid");

			mysql_close();
			header("Location: /x_admin/editprofile.php");
			exit();
		}
		mysql_close();
		
	}
}




mysql_connect($dbip, $dbuser, $dbpass);
@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");



$djid = mysql_result($user, 0, "djid");

if(is_null($djid)) {
	$dj_table = <<<DJTABLE
		<table border="0" cellspacing="0" cellpadding="2" style="width: 550px !important;" >
		<form action="" method="post" enctype="multipart/form-data">
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
				<td align="center" colspan="2"><input type="submit" name="subbut" value="Save" /></td>
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

	if($vis == "1") {
		$p1c = "checked";
		$p0c = "";
	}
	if($vis == "0") {
		$p1c = "";
		$p0c = "checked";
	}
	
	$dj_table = <<<DJTABLE
		<table border="0" cellspacing="0" cellpadding="2" style="width: 550px !important;">
		<form action="" method="post" enctype="multipart/form-data" >
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
				<td align="center" colspan="2"><input type="submit" name="subbut" value="Save" /></td>
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
		Please keep the images 150x150, there is nothing that checks this when uploading.<br/>
$dj_table
	</body>
</html>
SITESTR;

echo $site;

?>
