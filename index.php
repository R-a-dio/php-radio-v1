<?php
	// :V - Vin
	//if (@$_SERVER['HTTPS'] != "on") {
	//	header("Location: https://www.r-a-d.io");
	//}
	include("templates/header.php");
       	echo '<div class="container page" id="page-p-home">';
	include("home.php");
	echo '</div>';
	include("templates/footer.php");
?>
