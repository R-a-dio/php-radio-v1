<?php
	include_once("../res/common.php");

	mysql_connect($dbip, $dbuser, $dbpass);

	@mysql_select_db($dbname);

	mysql_query("SET NAMES 'utf8';");

	$queue = mysql_query("SELECT meta AS track, time AS timestr, type FROM `queue` ORDER BY time asc;");
	$num = mysql_num_rows($queue);
	$i = 0;

	$tablerows = "";
	if ($num > 0) {
		while($i < $num) {
			$timestr = date("H:i", strtotime(mysql_result($queue, $i, "timestr")));
			$song = htmlspecialchars(mysql_result($queue, $i, "track"));
			if(mysql_result($queue, $i, "type") == 1)
				$song = '<b>' . $song . '</b>';
			$queuerow = "<tr><th>$timestr</th><td>$song</td></tr>";
			
			$tablerows = $tablerows . $queuerow;
			
			$i = $i + 1;
		}
	}
	$display = "";
	$display_error = ' style="display: none;"';
	if ($tablerows == "") {
		$display = ' style="display: none;"';
		$display_error = ' style="display: visible;"';
	}
	
	echo <<<OUTPUT
			<div class="alert-message info"$display_error>
				<p>There is currently no queue available.</p>
			</div>
			<div class="row"$display>
						<div class="span16">
							<table class="zebra-striped">
								<thead>
									<tr>
										<th>Time</th>
										<th class="head">Track</th>
									</tr>
								</thead>
								<tbody>
									$tablerows
								</tbody>
							</table>
						</div>
					</div>
OUTPUT;
	mysql_close();
?>
