<?php

namespace Krak\Crypto;

class EncryptOnlyCrypt implements Crypt
{
    private $crypt;

    public function __construct(Crypt $crypt) {
        $this->crypt = $crypt;
    }

    public function encrypt($data) {
        return $this->crypt->encrypt($data);
    }

    public function decrypt($data) {
        throw new \RuntimeException("This crypt can only be used for encrypting");
    }
}
