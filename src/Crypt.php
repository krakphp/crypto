<?php

namespace Krak\Crypto;

interface Crypt
{
    public function encrypt($data);
    public function decrypt($data);
}
