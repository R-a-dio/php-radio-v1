<?php
$id = $_GET['id'];
$id = preg_replace('/[^0-9]/','',$id);
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Wed, 09 Sep 2009 09:09:09 GMT');
if (!isset($_GET) || !isset($_GET['id']))
{
	die('sorry but i need a track ID :(');
}
include_once("common.php");
mysql_connect($dbip, $dbuser, $dbpass);
@mysql_select_db($dbname);

$ass = mysql_query("SELECT * FROM tracks WHERE ID=".$id);
$res = mysql_num_rows($ass);
if ($res != 1) die('ERROR: number of results != 1 ('.$res.')');
$ass = mysql_fetch_assoc($ass);
$ass = $ass['path'];
$dir = '/radio/www/music/';

$slash = strripos($ass,'/');
if ($slash !== false)
{
	// woodoo magic to remove absolute paths
	$ass = substr($ass, $slash + 1);
}
if (!file_exists($dir.$ass))
{
	// /radio/www/music/7261_rfswl42myv7q2z2.mp3
	// 2818_0mh87wuu1q1j9wo.mp3
	// cvy60zgbfgjrqsl.mp3
	if (strlen($ass) != 19)
	{
		$ass = substr(stripos($ass,'_') +1);
	}
	else
	{
		$ass = $id .'_'. $ass;
	}
}
if (!file_exists($dir.$ass))
{
	die('ERROR: database is fubar');
}
//die($dir.$ass);
header('Content-Type: audio/mpeg');
header('Content-Disposition: attachment; filename=radio-song-'.$id.'.mp3');
$fh = fopen($dir.$ass,'rb');
while (!feof($fh))
{
	echo fread($fh, 8192);
}
fclose($fh);
die();
