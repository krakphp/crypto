<?php

namespace Krak\Crypto\Stream;

use Krak\Crypto;

class CryptStream
{
    private $crypt;
    private $chunk_size;

    public function __construct(Crypto\Crypt $crypt, $chunk_size = 1024) {
        $this->crypt = $crypt;
        $this->chunk_size = $chunk_size;
    }

    public function encrypt() {
        return Crypto\encrypt_stream($this->crypt, $this->chunk_size, $header = true);
    }

    public function decrypt() {
        return Crypto\decrypt_stream($this->crypt, null);
    }
}
