<?php



class NsecEncoder
{
    public static function encode($types)
    {
        $blocks = [];
    
        foreach($types as $int) {
            $window = $int >> 8;
            $int = $int & 0b11111111;
            $mod = $int % 8;

            $mask = $blocks[$window] ?? str_repeat("\0", 32);
            $byteNum = ($int - $mod) / 8;
            $byte = ord($mask[$byteNum]) | (128 >> $mod);
            $mask[$byteNum] = chr($byte);
            $blocks[$window] = $mask;
        }
    
        $encoded = '';
        foreach($blocks as $n => $mask) {
            $mask = rtrim($mask, "\0");
            $encoded .= chr($n) . chr(strlen($mask)) . $mask;
        }
    
        return $encoded;
    }
}

class NsecDecoder
{
    public static function decode($encoded)
    {
        $bytes = unpack('C*', $encoded);
        $types = [];

        while (count($bytes) > 0) {
            $mask = '';
            $window = array_shift($bytes);
            $len = array_shift($bytes);

            for ($i = 0; $i < $len; $i++) {
                $mask .= str_pad(decbin(array_shift($bytes)), 8, '0', STR_PAD_LEFT);
            }

            $offset = 0;
            while (false !== $pos = strpos($mask, '1', $offset)) {
                $types[] = $window * 256 + $pos;
                $offset = $pos + 1;
            }
        }

        return $types;
    }
}

$types = [1,15,46,47,1234];
$encoded = NsecEncoder::encode($types);
$decoded = NsecDecoder::decode($encoded);
var_dump($decoded);
