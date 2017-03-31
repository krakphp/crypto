<?php

namespace Krak\Crypto;

class EncryptOnlyPGCrypt implements Crypt
{
    private $gpg;

    public function __construct($gpg) {
        $this->gpg = $gpg;
    }

    public function encrypt($data) {
        return $this->gpg->encrypt($data);
    }

    public function decrypt($data) {
        throw new \RuntimeException("This crypt can only be used for encrypting");
    }
}
