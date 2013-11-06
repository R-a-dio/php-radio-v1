<?php
include_once("../res/common.php");
include_once("../res/recaptchalib.php");

//the false there is a safeguard; uncomment to disable comment page
if(/*false &&*/isset($_GET['nid']) && ctype_digit($_GET['nid'])) {
	$nid = (int)$_GET['nid'];
	$callback = "news";
	if (isset($_GET['callback'])) {
		$callback = $_GET['callback'];
	}
	if(isset($_POST['commentname'])) {
		//print_r($_POST);
		
		mysql_connect($dbip, $dbuser, $dbpass);
		@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");
		
		
		
		$name = mysql_real_escape_string($_POST['commentname']);
		$mail = mysql_real_escape_string($_POST['commentemail']);
		$text = trim(mysql_real_escape_string(substr($_POST['commenttext'], 0, 1000)));
		
		if($_SESSION['userid'] == -1) {
			$privatekey = "6LdNGuASAAAAAB28zs6NoQemsDcfDtatv_UFPZrz";
			$captcha_resp = recaptcha_check_answer($privatekey, $_SERVER['REMOTE_ADDR'], @$_POST['recaptcha_challenge_field'], @$_POST["recaptcha_response_field"]);		
			$cap_valid = $captcha_resp->is_valid;
		}
		else 
			$cap_valid = true;
		
		$wp_key = "3220b751ba2d";
		$wp_url = "http://r-a-d.io";
		
		$akismet = new Akismet($wp_url, $wp_key);
		$akismet->setCommentAuthor($_POST['commentname']);
		if(strtolower($_POST['commentemail']) === "sage")
			$akismet->setCommentAuthorEmail("");
		else
			$akismet->setCommentAuthorEmail($_POST['commentemail']);
		$akismet->setCommentAuthorURL("");
		$akismet->setCommentContent($_POST['commenttext']);
		$akismet->setPermalink("http://r-a-d.io/news.php?nid=$nid");
		$isSpam = $akismet->isCommentSpam();
		$isSpam = $isSpam || (substr_count($_POST['commenttext'], 'http://') >= 4); // hack to prevent link spam
		
		$site_text = "Thank you for your comment";
		if(!$cap_valid || $text == "" || strlen($text) >= 1000 || $isSpam) {
			if($text != "") {
				if($captcha_resp->error == "incorrect-captcha-sol") {
					$site_text = "<span style=\"color:red;\">You seem to have mistyped the verification.</span>";
				}
				elseif(strlen($text) >= 1000) {
					$site_text = "Your comment was too long;<br /><br />".$text;
				}
				elseif($isSpam) {
					$site_text = "Your comment was filtered as spam. Apologies if it wasn't.";
				}
				else {
					$site_text = "An error occurred: " . $captcha_resp->error;
				}
			}
			else {
				$site_text = "You did not enter a comment.";
			}
		}
		
		$submit_site = <<<SUBMIT
<html>
<head>
	<title>r/a/dio</title>
	<meta http-equiv="refresh" content="5;url=/news/?nid=$nid">
</head>
<body>
	<center><h2>$site_text</h2></center><br/>
	<center><h3>You will be redirected shortly.</h3></center>
</body>
</html>
SUBMIT;
		echo $submit_site;
		if(!$cap_valid || $text == "" || strlen($text) >= 1000 || $isSpam) {
			mysql_close();
			return;
		}
		if($_SESSION['userid'] == -1) {
			$loginname = "";
		}
		else {
			$usr = $_SESSION['userid'];
			$userdata = mysql_query("SELECT djid FROM `users` WHERE `id`=$usr LIMIT 1;");
			if(mysql_num_rows($userdata) == 1) {
				$djid = mysql_result($userdata, 0, "djid");
				if(!is_null($djid)) {
					$djdata = mysql_query("SELECT * FROM `djs` WHERE `id`=$djid LIMIT 1;");
					$loginname = mysql_result($djdata, 0, "djname");
					if(strlen($loginname) == 0)
						$loginname = $loginname = $_SESSION['user'];
				}
				else {
					$loginname = $_SESSION['user'];
				}
			}
			else {
				$loginname = "";
			}
		}
		
		mysql_query("INSERT INTO `comments` (nid, header, mail, text, login, time) VALUES ($nid, '$name', '$mail', '$text', '$loginname', NOW());");
		
		mysql_close();
		if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
			header("Location: /news/?nid=$nid");
		}
		return;
	}
	
	mysql_connect($dbip, $dbuser, $dbpass);
	@mysql_select_db($dbname);
	mysql_query("SET NAMES 'utf8';");
	
	
	
	$nid = mysql_real_escape_string($nid);
	
	$newsitem = mysql_query("SELECT * FROM `news` WHERE `id`=$nid LIMIT 1;");
	
	if(mysql_num_rows($newsitem) != 1) {
		mysql_close();
		header("Location: /news/");
		return;
	}
	
	$news_title = htmlspecialchars(mysql_result($newsitem, 0, "header"));
	$news_time = date("D j M, H:i", strtotime(mysql_result($newsitem, 0, "time")));
	$news_text = preg_replace("/\n/", "<br/>", mysql_result($newsitem, 0, "newstext"));
	
	$can_comment = mysql_result($newsitem, 0, "cancomment");
	
	if($can_comment == 1) {
		
		if($_SESSION['userid'] == -1) {
			$publickey = "6LdNGuASAAAAAAXwYu4QfnpDTV-I9lxph47Rlm72";
			$captcha = recaptcha_get_html($publickey);
		}
		else {
			$publickey = False;
			$captcha = "Logged in; no captcha needed.";
		}
		
		$submit_comment = <<<SUBMIT
<form id="comment-form" action="/news/news.php?nid=$nid" method="post"><table cellspacing="0" cellpadding="2"><tr><td colspan="2" align="center">Submit a comment</td></tr><tr><td>Name: (max 100c)</td><td><input type="text" name="commentname" maxlength="100" size="40" value="Anonymous" /></td></tr><tr><td>Email: (optional)</td><td><input type="text" name="commentemail" maxlength="200" size="40" /></td></tr><tr><td>Comment:</td><td><textarea style="font-family:&quot;arial&quot;, sans-serif; font-size:13px;" name="commenttext" cols="40" rows="5"></textarea></td></tr><tr><td>Captcha:</td><td><div id="captcha">$captcha</div></td></tr><tr><td colspan="2" align="center"><input class="btn primary" type="submit" value="Submit" /></td></tr></table></form>
SUBMIT;
		
		$comments = mysql_query("SELECT * FROM `comments` WHERE `nid`=$nid ORDER BY `time` DESC;");
		
		$comment_tables = "";
		
		$num = mysql_num_rows($comments);
		$i = 0;
		
		while($i < $num) {
			$commenter = mysql_result($comments, $i, "header");
			$commenttime = date("D j M, H:i", strtotime(mysql_result($comments, $i, "time")));
			$count = 10;
			$commenttext = mysql_result($comments, $i, "text");
			$commentmail = mysql_result($comments, $i, "mail");
			$commentlogin = mysql_result($comments, $i, "login");
			
			if ($commenter == "") {
				$commenter = "Anonymous";
			}
			$login = '';
			if ($commentlogin != "") {
				$login = '<abbr title="This was posted by ' . $commentlogin . '.">&#9733;</abbr>';
			}
			$mailto = '';
			if ($commentmail != "") {
				$mailto = ' href="mailto:' . $commentmail . '"';
			}
			$comment_tables .= "<div class=\"comment well\"><div class=\"cmt_head\"><a class=\"cmt_name\"$mailto>$commenter</a>$login<span class=\"cmt_time\">$commenttime</span></div><div class=\"cmt_text\">$commenttext</div></div>";
		
			$i = $i + 1;
		}
		
	}
	else {
		$comment_tables = '';
		$submit_comment = "";
	}
	
	mysql_close();
	echo <<<SITE
		<div class="news" value="$nid"><h5><a href="/news/?nid=$nid">$news_title</a></h5>
		<div class="well content"><div class="nw_time">$news_time</div>$news_text</div>
		$submit_comment
		$comment_tables</div>
		<div id="comment_info" class="fade modal" style="display: none;" data-backdrop="true" data-keyboard="true">
			<div class="modal-header">
				<a class="close" href="#">Å~</a>
				<h3>News comment</h3>
			</div>
			<div class="modal-body">
			
			</div>
		</div>
SITE;
	
	return;
}



$page = 1;
if(isset($_GET['page']) && ctype_digit($_GET['page'])) {
	$page = (int)$_GET['page'];
}

mysql_connect($dbip, $dbuser, $dbpass);

@mysql_select_db($dbname);
mysql_query("SET NAMES 'utf8';");



$news = mysql_query("SELECT * FROM `news` ORDER BY `time` DESC;");

$rescount = mysql_num_rows($news);

$pagecount = ceil($rescount / 20);

$resstart = ($page - 1) * 20;
$count = 0;

$news_items = "";

while($count < 20 && $resstart < $rescount) {
	$nid = mysql_result($news, $resstart, "id");
	$header = htmlspecialchars(mysql_result($news, $resstart, "header"));
	$time = date("D j M, H:i", strtotime(mysql_result($news, $resstart, "time")));
	$newstext = str_replace("\n", "<br/>", mysql_result($news, $resstart, "newstext"));
	$newstext = str_replace("TRUNCATE", "", $newstext);
	$news_item = <<<NEWSITEM
					<div class="news" value="$nid">
						<h5><a href="/news/?nid=$nid">$header</a></h5>
					</div>
NEWSITEM;
	
	$news_items = $news_items . $news_item . "\n";
	
	$count = $count + 1;
	$resstart = $resstart + 1;
}

$pagenav = "";

$i = 1;
while($i <= $pagecount) {
	if($i == (int)$page) {
		$pnav = "<span class=\"cur\">$i</span>";
	}
	else {
		$pnav = "<span><a href=\"/news/?page=$i\">$i</a></span>";
	}
	
	$pagenav = $pagenav . "\n" . $pnav;
	$i = $i + 1;
}

$site = $news_items . '<div id="comment_info" class="fade modal" style="display: none;" data-backdrop="true" data-keyboard="true">
			<div class="modal-header">
				<a class="close" href="#">√ó</a>
				<h3>News comment</h3>
			</div>
			<div class="modal-body">
			
			</div>
		</div>';

echo $site;

?>
