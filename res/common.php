<?php
require_once('recaptchalib.php');
require_once('Akismet.php');


// Remove this when we can be arsed.
if (isset($_SERVER['HTTP_REFERER']) &&
	 preg_match("/reddit/", $_SERVER['HTTP_REFERER'])) { //my bracket style is better
	header("Location: /reddit.html");
}

// START OF REMOVE THIS PLEASE
// Please remove this soon(tm)
//require_once('/home/wessie/raven-php/lib/Raven/Autoloader.php');

//Raven_Autoloader::register();

//$client = new Raven_Client('http://f6487ea3991641b7b135cdbd58e3db28:e7f3fde838e54e03b06839e59c7f44cf@sentry.wessie.info/4');

//$error_handler = new Raven_ErrorHandler($client);

// register
//set_error_handler(array($error_handler, 'handleError'));
//set_exception_handler(array($error_handler, 'handleException'));

// END OF REMOVE THIS PLEASE


$dbip = "localhost";
$dbname = "radio_main";
$dbuser = "php";
$dbpass = "1234jk23l4j";

$site_menu = <<<SITEMENU
			<div id="navbox">
				<ul id="nav">
					<li>
						<a href="/">Home</a>
					</li>
					<li>
						<a href="/news">News</a>
					</li>
					<li>
						<a href="">Music</a>
						<ul>
							<li><a href="/search">Search</a></li>
							<li><a href="/lastplayed">Last played</a></li>
							<li><a href="/queue">Queue</a></li>
							<li><a href="/submittrack">Submit track</a></li>
						</ul>
					</li>
					<li>
						<a href="/djs">DJs</a>
					</li>
					<li>
						<a href="/webirc">IRC</a>
					</li>
				</ul>
			</div>
SITEMENU;

class PassHash {
	public function RandString($length) {
		$chars = "0123456789./qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM"; //only allowed chars in the blowfish salt.
		$size = strlen($chars);
		$str = "";
		for( $i = 0; $i < $length; $i++ ) {
			$str .= $chars[rand(0, $size - 1)]; // oh yeah, in php a string is also a char array. hello zend and C.
		}
		return $str;
	}
	public function Hash($input) {
		return crypt($input, "$2a$13$" . self::RandString(22));
		// 2y is an exploit fix, and an improvement over 2a. Only available in 5.4.0+
	}
	public function Compare($input, $hash) { return (crypt($input, $hash) === $hash); }
}


// We no longer need this. Ever. (Use bloody PDO)
function mysql_safe($query,$params=false) { 
    if ($params) {
        foreach ($params as &$v) { $v = mysql_real_escape_string($v); }    # Escaping parameters 
        # str_replace - replacing ? -> %s. %s is ugly in raw sql query 
        # vsprintf - replacing all %s to parameters 
        $sql_query = vsprintf( str_replace("?","'%s'",$query), $params );
		# echo $sql_query;
        $sql_res = mysql_query($sql_query);    # Perfoming escaped query 
    } else { 
        $sql_res = mysql_query($query);    # If no params... 
    } 

    return ($sql_res); 
}

// Instead of using seeded random, just read the required length from dev/urandom; this is predictable.
function str_rand($length = 8, $seeds = 'alphanum') {

    // Possible seeds
    $seedings['alpha'] = 'abcdefghijklmnopqrstuvwqyz';
    $seedings['numeric'] = '0123456789';
    $seedings['alphanum'] = 'abcdefghijklmnopqrstuvwqyz0123456789';
    $seedings['hexidec'] = '0123456789abcdef';
    
    // Choose seed
    if (isset($seedings[$seeds]))
    {
        $seeds = $seedings[$seeds];
    }
    
    // Seed generator
    list($usec, $sec) = explode(' ', microtime());
    $seed = (float) $sec + ((float) $usec * 100000);
    mt_srand($seed);
    
    // Generate
    $str = '';
    $seeds_count = strlen($seeds);
    
    for ($i = 0; $length > $i; $i++)
    {
        $str .= $seeds{mt_rand(0, $seeds_count - 1)};
    }
    
    return $str;
}

// No clue what this even is.
function relativate($a, $b) {
	$longs = "";
	$shorts = "";
	if(strlen($a) >= strlen($b)) {
		$longs = $a;
		$shorts = $b;
	}
	else {
		$longs = $b;
		$shorts = $a;
	}
	for($i = 0; $i < strlen($longs); $i++) {
		if($i == strlen($shorts)) {
			return substr($longs, $i, strlen($longs));
		}
		if($longs[$i] != $shorts[$i]) {
			return substr($longs, $i, strlen($longs));
		}
	}
	return "/";
}

// Is this even needed?
function update_queue() {

	if(file_exists("/home/ed/streamqueue/queue.txt")) {
		mysql_connect($dbip, $dbuser, $dbpass);
		@mysql_select_db($dbname);
		
		$queuefile = file("/home/ed/streamqueue/queue.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		
		$count = count($queuefile);
		$i = 1;
		
		mysql_query("DELETE FROM `curqueue`;");
		
		$totdur = (int)$queuefile[0];
		$totdur = $totdur + (time() - 3600*6);
		
		while($i < $count) {
			$line = $queuefile[$i];
			$fspace = strpos($line, " ");
			
			$num = (int)substr($line, 0, $fspace);
			
			$text = substr($line, $fspace + 1, strlen($line) - ($fspace + 1));
			$text = mysql_real_escape_string(substr($text, 0, 200));
			$text = mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text, 'UTF-8, SJIS', true));
			
			$time = date("H:i", $totdur);
			
			mysql_query("INSERT INTO `curqueue` (timestr, track) VALUES ('$time', '$text');");
			
			$totdur = $totdur + $num;
			$i = $i + 1;
		}
		
		mysql_close();
	}
}

// we use piwik now. remove?
function pageView($page, $query, $ip, $login) {
	
	$dbip = "localhost";
	$dbname = "radiosite";
	$dbuser = "radiouser";
	$dbpass = "interplex5";
	
	mysql_connect($dbip, $dbuser, $dbpass);
	@mysql_select_db($dbname);
	
	$query = mysql_real_escape_string($query);
	$login = mysql_real_escape_string($login);
	
	//$l_str = "";
	//if($login != '')
	//	$l_str = "'$login'";
	
	mysql_query("INSERT INTO `pageviews` (`pagename`, `query`, `host`, `login`) VALUES ('$page', '$query', '$ip', '$login');");

	mysql_close();
	
}

/*function hex2bin($hexstr)
    {
    $n = strlen($hexstr);
    $sbin="";  
    $i=0;
    while($i<$n)
    {      
        $a =substr($hexstr,$i,2);          
        $c = pack("H*",$a);
        if ($i==0){$sbin=$c;}
        else {$sbin.=$c;}
        $i+=2;
    }
    return $sbin;
}*/


// You can use the DateTime::__construct() class.
function secs_to_h($secs)
{
        $units = array(
                "week"   => 7*24*3600,
                "day"    =>   24*3600,
                "hour"   =>      3600,
                "minute" =>        60,
                "second" =>         1,
        );
	// specifically handle zero
        if ( $secs == 0 ) return "0 seconds";
        $s = "";
        foreach ( $units as $name => $divisor ) {
                if ( $quot = intval($secs / $divisor) ) {
                        $s .= "$quot $name";
                        $s .= (abs($quot) > 1 ? "s" : "") . ", ";
                        $secs -= $quot * $divisor;
                }
        }
        return substr($s, 0, -2);
}

// we should have this as a "delay" property in the Song class in python.
function song_delay($val) {
	if($val > 30)
		$val = 30;
	if($val >= 0 && $val <= 7)
		return -11057*$val*$val + 172954*$val + 81720;
	else
		return (int)(599955 * exp(0.0372 * $val) + 0.5);
	//return (int)(-123.82*pow($val,3) + 3355.2*pow($val, 2) + 10110*$val + 51584 + 0.5);
	//return (int)(25133*exp(0.1625*$val) + 0.5);
}

$music_file_dir = "res/music/";

$song_req_delay = 3600 * 8; // 8 hours
$ip_req_delay = 3600 * 2;	// 2 hours
$upload_delay = 3600;	// 1 hour

if(isset($_SERVER['REMOTE_ADDR'])) {
	$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
}
else {
	$REMOTE_ADDR = '0.0.0.0';
}

session_start();

// Store the session_name() in the database, and resolve everything from it.
// Heck, don't use a PHPSESSID. Use a standard cookie; search for the cookie name in the login.
// Also it's good practise to store the session IP with it, too, but then mobile users are a little boned.
// Ensure we have something to stop session sniffing when we move to django.
if(!isset($_SESSION['login'])) {
	$_SESSION['login'] = 0;
	$_SESSION['user'] = "";
	$_SESSION['userid'] = -1;
	$_SESSION['privileges'] = 0;
	$_SESSION['nick'] = "";
}

/*mysql_connect($dbip, $dbuser, $dbpass);
@mysql_select_db($dbname);


$lastupdated = mysql_query("SELECT `lastset` FROM `streamstatus`;");
if(mysql_num_rows($lastupdated) != 0) {
	$now = time();
	$lastset = strtotime(mysql_result($lastupdated, 0, "lastset"));

	if($now - $lastset >= 60) {
		//mysql_query("DELETE FROM `streamstatus`;");
	}
}
$loginip = mysql_real_escape_string($REMOTE_ADDR);
$logins = mysql_query("SELECT unix_timestamp(time) as utime FROM `failedlogins` WHERE `ip`='$loginip' ORDER BY `time` DESC LIMIT 3;");

if(mysql_num_rows($logins) == 3) {
	$time1 = (int)mysql_result($logins, 0, "utime");
	$time3 = (int)mysql_result($logins, 2, "utime");
	$now = time();
	if($time1 - $time3 <= 3600 && $now - $time1 <= 3600*24*7) {
		// if the latest 3 attempts are within an hour, ban for a week
		header("Location: http://banned.com");
		mysql_close();
		exit();
	}
}

mysql_close();*/

// daily password to upload unlimited songs without account
$dp = date('Y-m-d').' indeterminate repressiveness!';
//$dp = base64_encode(pack('H*', md5($dp))); //PHP4+
$dp = base64_encode(md5($dp, true)); //PHP5+
$dp = str_replace('/', '', $dp);
$dp = str_replace('+', '', $dp);
define('DAYPASS', substr($dp,0,10));

?>
