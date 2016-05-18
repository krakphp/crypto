<?php

namespace Krak\Crypto;

/** Interface for encrypting and decrypting data. A crypt instance should be decrypt
    any of the data the same instance encrypted */
interface Crypt
{
    public function encrypt($data);
    public function decrypt($data);
}
