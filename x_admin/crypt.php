<?php
header("Content-Type: text/html; charset=utf-8");

$plaintext = "d3b0ebb9990d5efb6355fa15c5dc0642a4e89577";

echo "Plaintext: $plaintext <br>";

function compress_hex($hash_str) {
    $bin_str = hex2bin($hash_str);
    $b64_str = base64_encode($bin_str);
    return rtrim(str_replace(array("+", "="), array("-", "_"), $b64_str), "_");
}

function decompress_hex($comp_str) {
	$b64_str = str_replace(array("-", "_"), array("+", "="), $comp_str);
	$bin_str = base64_decode($b64_str);
	return bin2hex($bin_str);
}
$comp = compress_hex($plaintext);
echo "SHA-c: " . $comp;
echo "<br>SHA-d: " . decompress_hex($comp);



?>
