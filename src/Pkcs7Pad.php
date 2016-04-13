<?php

namespace Krak\Crypto;

/** Pkcs7 padding implementation */
class Pkcs7Pad implements Pad
{
    public function pad($val, $blocksize) {
    $padlen = $blocksize - strlen($val) % $blocksize;
        return $val . str_repeat(chr($padlen), $padlen);
    }

    public function strip($val, $blocksize) {
        $len = strlen($val);

        if ($len % $blocksize) {
            throw new PadException('Encrypted value is not padded to block size');
        }

        $padlen = ord($val[$len - 1]);
        $padding = substr($val, $len - $padlen);
        if ($padlen != substr_count($padding, chr($padlen))) {
            throw new PadException("Invalid Pkcs7 Padding");
        }

        return substr($val, 0, $len - $padlen);
    }
}
