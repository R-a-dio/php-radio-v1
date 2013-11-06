<?php
require_once('../res/getid3/getid3.php');

$mainmenu_user = htmlentities($_SESSION['user']);
$mainmenu = <<<MM
		
MM;
$priv = $_SESSION['privileges'];

// mysql_connect($dbip, $dbuser, $dbpass);
// @mysql_select_db($dbname);


// $test_uid = $_SESSION['user'];
// $usertest = mysql_query("SELECT * FROM `users` WHERE `id`=$test_uid;");
// if(mysql_num_rows($usertest) == 0) {
	// mysql_close();
	// header("Location: /x_admin/logout.php");
	// return;
// }

// mysql_close();



$mm_array = array();
if($priv >= 1) {
	$mm_array[] = '<a href="/x_admin/main.php">Main</a>';
	$mm_array[] = '<a href="/x_admin/logout.php">Logout</a>';
	$mm_array[] = '<a href="/x_admin/chpwd.php">Change password</a>';
	$mm_array[] = '<a href="/x_admin/pending.php">Review pending submissions</a>';
	$mm_array[] = '<a href="/x_admin/viewdb.php">View music database</a>';
//        $mm_array[] = '<a href="/x_admin/tags.php">HELP TAGGING FAGGOT</a>';
}
if($priv >= 2) {
	$mm_array[] = '<a href="/x_admin/editprofile.php">Edit DJ profile</a>';
	$mm_array[] = '<a href="/hiroto/etc/streamdesk">Streamdesk folder</a>';
	$mm_array[] = '<a href="/x_admin/settings.php">Settings and commands</a>';
}
if($priv >= 3) {
	$mm_array[] = '<a href="/x_admin/news.php">Edit news</a>';
}
if($priv >= 4) {
	$mm_array[] = '<a href="/x_admin/users.php">Edit users</a>';
	$mm_array[] = '<a href="/x_admin/viewbans.php">Site bans</a>';
}
if($priv >= 5) {
	$mm_array[] = '<a href="/x_admin/relays.php">Edit Relays</a>';
}


$main_menu = "<h4 style=\"padding-bottom: 5px; margin-bottom:5px;\">Welcome, $mainmenu_user</h4>" . implode(' | ', $mm_array) . "<br>";

?>
