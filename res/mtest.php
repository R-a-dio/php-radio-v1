<?php
include_once('mdetect.php');

$uagent_obj = new uagent_info();

if($uagent_obj->DetectSmartphone() == $uagent_obj->true) {
	echo "ok you have a smartphone";
}
else {
	echo "no smartphone you fucker";
}


?>