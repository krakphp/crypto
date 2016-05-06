<?php

namespace Krak\Crypto;

/** Doesn't do any padding */
class NoPad implements Pad
{
    public function pad($val, $blocksize) {
        return $val;
    }

    public function strip($val, $blocksize) {
        return $val;
    }
}
