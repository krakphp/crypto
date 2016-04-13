<?php

namespace Krak\Crypto;

interface Pad
{
    public function pad($val, $blocksize);
    public function strip($val, $blocksize);
}
