<?php

namespace Krak\Crypto\Stream;

use Krak\Crypto;

class Base64Stream
{
    private $chunk_size;

    public function __construct($chunk_size) {
        $this->chunk_size = $chunk_size;
    }

    public function encode() {
        return Crypto\base64_encode_stream($this->chunk_size);
    }

    public function decode() {
        return Crypto\base64_decode_stream($this->chunk_size);
    }
}
