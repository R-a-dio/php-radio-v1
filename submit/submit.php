<?php
include_once("../res/common.php");
require_once('../res/getid3/getid3.php');

/*$submitmessage = <<<MESSAGE
			<p>You can only upload one song every hour. The song also has to be reviewed before you can request it; this can take a few days or more.</p>
			<p>If you supply the source (anime, VN, whatever) in the comment, it will speed things up dramatically for us.</p>
			<p>Keep in mind that the maximum file size is 15MB; anything over that will be automatically rejected.</p>
			<p><b>Please make sure to check the existing database for your song before uploading it.<br />
			Also please make sure to mention the artist when you are uploading a cover, we are having a hard time finding out the artist without.</b></p>
			<p>Don't know how to tag MP3 files? <a href="/submit/tagging.html" target="_blank">Here's a guide!</a></p>
MESSAGE;*/

$submitmessage = <<<MESSAGE
<p>You can only upload one song every hour. Before the song is submitted into the database it must be reviewed. This can take anywhere from a few hours to a day or two, depending on how many songs are pending.</p>
<ul>
<li><b>Make sure that the song isn't already in the database.</b> You can use the search bar located at the top right of the page to check. Keep in mind that songs in runes are often romanized, so try searching for the romanization of the song as well.</li>
<li><b>The song you are uploading has to be at an acceptable bitrate.</b> Nothing below 192 kbps is to be accepted. There are a few rare instances, such as a song being incredibly hard to find in higher quality, but for the most part songs below 192kbps are discarded almost immediately.</li>
<li><b>Please include the source (anime, VN, whatever) of the song in the comment field.</b> This helps speed the process up dramatically. Any other information you deem helpful can be included as well.</li>
<li><b>Accepted songs</b> are under 'Latest accepted songs'. Good uploads (well tagged, good quality, helpful comment etc.) are marked in <span class="good-song">green</span>. Songs that were replacements are marked in <span class="replace-song">teal</span>.</li>
<li><b>Declined songs</b> can be found under 'Latest declined songs' with a reason for their denial.</li>
<li>If you have any specific questions, feel free to ask one of the staff in charge of the database on IRC.</li>
</ul>
<p class="text-warning">If you need to upload multiple songs, <u>Ask us first</u>. Using a proxy will result in mass-declining</p>
<p>Don't know how to tag MP3 files? <a href="https://www.r-a-d.io/submit/tagging.html">Here's a guide!</a> (for Windows)</p>
<p>Occasionally, we find songs in the database that aren't quite up to snuff. In that case, we mark them for replacement, and they show up to the right under 'Specify song replacement'. If you believe you have a high quality version of this song, please select it and upload it. We appreciate it!</p>
MESSAGE;

$rel_music_dir = $_SERVER["DOCUMENT_ROOT"]."/".$music_file_dir;

$site_text = "Error: The file exceeded the maximum post size.";
if(isset($_POST['comment'])) {
	
	$site_text = "Thank you for your upload!";
	
	if($_FILES['submitted']['error'] != 0) {
		switch($_FILES['submitted']['error']) {
			case 1:
			case 2:
				$site_text = "Error: The file exceeded the maximum file size.";
				break;
			case 4:
				$site_text = "Error: You forgot to select a file.";
				break;
			case 3:
			case 6:
			case 7:
			case 8:
				$site_text = "Error: An unknown error occurred. (errno. " . $_FILES['submitted']['error'] . ")";
				break;
		}
	}
	else { //no upload error
		mysql_connect($dbip, $dbuser, $dbpass);
		@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
		
		
		
		$comment = urldecode($_POST['comment']);
		if(get_magic_quotes_gpc())
			$comment = stripslashes($comment);
		$comment = mysql_real_escape_string($comment);
		
		$temp_file = $_FILES['submitted']['tmp_name'];
		$filename = $_FILES['submitted']['name'];
		$mime = $_FILES['submitted']['type'];
		
		
		
		if(substr(strrchr($filename, '.'), 1) != "mp3") {
			$site_text = "Error: The uploaded file is not an MP3 file. (PHP)";
		}
		else { //mime type is correct
			$lutime = mysql_query("SELECT `time` FROM `uploadtime` WHERE `ip`='$REMOTE_ADDR';");
			if(mysql_num_rows($lutime) > 0) {
				$time = strtotime(mysql_result($lutime, 0, "time"));
			}
			else {
				$time = 0;
			}
			$now = time();

			// I'm tired of manual cookie exporting to curl  --ed
			if(!isset($_GET['idontlikebackdoorsokay']) &&
				$_POST['daypass'] != DAYPASS &&
				$now - $time < $upload_delay &&
				$_SESSION['privileges'] == 0) {

				$remain = (int)($upload_delay / 60) - floor(($now - $time) / 60);
				$site_text = "Error: You need to wait another $remain minutes before uploading again.";
			}
			else { //request delay has passed OR we are logged in OR we have a valid daypass
				$result = Array();
				// call metadata which is a python file
				// return artist, title, album, mimetype
				exec("/radio/users/hanyuu/env/python/metadata/bin/python /radio/www/scripts/metadata.py '" . $temp_file . "' 2> /radio/www/logs/metadata.log", $result);
				$filemime = "";
				$artist = "";
				$album = "";
				$title = "";
				if (isset($result[3])) {
					$artist = $result[0];
					$title = $result[1];
					$album = $result[2];
					$filemime = $result[3];
					//error_log("Artist: " . $artist);
					//error_log("Album: " . $album);
					//error_log("Title: " . $title);
					//error_log("mime: " . $filemime);
				}
				if($filemime == "audio/mp3") {
					do {
						$randname = str_rand(15,'alphanum') . ".mp3";
						$new_filename  = $rel_music_dir . "pend/" . $randname;
						$new_filename2 = $rel_music_dir . $randname;
					} while (file_exists($new_filename) || file_exists($new_filename2));
					//echo $new_filename;
					move_uploaded_file($temp_file, $new_filename);
					chmod($new_filename, 0664);
					
					$artist = substr(mysql_real_escape_string($artist), 0, 200);
					$title = substr(mysql_real_escape_string($title), 0, 200);
					$album = substr(mysql_real_escape_string($album), 0, 200);
					$path = mysql_real_escape_string(substr($new_filename, strrpos($new_filename, "/") + 1, strlen($new_filename)));
					$oldname = mysql_real_escape_string($filename);
					
					//echo relativate($_SERVER["DOCUMENT_ROOT"], $new_filename);
					
					if($artist != "" or $title != "") {
						$dupe = mysql_query("SELECT * FROM `tracks` WHERE `artist`='$artist' AND `track`='$title';");
						if(mysql_num_rows($dupe) > 0) 
							$dupe_flag = 1;
						else
							$dupe_flag = 0;
					}
					else
						$dupe_flag = 0;
					$replace = 'NULL';
					if(isset($_POST['replace']) && $_POST['replace'] != 0) {
						$replace = mysql_real_escape_string($_POST['replace']);
						mysql_query("UPDATE `tracks` SET `need_reupload`=0 WHERE `id`=$replace;");
					}
					
					mysql_query("INSERT INTO `pending` (artist, track, album, path, origname, comment, submitter, dupe_flag, replacement) VALUES ('$artist', '$title', '$album', '$path', '$oldname', '$comment', '$REMOTE_ADDR', $dupe_flag, $replace);");
					
					if($time == 0) { // no previous entry
						mysql_query("INSERT INTO `uploadtime` (ip, time) VALUES ('$REMOTE_ADDR', NOW());");
						//echo mysql_error();
					}
					else { // had a previous entry
						mysql_query("UPDATE `uploadtime` SET `time`=NOW() WHERE `ip`='$REMOTE_ADDR';");
						//echo mysql_error();
					}
					
				}
				else {
					$site_text = "Error: The uploaded file is not an MP3 file (". implode($result) .").";
					$new_filename = '/radio/www/brainfarts/' . substr($temp_file, strripos($temp_file, '/') + 1);
					move_uploaded_file($temp_file, $new_filename);
					chmod($new_filename, 0664);
				}
				
			}
		}
		
		mysql_close();
	}
	/*
		$submit_site = <<<SUBMIT
<html>
	<head>
		<title>r/a/dio</title>
		<meta http-equiv="refresh" content="5;url=/submit/">
		<link rel="shortcut icon" href="/favicon.ico" />
	</head>
	<body>
		<center><h2>$site_text</h2><center><br/>
		<center><h3>You will be redirected shortly.</h3></center>
	</body>
</html>
SUBMIT;

	echo $submit_site;
	
	return;*/
}
if (isset($_GET['upload'])) {
	$submit_site = <<<SUBMIT
<html>
	<head>
		<title>R/a/dio</title>
		<meta http-equiv="refresh" content="5;url=/submit/">
		<link rel="shortcut icon" href="/favicon.ico" />
	</head>
	<body>
		<center><h2>$site_text</h2></center><br/>
		<center><h3>You will be redirected shortly.</h3></center>
	</body>
</html>
SUBMIT;

	echo $submit_site;

	return;
}
mysql_connect($dbip, $dbuser, $dbpass);

@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");


$display = " success";
$disable = "";
$disable2 = "";
$cannotupload = "";

$lutime = mysql_query("SELECT `time` FROM `uploadtime` WHERE `ip`='$REMOTE_ADDR';");
if(mysql_num_rows($lutime) > 0) {
	$time = strtotime(mysql_result($lutime, 0, "time"));
}
else {
	$time = 0;
}
$now = time();

if($now - $time < $upload_delay && $_SESSION['privileges'] == 0) {
	$remain = (int)($upload_delay / 60) - floor(($now - $time) / 60);
	
	$disable = 'disabled="disabled"';
	$disable2 = "disabled";
	$display = " danger";
	$cannotupload = "You need to wait another $remain minutes before uploading again.";	
}
$upload_message = $cannotupload;
if ($cannotupload == "") {
	$upload_message = "You are able to submit a track";
}

$latest_a = mysql_query("SELECT * FROM `postpending` WHERE `accepted`=1 OR `accepted`=2 ORDER BY `time` DESC LIMIT 70;");
$accepted_table = array();
$count = mysql_num_rows($latest_a);
$i = 0;
while($i < $count) {
	$meta = htmlspecialchars(mysql_result($latest_a, $i, "meta"));	
	$style = "";
	if(mysql_result($latest_a, $i, "good_upload") === "1") {
		$style = "class=\"good-song\"";
	}
	if(mysql_result($latest_a, $i, "accepted") === "2") {
		$style = "class=\"replace-song\"";
	}
	$row = <<<ROW
<tr>
	<td $style>$meta</td>
</tr>
ROW;
	$accepted_table[] = $row;
	$i++;
}
$accepted_table = implode("\n", $accepted_table);


$latest_d = mysql_query("SELECT * FROM `postpending` WHERE `accepted`=0 AND `reason`!='' ORDER BY `time` DESC LIMIT 50;");
$declined_table = array();
$count = mysql_num_rows($latest_d);
$i = 0;
while($i < $count) {
	$meta = htmlspecialchars(mysql_result($latest_d, $i, "meta"));
	$reason = htmlspecialchars(mysql_result($latest_d, $i, "reason"));
	$row = "";
	if($reason !== "") {
		$row = <<<ROW
<tr>
	<td>$meta</td>
	<td>$reason</td>
</tr>
ROW;
	}
	else {
		$row = <<<ROW
<tr>
	<td colspan="2">$meta</td>
</tr>
ROW;
	}
	$declined_table[] = $row;
	$i++;
}
$declined_table = implode("\n", $declined_table);

$replace = mysql_query("SELECT * FROM `tracks` WHERE `need_reupload`=1 ORDER BY lastplayed ASC LIMIT 6;"); // the order by gives some variety on the list
$count = mysql_num_rows($replace);
$i = 0;
$replace_songs = array();
while($i < $count) {
	$id = mysql_result($replace, $i, "id");
	$artist = htmlspecialchars(mysql_result($replace, $i, "artist"));
	$track = htmlspecialchars(mysql_result($replace, $i, "track"));
	$meta = $artist . " - " . $track;
	if($artist === "")
		$meta = $track;
	
	$replace_songs[] = <<<REPLACE
		<li><label><input type="radio" name="replace" value="$id" $disable />$meta</label></li>
REPLACE;
	
	$i++;
}
$replace_songs = implode("\n", $replace_songs);


mysql_close();

$site = <<<SITESTR
		<div class="alert-message$display"><p>$upload_message</p></div>
		<div id="submit-form-main" class="well">
			$submitmessage
			<hr>
			<form name="songsubmit" enctype="multipart/form-data" action="/submit/submit.php?upload=1" method="POST">
				<div class="row">
					<div class="span8">
						<div class="input-div input-e">
							<input type="hidden" name="MAX_FILE_SIZE" value="15728640" />
							<label for="file">File</label>
							<input type="file" class="xlarge input-fix file-input $disable2" name="submitted" $disable />
						</div>
						<div class="input-div input-e">
							<label for="comment">Comment</label>
							<input type="text" class="xlarge input-fix $disable2" name="comment" size="35" maxlength="100" placeholder="Max 100 chars" $disable />
						</div>
						<div class="input-div input-e hidden">
							<label for="daypass">Daypass</label>
							<input type="text" class="xlarge input-fix $disable2" name="daypass" size="35" maxlength="100" placeholder="What's the secret word?" $disable />
						</div>
						<div class="input-e">
							<input type="submit" class="btn primary submit-btn $disable2" value="Upload" $disable />
						</div>
					</div>
					<div class="span6">
						<h3 class="replace-song-header input-e">Specify song replacement</h3>
						<ul class="replace-song input-e">
							<li><label><input type="radio" name="replace" value="0" checked $disable />No song replacement</label></li>
							$replace_songs
						</ul>
					</div>
				</div>
			</form>
			<div id="submit_uploading" style="display: none;">
				<div style="text-align: center;">
					<img src="/res/img/ed_spin.gif">
				</div>
				<div style="text-align: center;">
					Uploading<br /><br />You can navigate the other pages while uploading.
				</div>
			</div>
			<hr>
			<script>
$('label').first().click(function() {
	$('.hidden').css('display','block');
	$('input').removeAttr('disabled');
});
			</script>
			<div id="submit-tables">
				<div class="row">
					<div class="span7">
						<table class="submit-table" id="accepted-table">
							<tr>
								<th class="mainhead">Latest accepted songs</th>
							</tr>
							<tr>
								<th>Song</th>
							</tr>
							$accepted_table
						</table>
					</div>
					<div class="span8" style="float:right;">
						<table class="submit-table" id="declined-table">
							<tr>
								<th colspan="2" class="mainhead">Latest declined songs</th>
							</tr>
							<tr>
								<th>Song</th>
								<th width="120px">Reason</th>
							</tr>
							$declined_table
						</table>
					</div>
				</div>
			</div>
			
		</div>
		<div id="submit_info" class="fade modal" style="display: none;" data-backdrop="true" data-keyboard="true">
			<div class="modal-header">
				<a class="close" href="#">×</a>
				<h3>Submit</h3>
			</div>
			<div class="modal-body">
			
			</div>
		</div>
SITESTR;

echo $site;

?>
