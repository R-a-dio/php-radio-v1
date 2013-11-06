<?php
	include_once('../res/common.php');
	include('../templates/header.php');
	mysql_connect($dbip, $dbuser, $dbpass);
	@mysql_select_db($dbname);
	//if (isset($_POST['auth'])) {}
	mysql_query("SET NAMES 'utf8';");
	$nick = mysql_real_escape_string(@$_GET['nick']);
	//$fave = mysql_query("SELECT esong.meta, tracks.id FROM tracks RIGHT JOIN esong ON tracks.hash = esong.hash JOIN efave ON efave.isong = esong.id JOIN enick ON efave.inick = enick.id WHERE enick.nick = lower('$nick') ORDER BY esong.meta DESC;");
	//fuck your fancy joins, I'm doing this the orderly and straightforward way
	$q = <<<Q
select esong.meta, tracks.id, efave.id as faveid
from tracks, esong, efave, enick
where tracks.hash = esong.hash
and efave.isong = esong.id
and efave.inick = enick.id
and enick.nick = lower('$nick')
order by esong.meta desc;
Q;
	$fave = mysql_query($q);
	$nick_print = htmlspecialchars(@$_GET['nick']);
	$count = mysql_num_rows($fave);
	$table_head = <<<TABLE
		<thead>
			<tr>
				<th class="f-idx">#</th>
				<th class="f-met">Artist - Title</th>
				<th class="f-rem">Remove Favorite</th>
				<th class="f-req">Request</th>
			</tr>
		</thead>
TABLE;
	if (isset($_SESSION['nick'])) {
		$auth_info = "<h4>Logged in as {$_SESSION['nick']}</h4>";
		if ($_SESSION['nick'] == @$_GET['nick']) {
			$auth_info .= "<p>To unfave, check the boxes of favorites you wish to remove below, then press submit at the bottom of the page</p>";
			for($i = 0; $i < $count; $i++) {
                $result = mysql_result($fave, $i, "meta");
				$trackid = mysql_result($fave, $i, "id");
				if ($trackid == null) {
					$requestbtn = '<input disabled="disabled" class="btn danger disabled" value="0">Request</button>';
				} else {
					$requestbtn = "<button class=\"btn info\" name=\"request\" value=\"$trackid\">Request</button>";
				}
                $table .= "<tr><form method=\"POST\" id=\"fave\" action=\"fave-config.php\"><input type=\"hidden\" name=\"faveid\" value=\"\"<td>$result</td><td><button class=\"btn primary\" type=\"submit\" name=\"delfave\"</td><td>$requestbtn</td></form></tr>";
            }
		}
	} else {
		$auth_info = <<<AUTH
		<form id="login-fave" method="POST" action="fave-auth.php">
		<input type="hidden" name="auth" value="login"
		<h4>Enter authcode* to change favourites: <input type="text" name="nickname" placeholder="Nickname"> 
							  <input type="text" name="authcode" placeholder="Enter Authcode"> <input type="submit" value="Submit"></h4></form>
		<p>Authcodes are given out by Hanyuu-sama on IRC. Type the following to receive one for your nick. Logging in lets you remove favourites and request from this page.</p><pre>/msg Hanyuu-sama SEND CODE &lt;NOTE: THIS IS NOT WORKING&gt;</pre>
		</form>
AUTH;
		for($i = 0; $i < $count; $i++) {
			$ret = mysql_fetch_assoc($fave);
			$meta = $ret['meta'];
			$id = $ret['faveid'];
            $table .= "<tr><td>$id</td><td>$meta</td><td><button disabled=\"disabled\" class=\"btn danger disabled\" value=\"placeholder\">Remove</button></td><td><button disabled=\"disabled\" class=\"btn info disabled\" value=\"placeholder\">Request</button></tr>";
        }
	}
	echo "\n" . '<div class="container page" id="page-p-home">';
	if ($_GET['nick'] != '') {
		$favcount = "<p>{$nick_print} has <strong>{$count}</strong> Favorites</p>";
	} else {
		$favcount = '';
	}
	echo <<<TOP
		<form id="nicksearch" name="nick" method="GET" action="favorites.php">
		<h3>Favourites for: <input type="text"  name="nick" value="{$nick_print}"> <input type="submit" value="Search"></h3>
		</form>
		{$auth_info}
		{$favcount}
		<table class="zebra-striped bordered-table condensed-table">
			{$table_head}
			<tbody>
				{$table}
			</tbody>
		</table>
		</form>
	</div>
TOP;
?>
