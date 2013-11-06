<?php
	include($_SERVER["DOCUMENT_ROOT"] . "/templates/header.php");
	echo '<div class="container page" id="page-p-stats">';
	include($_SERVER["DOCUMENT_ROOT"] . "/stats/stats.php");
	echo '</div>';
	include($_SERVER["DOCUMENT_ROOT"] . "/templates/footer.php");
?>
