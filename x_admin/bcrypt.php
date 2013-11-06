<?php

// equiv to rand, mt_rand
// returns int in *closed* interval [$min,$max]                                                
function devurandom_rand($min = 0, $max = 0x7FFFFFFF) {
    $diff = $max - $min;
    if ($diff < 0 || $diff > 0x7FFFFFFF) {
	throw new RuntimeException("Bad range");
    }
    $bytes = mcrypt_create_iv(4, MCRYPT_DEV_URANDOM);
    if ($bytes === false || strlen($bytes) != 4) {
        throw new RuntimeException("Unable to get 4 bytes");
    }
    $ary = unpack("Nint", $bytes);
    $val = $ary['int'] & 0x7FFFFFFF;   // 32-bit safe                           
    $fp = (float) $val / 2147483647.0; // convert to [0,1]                          
    return round($fp * $diff) + $min;
}


class PassHash {
        public function RandString($length) {
                $chars = "0123456789./qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM"; //only allowed chars in the blowfish s$
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

