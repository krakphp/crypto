<?php

namespace Krak\Crypto;

/** A decorator for base64 encoding/decoding encrypted/decrypted text */
class Base64Crypt implements Crypt
{
    private $crypt;
    public function __construct(Crypt $crypt) {
        $this->crypt = $crypt;
    }

    public function encrypt($data) {
        return base64_encode($this->crypt->encrypt($data));
    }

    public function decrypt($data) {
        return $this->crypt->decrypt(base64_decode($data));
    }
}
