<?php
include_once("../res/common.php");

//pageView("djs", $_SERVER['QUERY_STRING'], gethostbyaddr($_SERVER['REMOTE_ADDR']), $_SESSION['user']);

mysql_connect($dbip, $dbuser, $dbpass);

@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");



$djs = mysql_query("SELECT * FROM `djs` WHERE `visible`='1' ORDER BY `priority` ASC;");
$num = mysql_num_rows($djs);
$i = 0;

$site_djs = '<ul class="media-grid">';

while($i < $num) {
	$djname = mysql_result($djs, $i, "djname");
	$djtext = mysql_result($djs, $i, "djtext");
	$djtext=htmlspecialchars($djtext);
	$djimage = mysql_result($djs, $i, "djimage");
	
	$site_dj = <<<SITEDJ
				<li><span rel="twipsy" class="djinfo" data-original-title="$djtext">
					<img class="thumbnail djimg" width="150" height="150" src="/res/img/dj/$djimage" />
					<span class="djname">$djname</span>
				</span></li>
SITEDJ;
	
	$site_djs = $site_djs . $site_dj;
	
	$i = $i + 1;
}

echo $site_djs . "</ul>";

?>


