<?php
include_once("res/common.php");
header('Content-Type: text/javascript; charset=utf-8');
header('Access-Control-Max-Age: 3628800');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

$callback = '';
if (isset($_GET['callback'])) {
	$callback = $_GET['callback'];
}

//$contents = file_get_contents("/home/www/r-a-dio.com/api_generated");

$memcache = memcache_connect('localhost', 11211);
$contents = memcache_get($memcache, 'main_page_api_gen');
if($contents === FALSE) {
	include_once('api_generate.php'); //this ought to run the script...
	$contents = memcache_get($memcache, 'main_page_api_gen');
}

if($contents === FALSE) { // still false? something is broken
	$contents = "{online:'0'}";
}



if ($callback == '') {
    echo $contents;
}
else {
    echo $callback . '(' . $contents . ');';
}


?>
