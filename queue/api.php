<?php
	include_once("../res/common.php");
	header('Content-Type: text/javascript; charset=utf-8');
	header('Access-Control-Max-Age: 3628800');
	header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

	mysql_connect($dbip, $dbuser, $dbpass);

	@mysql_select_db($dbname);

	mysql_query("SET NAMES 'utf8';");

	$queue = mysql_query("SELECT meta AS track, UNIX_TIMESTAMP(time) AS timestr, type FROM `queue` ORDER BY time;");
	$num = mysql_num_rows($queue);
	$i = 0;

	$result = array();

	while($i < $num) {
		$timestr = mysql_result($queue, $i, "timestr");
		$song = mysql_result($queue, $i, "track");
		$type = mysql_result($queue, $i, "type");
		$result[] = array($timestr, $song, $type);

		$i = $i + 1;
	}

	mysql_close();
	if (isset($_GET['mode']) && $_GET['mode'] == 'plaintext')
	{
		for ($a=0; $a<count($result); $a++)
		{
			echo $result[$a][1]."\n";
		}
		die();
	}
        if (isset($_GET['callback'])) {
                $callback = $_GET['callback'];
		echo $callback . '(' . htmlentities(json_encode($result), ENT_NOQUOTES, 'UTF-8') . ');';
	} else {
		echo json_encode($result);
	}
?>
