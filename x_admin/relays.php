<?php

include_once("../res/common.php");
include_once("adminc.php");

if($_SESSION['login'] == 0) {
        header("Location: /x_admin");
        die();
}
if($_SESSION['privileges'] < 1) {
        header("Location: /");
        die();
}

if($_SESSION['privileges'] < 4) {
	header("Location: /x_admin");
	die();
}

$db = new mysqli($dbip, $dbuser, $dbpass, $dbname);

$table_head = <<<TABLE
		<table class="edwardian-table compactix" style="align: center;">
		<tr>
			<th>DNS ID</th>
			<th>Owner</th>
			<th>IP</th>
			<th>Port</th>
			<th>Mount</th>
			<th>kbps</th>
			<th>Format</th>
			<th>Priority</th>
			<th>Limit</th>
			<th>Country</th>
			<th>Password</th>
			<th>Save</th>
			<th>Del</th>
		</tr>
TABLE;


$table = "";

if ($fetch = $db->query("select * from relays;")) {
	while ($relay = $fetch->fetch_assoc()) {
		$table .= <<<RELAY
		<tr>
			<form method="POST" action="">
				<input type="hidden" id="id" value='{$relay["id"]}'>
				<td><input type="text" class="input-small" name="dns" value='{$relay["relay_name"]}'></td>
				<td><input type="text" class="input-small" name="owner" value='{$relay["relay_owner"]}'></td>
				<td><input type="text" class="input-medium" name="ip" value='{$relay["base_name"]}'></td>
				<td><input type="text" class="input-small" name="port" value='{$relay["port"]}'></td>
				<td><input type="text" class="input-small" name="mount" value='{$relay["mount"]}'></td>
				<td><input type="text" class="input-small" name="bitrate" value='{$relay["bitrate"]}'></td>
				<td><input type="text" class="input-small" name="format" value='{$relay["format"]}'></td>
				<td><input type="text" class="input-small" name="priority" value='{$relay["priority"]}'></td>
				<td><input type="text" class="input-small" name="limit" value='{$relay["listener_limit"]}'></td>
				<td><input type="text" class="input-small" name="country" value='{$relay["country"]}'></td>
				<td><input type="password" class="input-medium" name="password"></td>
				<td><input type="submit" name="submit" value="Save"></td>
				<td><input type="submit" name="submit" value="Del"></td>
			</form>
		</tr>
RELAY;
	}
}
$db->close();


$content = $table_head . $table . "\n</table>";








$site = <<<SITE
<!DOCTYPE html>
<html>
        <head>
                <title>Edit Relays</title>
                <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
                <link rel="stylesheet" href="style.css" type="text/css" />
                <link rel="stylesheet" href="/css/bootstrap.min.css" type="text/css" />
		<style type="text/css">
		input, textarea, select, .uneditable-input { height: inherit; line-height: inherit; width: 100%;}
		</style>
        </head>
        <body>
                {$main_menu}
		<br>
		<br>
		<p>Edit these with care! "Country" is a 2-letter country code for where the server is located.</p>
		<p>TODO(Hiroto):</p>
			<ul>
				<li>Actually use _POST</li>
				<li>Dropdown boxes</li>
				<li>base64 encoding on the password when entered</li>
			</ul>
		<br>
		{$content}
	</body>
</html>
SITE;
echo $site;
?>
