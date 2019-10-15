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
        $window = self::getWindow($int);
        $pos = self::getBitToFlip($int);

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

        return $encoded;
    }
}

$types = [1,15,46,47,1234];
$encoded = NsecEncoder::encode($types);

$encoded = str_split($encoded, 8);

foreach ($encoded as $hextet) {
    $hextet = bindec('0b'.$hextet);
    echo '0x'.str_pad(dechex($hextet), 2, '0', STR_PAD_LEFT) . ' ';
}
