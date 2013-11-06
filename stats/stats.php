<?php

error_reporting(E_ALL);

require_once($_SERVER["DOCUMENT_ROOT"] . "/res/common.php");


mysql_connect($dbip, $dbuser, $dbpass);

@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");

$q = mysql_query("SELECT COUNT(*) as c FROM `tracks`;");

$stat_count = mysql_result($q, 0, "c");

$q = mysql_query("SELECT * FROM `tracks` WHERE UNIX_TIMESTAMP(lastplayed) = 0;");

$stat_notplayed = mysql_num_rows($q);
$np_a = "have";
if($stat_notplayed == 0)
	$stat_notplayed = "none";
if($stat_notplayed == 1)
	$np_a = "has";

$q = mysql_query("SELECT COUNT(*) AS c FROM `pending`;");

$pend_count = mysql_result($q, 0, "c");
$pc_a = "are";
$pc_b = "songs";
if($pend_count == 0)
	$pend_count = "no";
if($pend_count == 1) {
	$pc_a = "is";
	$pc_b = "song";
}

$q = mysql_query("SELECT COUNT(*) as c FROM `esong`;");

$stat_played = mysql_result($q, 0, "c");
$p_a = "songs";
if($stat_played == 1) {
	$p_a = "song";
}

$q = mysql_query("SELECT COUNT(*) as c FROM `queue`;");

$stat_queuelen = mysql_result($q, 0, "c");
$ql_a = "songs";
if($stat_queuelen == 0)
	$stat_queuelen = "no";
if($stat_queuelen == 1)
	$ql_a = "song";


$q = mysql_query("SELECT * FROM `relays` ORDER BY length(relay_name) asc, relay_name asc;");
$count = mysql_num_rows($q);

$i = 0;
$relays = array();
while($i < $count) {
	$status = mysql_result($q, $i, "active") == "1" ? 'server-on' : 'server-off';
	$status_text = $status == 'server-on' ? 'Online' : 'Offline';
	$disabled = mysql_result($q, $i, "disabled");
	if ($disabled == 1) {
		$status = 'server-off';
		$status_text = 'Disabled';
	}
	$server = mysql_result($q, $i, "relay_name") . ".r-a-d.io";
	$link = 'http://'.$server.':'.mysql_result($q, $i, "port"). mysql_result($q, $i, "mount");
	$listeners = mysql_result($q, $i, "listeners");
	$format = strtoupper(mysql_result($q, $i, "format"));
	$quality = mysql_result($q, $i, "bitrate") <= 128 ? 'LQ' : 'HQ';
	$country = mysql_result($q, $i, "country");
	$countryimg = "/res/img/flag/$country.png";
	$ownername = mysql_result($q, $i, "relay_owner");
	$relays[] = <<<RELAY
		<tr>
			<td><span class="server-status $status" title="$status_text"></span> <a href="$link" target="_blank">$server</a> <img class="country-image" align="right" alt="$country" title="Hosted by: $ownername" src="$countryimg" /></td>
			<td>$format</td>
			<td>$quality</td>
			<td>$listeners</td>
		</tr>
RELAY;
	
	$i = $i + 1;
}

$relays = implode("\n", $relays);

$count++; // for good measure
//$count = 900; # that's not a good measure
// it's not meant to show everyone, it's meant to line up nicely.


$q = mysql_query("SELECT djname, ROUND(AVG(listeners)) AS list FROM (SELECT * FROM `listenlog` WHERE UNIX_TIMESTAMP() - UNIX_TIMESTAMP(time) < 3600*24*30) AS filtered JOIN djs ON filtered.dj=djs.id WHERE visible=1 GROUP BY djs.id ORDER BY list DESC LIMIT $count;");
$count = mysql_num_rows($q);

$i = 0;
$dj_listeners = array();
while($i < $count) {
	$dj = htmlspecialchars(mysql_result($q, $i, "djname"));
	$listeners = mysql_result($q, $i, "list");
	
	$dj_listeners[] = <<<DJLIST
		<tr>
			<td><b>$dj</b></td>
			<td width="80">$listeners</td>
		</tr>
DJLIST;
	$i = $i + 1;
}

$dj_listeners = implode("\n", $dj_listeners);

$site = <<<SITE
<div class="row">
	<div class="span16 stats-header">
		<p>Statistics</p>
	</div>
</div>
<div class="row">
	<div class="span10 offset3">
		<p>There are <b>$stat_count</b> songs in the music database, <b>$stat_notplayed</b> of which $np_a never been played. There $pc_a <b>$pend_count</b> $pc_b currently waiting to be accepted into the database.</p>
		<p>The database contains play data from <b>$stat_played</b> uniquely tagged $p_a. The current queue contains <b>$stat_queuelen</b> $ql_a.</p>
	</div>
</div>
<div class="row seperator" style="height:1px">
	<div class="seperator span16" style="height:1px;margin-bottom:0px"></div>
</div>
<div class="row">
	<div class="span8">
		<table class="stats-table">
			<tr>
				<th colspan="4">List of relays</th>
			</tr>
			<tr>
				<th class="subheader">Server</th>
				<th class="subheader">Format</th>
				<th class="subheader">Quality</th>
				<th class="subheader">Listeners</th>
			</tr>
$relays
		</table>
	</div>
	<div class="span8">
		<table class="stats-table">
			<tr>
				<th colspan="2">Average listeners per DJ over the past month</th>
			</tr>
			<tr>
				<th class="subheader">DJ</th>
				<th class="subheader">Listeners</th>
			</tr>
$dj_listeners
		</table>
	</div>
</div>
<div class="row seperator">
	<div class="seperator span16"></div>
</div>
<div class="row graph">
	<div class="span14 offset1">
		<!--<div id="graph" style="width: 100%; height: 330px;"></div>-->
		<p>Listener count over the past 24 hours (updated every 5 minutes)</p>
		<img src="/res/img/listeners.svg" width="820">
	</div>
</div>



<div class="row graph">
	<div class="span14 offset1">
		<p>Player statistics (updated hourly)</p>
		<img src="/res/img/players.svg" width="820">
	</div>
</div>
<div class="row graph">
        <div class="span14 offset1">
                <p>Listener location</p>
                <a title="Click for 'Other' percentage" href="/res/img/geo2.svg" target="_blank"><img src="/res/img/geo.svg" width="820"></a>
        </div>
</div>
<div class="row graph">
	<div class="span14 offset1">
		<p>Accepted and declined uploads over the past 20 weeks</p>
		<img src="/res/img/uploads.svg" width="820">
	</div>
</div>
SITE;

echo $site;

		
// remove this when you figure out how to get it to work.
// i also commented out the #graph above.
mysql_close();
return;


$pdo = new PDO("mysql:host=$dbip;dbname=$dbname", $dbuser, $dbpass);
		$sql = <<<SQL
SELECT
	listenlog.time,
	CAST(listenlog.listeners AS UNSIGNED),
	djs.djname
FROM
	listenlog
JOIN
	djs
ON
	listenlog.dj = djs.id
ORDER BY
	listenlog.id desc
LIMIT 288
SQL;
		$stats = array();
                // we need a plain array here
		$query = $pdo->query($sql);
		while($row = $query->fetch(PDO::FETCH_NUM)) {
			$stats[] = $row;
		}
                // post-processing is mandatory because mysql is fucking stupid for JS

                foreach ($stats as &$stat) {
                        $stat[0] = DateTime::createFromFormat('Y-m-d H:i:s', $stat[0])->format('Y,m,d,H,i,s');
                        $stat[1] = (int) $stat[1];
                        $stat[3] = ($stat[2] == "Hanyuu-sama") ? FALSE : TRUE;

                        $stat[2] = "<div style=\"width: 120px; padding: 6px;\"><p><b>DJ:</b> {$stat[2]}</p><p><b>Listeners:</b> {$stat[1]}</p></div>";
                }

$graph = <<<'GRAPH'

<script>

	// Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {
GRAPH;
$graph .= "\n\tgraph = " . json_encode($stats) . ";\n";
$graph .= <<<'GRAPH'

      	var formatter = new google.visualization.DateFormat({format: "medium"});

        // Create the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('datetime', 'Time');
        data.addColumn('number', 'Listeners');
        data.addColumn({type: 'string', role: 'tooltip', p: { 'html' : true}});
        data.addColumn({type: 'boolean', role: 'scope'});
        
        for(var i = 0; i < graph.length; i++) {
        	var d = graph[i][0].split(',');
        	graph[i][0] = new Date(d[0], d[1], d[2], d[3], d[4], d[5]);
        	data.addRow(graph[i]);
        }

        // Set chart options
        var options = {'title':'R/a/dio Listeners (hover for DJ)',
                       'tooltip' : { isHtml: true },
                       'legend': { position: 'none' }
                   };

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.LineChart(document.getElementById('graph'));
        chart.draw(data, options);
      }
</script>
GRAPH;
echo $graph;

/////////////////////////////////////////////////////////////////////////////////
mysql_close();

?>
