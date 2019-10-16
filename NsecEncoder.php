<?php


class NsecEncoder
{
    private static function getWindow(int $int)
    {
        return $int >> 8;
    }

    private static function getBitToFlip(int $int)
    {
        return $int & 0b11111111;
    }

    private static function flipBit(string &$mask, int $bit)
    {
        if (strlen($mask) < $bit) {
            $diff = $bit - strlen($mask);
            $mask .= str_repeat('0', $diff);
        }

        $mask[$bit] = '1';
    }

    private static function pad(string &$mask)
    {
        $mod = strlen($mask) % 8;
        if (0 === $mod) {
            return $mask;
        }

        $padlen = strlen($mask) + 8 - $mod;

        $mask = str_pad($mask, $padlen, '0', STR_PAD_RIGHT);
    }

    private static function maskLen(string &$mask)
    {
        $len = strlen($mask) / 8;
        $len = decbin((int) $len);
        $len = str_pad($len, 8, '0', STR_PAD_LEFT);

        $mask = $len.$mask;
    }

    private static function addToWindowBlocks(int $int, array &$windowBlocks)
    {
        $window = $int >> 8; //self::getWindow($int);
        $pos = $int & 0b11111111; //self::getBitToFlip($int);

        if (!array_key_exists($window, $windowBlocks)) {
            $windowBlocks[$window] = '0';
        }

        $mask = $windowBlocks[$window];
        self::flipBit($mask, $pos);

        $windowBlocks[$window] = $mask;
    }

    public static function encode(array $types)
    {
        $windowBlocks = [];

        foreach ($types as $type) {
            self::addToWindowBlocks($type, $windowBlocks);
        }

        $encoded = '';

        foreach ($windowBlocks as $block => &$mask) {
            self::pad($mask);
            self::maskLen($mask);
            $block = str_pad(decbin($block), 8, '0', STR_PAD_LEFT);
            $encoded .= $block.$mask;
        }

        $bytes = array_map('bindec', str_split($encoded, 8));

        return pack('C*', ...$bytes);
    }
}

$types = [1,15,46,47,1234];
$encoded = NsecEncoder::encode($types);

$bytes = unpack('C*', $encoded);

foreach ($bytes as $byte) {
    echo '0x'.dechex($byte).' ';
}
//var_dump($bytes);
$iterator = new \ArrayIterator($bytes);

$blockWindows = [];

while ($iterator->valid()) {
    $window = $iterator->current();
    $mask = '';

    $iterator->next();
    $len = $iterator->current();
    $nextWindow = $iterator->key() + $len;

    $iterator->next();

    echo "$len, $nextWindow\n";

    for ($i=0;$i<$len;$i++) {
        $mask .= str_pad(decbin($iterator->current()), 8, '0', STR_PAD_LEFT);
        $iterator->next();
    }

    $blockWindows[$window] = $mask;
}



var_dump($blockWindows);
