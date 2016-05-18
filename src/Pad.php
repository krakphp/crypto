<?php

namespace Krak\Crypto;

/** Interface for padding and stripping data for encryption */
interface Pad
{
    public function pad($val, $blocksize);
    public function strip($val, $blocksize);
}
