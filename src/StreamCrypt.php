<?php

namespace Krak\Crypto;

/** StreamCrypt is an interface for taking an input and piping the results as
    encrypted or decrypted. */
interface StreamCrypt {
    /** takes a plain text source and writes to dest as encrypted */
    public function streamEncrypt($src, $dst);
    /** takes a cipher text src and writes to dst plaintext */
    public function streamDecrypt($src, $dst);
}
