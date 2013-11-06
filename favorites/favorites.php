<?php
	include_once('../res/common.php');
	//include('../templates/header.php');
	$table = "";
	mysql_connect($dbip, $dbuser, $dbpass);
	@mysql_select_db($dbname);
	mysql_query("SET NAMES 'utf8';");
	
	if(isset($_POST['delfave'])) { // unfave
		if(@$_SESSION['nick'] !== "") {
			$faveid = mysql_real_escape_string($_POST['delfave']);
			mysql_query("DELETE FROM `efave` WHERE `id`='$faveid';");

		}
		mysql_close();
		header("Location: /favorites/");
		exit();
	}
	else if(isset($_POST['login'])) { // auth
		$nickname = mysql_real_escape_string(urldecode($_POST['nickname']));
		$authcode = mysql_real_escape_string($_POST['authcode']);
		$auth = mysql_query("SELECT * FROM `enick` WHERE nick='$nickname' AND authcode='$authcode';");
		if(mysql_num_rows($auth) > 0) {
			$_SESSION['nick'] = $nickname;
			echo ' ';
		}
		else {
			echo '<div class="alert-message danger">Invalid username or authcode.</div>';
		}
		mysql_close();
		exit();
	}
	else if(isset($_POST['logout'])) { // unauth
		@$_SESSION['nick'] = "";
		mysql_close();
		header("Location: /favorites/");
		exit();
	}
	
	
	$nick = mysql_real_escape_string(@$_GET['nick']);
	$fave = mysql_query("SELECT efave.id as faveid, esong.meta, tracks.id as trackid, tracks.requestcount as rcount, unix_timestamp(tracks.lastplayed) as lp, unix_timestamp(tracks.lastrequested) as lr FROM tracks RIGHT JOIN esong ON tracks.hash = esong.hash JOIN efave ON efave.isong = esong.id JOIN enick ON efave.inick = enick.id WHERE lower(enick.nick) = lower('$nick') ORDER BY esong.meta ASC;");
	
	$nick_print = htmlspecialchars(@$_GET['nick']);
	$count = mysql_num_rows($fave);
	$table_head = <<<TABLE
		<thead>
			<tr>
				<th class="f-met">Artist - Title</th>
				<th class="f-rem">Unfave</th>
				<th class="f-req">Request</th>
			</tr>
		</thead>
TABLE;
	
	$afk = mysql_query("SELECT `isafkstream` FROM `streamstatus`;");
	if(mysql_num_rows($afk) > 0) {
		if(mysql_result($afk, 0, "isafkstream") == 1) {
			$canrequest_afk = TRUE;
		}
		else {
			$canrequest_afk = FALSE;
		}
	}
	else {
		$canrequest_afk = FALSE;
	}
	
	$ip_lr = mysql_query("SELECT * FROM `requesttime` WHERE `ip`='$REMOTE_ADDR' LIMIT 1;");
	if(mysql_num_rows($ip_lr) >= 1) {
		$iptime = strtotime(mysql_result($ip_lr, 0, "time"));
	}
	else {
		$iptime = 0;
	}
	
	$now = time();
	
	if($now - $iptime > $ip_req_delay) {
		$canrequest_ip = TRUE;
	}
	else {
		$canrequest_ip = FALSE;
	}
	
	if (@$_SESSION['nick'] !== "") {
		$sn = $_SESSION['nick'];
		$auth_info = <<<AUTH
		<h4>Logged in as $sn.</h4>
		<form style="margin-bottom:8px;" id="logout-fave" method="POST" action="">
			<input name="logout" type="submit" value="Log out">
		</form>
AUTH;
	}
	else {
		$auth_info = <<<AUTH
		<form target="submit_trick" id="login-fave" method="POST" action="">
			<h4>Enter authcode* to change favourites:
				<input type="text" name="nickname" placeholder="Nickname">
				<input type="text" name="authcode" placeholder="Enter Authcode">
				<input name="login" id="nick-auth" type="submit" value="Submit">
			</h4>
		</form>
		<iframe name="submit_trick" id="submit_trick" style="display:none"></iframe>
		<p>Authcodes are given out by Hanyuu-sama on IRC. Type the following in your IRC client to receive one for your nick. Logging in lets you unfave from this page.</p>
		<pre>/msg Hanyuu-sama SEND CODE</pre>
AUTH;
	}
	//$auth_info .= "<p>To unfave, check the boxes of favorites you wish to remove below, then press submit at the bottom of the page</p>";
	for($i = 0; $i < $count; $i++) {
		$meta = mysql_result($fave, $i, "meta");
		$trackid = mysql_result($fave, $i, "trackid");
		$faveid = mysql_result($fave, $i, "faveid");
		
		$requestbtn = '<button disabled="disabled" class="btn warning disabled fave-request" value="0">Request</button>';
		$removebtn = '<button disabled="disabled" class="btn danger disabled fave-delete" value="0">Unfave</button>';
		
		if(@$_GET['nick'] !== "" && strtolower($_SESSION['nick']) == strtolower(@$_GET['nick'])) {
			$removebtn = <<<BUTTON
<button class="btn danger fave-delete" name="delfave" value="$faveid">Unfave</button>
BUTTON;
		}
		if (!is_null($trackid)) {
			$rcount = mysql_result($fave, $i, "rcount");			
			$lptime = mysql_result($fave, $i, "lr");
			
			if(!is_numeric($lptime))
				$lptime = 0;
			
			if($now - $lptime > song_delay($rcount)) {
				$canrequest_song = TRUE;
			}
			else {
				$canrequest_song = FALSE;
			}
			
			$lptime = mysql_result($fave, $i, "lp");
			
			if(!is_numeric($lptime))
				$lptime = 0;

			if($now - $lptime > song_delay($rcount)) {
				$canrequest_song = $canrequest_song && TRUE;
			}
			else {
				$canrequest_song = FALSE;
			}
			
			if($canrequest_afk && $canrequest_ip && $canrequest_song) {
				$requestbtn = <<<BUTTON
<button class="btn info fave-request" name="songid" value="$trackid">Request</button>
BUTTON;
			}
		}
		$t = <<<TEXT
		<tr>
			<td>
				$meta
			</td>
			<td>
				<form name="remove" method="POST" action="">
				$removebtn
				</form>
			</td>
			<td>
				<form name="request" method="POST" action="/request/index.py">
				$requestbtn
				</form>
			</td>
		</tr>
TEXT;
		$table .= $t;
	}
	
	
/*	if ($_SESSION['nick'] !== "") {
		$sn = $_SESSION['nick'];
		$auth_info = <<<AUTH
		<h4>Logged in as $sn</h4>
AUTH;
	}
	else {
		$auth_info = <<<AUTH
		<form name="auth" id="login-fave" method="POST" action="/favorites/">
			<input type="hidden" name="auth" value="login" />
			<h4>Enter authcode* to change favourites:
				<input type="text" name="nickname" placeholder="Nickname">
				<input type="text" name="authcode" placeholder="Enter Authcode">
				<input type="submit" value="Submit">
			</h4>
		</form>
		<p>Authcodes are given out by Hanyuu-sama on IRC. Type the following to receive one for your nick. Logging in lets you remove favourites and request from this page.</p>
		<pre>/msg Hanyuu-sama SEND CODE &lt;NOTE: THIS IS NOT WORKING&gt;</pre>
AUTH;
		for($i = 0; $i < $count; $i++) {
			$meta = mysql_result($fave, $i, "meta");
			$t = <<<TEXT
				<tr>
					<td>$meta</td>
					<td>
						<button disabled="disabled" class="btn danger disabled fave-delete" value="placeholder">Remove</button>
					</td>
					<td>
						<button disabled="disabled" class="btn info disabled fave-request" value="placeholder">Request</button>
					</td>
				</tr>
TEXT;
			$table .= $t;
		}
	}
	*/
	
	//echo "\n" . '<div class="container page" id="page-p-home">';
	if (@$_GET['nick'] != '') {
		$favcount = "<p>{$nick_print} has <strong>{$count}</strong> favorites.</p>";
	}
	else {
		$favcount = '';
	}
	echo <<<TOP
		<!-- <div class="alert-message danger">This page totally doesn't work yet. Please don't use it.</div> -->
		<form id="nicksearch" name="nick" method="GET" action="">
			<h3>Favourites for: <input type="text" name="nick" value="{$nick_print}"> <input id="nick-search" type="submit" value="Search"></h3>
		</form>
		{$auth_info}
		{$favcount}
		<table class="zebra-striped bordered-table condensed-table">
			{$table_head}
			<tbody>
				{$table}
			</tbody>
		</table>
	<!-- </div> -->
	<div id="faves_request_info" class="fade modal" style="display: none;" data-backdrop="true" data-keyboard="true">
		<div class="modal-header">
			<a class="close" href="#">×</a>
			<h3>Request</h3>
		</div>
		<div class="modal-body">
		
		</div>
	</div>
TOP;
?>
