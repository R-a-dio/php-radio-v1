<?php
include_once("../res/common.php");
include_once("adminc.php");

if($_SESSION['login'] == 0) {
	header("Location: /x_admin");
        die();
	return;
}
if($_SESSION['privileges'] < 1) {
	header("Location: /");
        die();
	return;
}

$daypass = DAYPASS;
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Admin</title>
                <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
                <link rel="stylesheet" href="style.css" type="text/css" />
                <link rel="stylesheet" href="/css/bootstrap-2.2.2.css" type="text/css" />
	</head>
	<body>
		<div class="alert alert-error">
		    SSL is now enabled and forced. All users <i>must</i> change their passwords as we've been using plaintext logins for a while now. With Telstra, every US ISP (and every government in the world) logging all traffic routed anywhere, you're an idiot if you think HTTP is safe. &mdash;Hiroto
                </div>
		<?= $main_menu ?>
		<br>
		<br>
		<b>Daypass for unlimited uploads: <?= $daypass ?></b><br>
		Daypass can be entered on the Submit page by clicking the "Page" text in the upload form.
	</body>
</html>	
