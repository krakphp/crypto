<?php

namespace Krak\Crypto;

/** A decorator for performing hmac signing and authentication on messages.

    This will append the signature to the cipher that was encrypted and it will
    verify and remove the signature from the cipher being decrypted.

    The default size of the signature is 32 bytes (for sha256)
*/
class HmacCrypt implements Crypt
{
    private $crypt;
    private $key;
    private $algo;
    private $size;

    public function __construct(Crypt $crypt, $key, $algo = 'sha256', $size = 32) {
        $this->crypt = $crypt;
        $this->key = $key;
        $this->algo = $algo;
        $this->size = $size;
    }

    private function sign($cipher) {
        return hash_hmac($this->algo, $cipher, $this->key, true);
    }

    public function encrypt($data) {
        $cipher = $this->crypt->encrypt($data);
        $signature = $this->sign($cipher);
        return $cipher . $signature;
    }

    public function decrypt($data) {
        $cipher = substr($data, 0, $this->size * -1);
        $signature = substr($data, $this->size * -1);

        if (!hash_equals($this->sign($cipher), $signature)) {
            throw new HmacException('hmac signature did not match');
        }

        return $this->crypt->decrypt($cipher);
    }
}
