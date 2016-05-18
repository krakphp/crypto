<?php

namespace Krak\Crypto;

/** NullByte padding implementation

    Simply appends null bytes to meet the blocksize
*/
class NullBytePad implements Pad
{
    public function pad($val, $blocksize) {
        $padlen = $blocksize - strlen($val) % $blocksize;
        return $val . str_repeat("\0", $padlen);
    }

    public function strip($val, $blocksize) {
        $len = strlen($val);

        if ($len % $blocksize) {
            throw new PadException('Encrypted value is not padded to block size');
        }

        return rtrim($val, "\0");
    }
}
