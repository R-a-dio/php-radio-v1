<?php
	mb_internal_encoding('UTF-8');

	if (!isset($_GET["hash"])) {
        	header("Location: /e404.php");
        	die();
	}
	if (strlen($_GET["hash"]) != 27) { // compressed SHA-1 hash from 40 -> 27 with base64 - NULL
		header("Location: /e404.php");
                die();
	}

	$location = "/radio/www/music/";
	require_once(__DIR__ . "/../res/common.php");

	//removed permission requirement to allow linking; i doubt people will figure out the hashes
	/*if ($_SESSION["login"] != 1) {
                header("Location: /x_admin");
                die();
	}*/



	require_once(__DIR__ . "/../id3/getid3.php");
	require_once(__DIR__ . "/../id3/write.php");

	$db = new mysqli($dbip, $dbuser, $dbpass, $dbname);
	$db->set_charset("utf8");
	$g = new getID3;
	$tagwriter = new getid3_writetags;
	function decompress_hex($comp_str) {
        	$b64_str = str_replace(array("-", "_"), array("+", "/"), $comp_str);
        	$bin_str = base64_decode($b64_str);
        	return bin2hex($bin_str);
	}
	$hash = decompress_hex($_GET["hash"]);

	$g->setOption(array('encoding' => 'UTF-8'));
	$tagwriter->tagformats 		= array('id3v2.3');
	$tagwriter->overwrite_tags 	= true;
	$tagwriter->tag_encoding   	= 'UTF-8';
	$tagwriter->remove_other_tags 	= true;

	$query = $db->prepare("SELECT artist, track, album, path FROM tracks WHERE hash = ?");
	$query->bind_param("s", $hash);
	$query->execute();
	$query->bind_result($artist, $track, $album, $path);
        if (is_null($query->fetch())) {
                header("Location: /e404.php");
		die();
        }

	$query->close();

	$real = $location . $path;
	$tmp  = "/radio/www/music/tagged/" . $path;
	$copied = copy($real, $tmp) ? "True" : "False";

	$tagwriter->filename = $tmp;

	$tags = array(
        	'title'   => array($track),
        	'artist'  => array($artist),
		'album'   => array($album),
        	'comment' => array('R-a-dio Database File')
	);

	$tagwriter->tag_data = $tags;
	if (!$tagwriter->WriteTags()) {
		print_r($tagwriter->errors);
		echo "Filename: $tmp <br>";
		echo "Artist: $artist <br>";
		echo "Title: $track <br>";
		echo "Album: $album <br>";
		echo "Path: $path <br>";
		echo "Copied: $copied <br>";
		die("Tag Writing failed.");
	}
	//header("Content-Disposition: attachment; filename=$path");
	$new_filename = str_replace(str_split('\\/:*?"<>|'), "", "$artist - $track.mp3");
	$new_path = "/radio/www/music/tagged/" . $new_filename;
	rename($tmp, $new_path);
	$url_safe = urlencode($new_filename);
	$url_safe = str_replace("+", "%20", $url_safe);
	$url_safe = "/res/music/tagged/".$url_safe;
	if(isset($_SERVER['HTTP_USER_AGENT'])){
		$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
	}
	else {
		$ua = '';
	}
	if(stripos($ua,'android') !== false)
	{
		echo "<a href=\"$url_safe\" style=\"font-size:2em\">Hello <del>Kitty</del> Android</a>";
	}
	else
	{
		header("Location: $url_safe");
	}
?>
