<?php
	$start = microtime(true);
	mb_internal_encoding('UTF-8');

	if (!isset($_GET["hash"])) {
        	header("Location: /x_admin");
        	die();
	}
	if (strlen($_GET["hash"]) != 27) {
                die(strlen($_GET["hash"]));
	}
        function decompress_hex($comp_str) {
                $b64_str = str_replace(array("-", "_"), array("+", "/"), $comp_str);
                $bin_str = base64_decode($b64_str);
                return bin2hex($bin_str);
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


	$g->setOption(array('encoding' => 'UTF-8'));
	$tagwriter->tagformats 		= array('id3v2.3');
	$tagwriter->overwrite_tags 	= true;
	$tagwriter->tag_encoding   	= 'UTF-8';
	$tagwriter->remove_other_tags 	= true;

	$query = $db->prepare("SELECT artist, track, album, path FROM tracks WHERE hash = ?");
	$query->bind_param("s", $_GET["hash"]);
	$query->execute();
	$query->bind_result($artist, $track, $album, $path);
	if (is_null($query->fetch())) {
		header("Location: /");
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
	$now = microtime(true);
	$time = $now - $start;
	if ($tagwriter->WriteTags()) {
		header("Content-Type: text/html; charset=utf-8");
		$time_ = microtime(true) - $now;
		echo "Errors: ";
		print_r($tagwriter->errors);
		echo "<br>Filename: $tmp <br>";
		echo "Artist: $artist <br>";
		echo "Title: $track <br>";
		echo "Album: $album <br>";
		echo "Path: $path <br>";
		echo "Copied: $copied <br>";
		echo "Time to fetch: $time <br>";
		echo "Time to write: $time_ <br>";
		die("Tag Writing Successful.");
	}
	//header("Content-Disposition: attachment; filename=$path");

?>
