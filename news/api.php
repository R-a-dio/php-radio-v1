<?php
include_once("../res/common.php");
include_once("../res/recaptchalib.php");

//the false there is a safeguard; uncomment to disable comment page
if(/*false &&*/isset($_GET['nid']) && ctype_digit($_GET['nid'])) {
	$nid = (int)$_GET['nid'];
	$callback = false;
	if (isset($_GET['callback'])) {
		$callback = $_GET['callback'];
	}
	mysql_connect($dbip, $dbuser, $dbpass);
	@mysql_select_db($dbname);
	mysql_query("SET NAMES 'utf8';");
	
	$nid = mysql_real_escape_string($nid);
	
	$newsitem = mysql_query("SELECT * FROM `news` WHERE `id`=$nid LIMIT 1;");
	
	if(mysql_num_rows($newsitem) != 1) {
		mysql_close();
		return;
	}
	
	$news_title = htmlspecialchars(mysql_result($newsitem, 0, "header"));
	$news_time = date("D j M, H:i", strtotime(mysql_result($newsitem, 0, "time")));
	$news_text = preg_replace("/\n/", "<br/>", mysql_result($newsitem, 0, "newstext"));
	$news_text = str_replace("TRUNCATE", "", $news_text);
	
	$can_comment = mysql_result($newsitem, 0, "cancomment");
	
	if($can_comment == 1) {
		
		if($_SESSION['userid'] == -1) {
			$publickey = "6LdNGuASAAAAAAXwYu4QfnpDTV-I9lxph47Rlm72";
		#	$captcha = recaptcha_get_html($publickey);
		}
		else {
			$publickey = False;
		#	$captcha = "Logged in; no captcha needed.";
		}
		
		$submit_comment = <<<SUBMIT
<form id="comment-form" action="/news/news.php?nid=$nid" method="post"><table cellspacing="0" cellpadding="2"><tr><td colspan="2" align="center">Submit a comment</td></tr><tr><td>Name: (max 100c)</td><td><input type="text" name="commentname" maxlength="100" size="40" value="Anonymous" /></td></tr><tr><td>Email: (optional)</td><td><input type="text" name="commentemail" maxlength="200" size="40" /></td></tr><tr><td>Comment:</td><td><textarea style="font-family:&quot;arial&quot;, sans-serif; font-size:13px;" name="commenttext" cols="40" rows="5"></textarea></td></tr><tr><td>Captcha:</td><td><div id="captcha"></div></td></tr><tr><td colspan="2" align="center"><input class="btn primary" type="submit" value="Submit" /></td></tr></table></form>
SUBMIT;
		
		$comments = mysql_query("SELECT * FROM `comments` WHERE `nid`=$nid ORDER BY `time` DESC;");
		
		$comment_tables = array();
		
		$num = mysql_num_rows($comments);
		$i = 0;
		
		while($i < $num) {
			$commentid = mysql_result($comments, $i, "id");
			$commenter = mysql_result($comments, $i, "header");
			$commenttime = date("D j M, H:i", strtotime(mysql_result($comments, $i, "time")));
			$count = 10;
			$commenttext = mysql_result($comments, $i, "text");
			$commentmail = mysql_result($comments, $i, "mail");
			$commentlogin = mysql_result($comments, $i, "login");
			//$commenttime = '#'.mysql_result($comments, $i, "id").', '.$commenttime;    # /ed/ - Hacks/General
			if ($commentlogin == '')
			{
				$commenttext = htmlentities($commenttext, ENT_NOQUOTES, 'UTF-8');
			}
			$comment_tables[] = array(
				htmlentities($commentid, ENT_NOQUOTES, 'UTF-8'),
				htmlentities($commenter, ENT_NOQUOTES, 'UTF-8'),
				htmlentities($commentmail, ENT_NOQUOTES, 'UTF-8'),
				htmlentities($commentlogin, ENT_NOQUOTES, 'UTF-8'),
				htmlentities($commenttime, ENT_NOQUOTES, 'UTF-8'),
				preg_replace("/\n/", "<br>", $commenttext)
			);
			
			$i = $i + 1;
		}
		
	}
	else {
		$comment_tables = array(null);
		$submit_comment = "";
	}
	
	mysql_close();
	$result["comments"] = $comment_tables;
	$result["reply"] = $submit_comment;
	$result["content"] = array($news_text, $news_time);
	$result["nid"] = htmlspecialchars($_GET['nid']);
	$result["pub"] = $publickey;
	if ($callback) {
		echo htmlspecialchars($callback) . '(' . json_encode($result) . ');';
	} else {
		echo json_encode($result);
	}
	return;
}
?>
