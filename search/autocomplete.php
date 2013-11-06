<?php
include_once("../res/common.php");

mysql_connect($dbip, $dbuser, $dbpass);

@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");

// echo '[]';
// exit();

if(isset($_GET['query']) && trim($_GET['query']) != "" && strlen(trim($_GET['query'])) > 1){
	$autoc = trim($_GET['query']);
}
else {
	echo '[]';
	exit();
}

$callback = @$_GET['callback'];

$terms = explode(" ", $autoc);
$query_terms = array();

for($i = 0; $i < count($terms); $i++) {
	$t = mysql_real_escape_string($terms[$i]);
	//$query_terms[] = "`search` LIKE '%$t%'";
	$query_terms[] = "(`track` LIKE '%$t%' OR `artist` LIKE '%$t%')";
}

$qts = implode(" AND ", $query_terms);

// $q = mysql_real_escape_string($autoc);

//$query = mysql_query("SELECT DISTINCT search FROM searchlog WHERE res_count!=0 AND ($qts) GROUP BY search ORDER BY time DESC, cumulative_prio DESC, res_count DESC LIMIT 5;");
$query = mysql_query("SELECT DISTINCT track FROM tracks WHERE `usable`=1 AND `need_reupload`=0 AND ($qts) ORDER BY priority DESC LIMIT 5;");

//echo "//" . "SELECT DISTINCT track FROM tracks WHERE `usable`=1 AND ($qts) ORDER BY priority DESC LIMIT 5;" . "\n";

$c = mysql_num_rows($query);
$norm = array();
for($i = 0; $i < $c; $i++) {
	//$norm[] = mysql_result($query, $i, "search");
	$norm[] = mysql_result($query, $i, "track");
}
if (isset($callback)) {
	echo $callback . '(' . htmlentities(json_encode($norm), ENT_NOQUOTES, 'UTF-8') . ');';
} else {
	echo json_encode($norm, JSON_PRETTY_PRINT);
}
mysql_close();
?>
