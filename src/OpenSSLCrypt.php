<?php

namespace Krak\Crypto;

class OpenSSLCrypt implements Crypt
{
    private $key;
    private $cipher;
    private $ivsize;
    private $iv_gen;

    public function __construct($key, $iv_gen = null, $cipher = 'aes-256-cbc') {
        $this->key = $key;
        $this->cipher = $cipher;
        $this->iv_gen = $iv_gen ?: php_iv_gen();
        $this->ivsize = openssl_cipher_iv_length($this->cipher);
    }

    public function encrypt($data) {
        $iv = call_user_func($this->iv_gen, $this->ivsize);

        $encrypted = openssl_encrypt(
            $data,
            $this->cipher,
            $this->key,
            0,
            $iv
        );

        return pack_payload($iv, $encrypted);
    }

    public function decrypt($data) {
        $data = base64_decode($data);

        $iv = substr($data, 0, $this->ivsize);
        $data = substr($data, $this->ivsize);

        $unencrypted = openssl_decrypt(
            $data,
            $this->cipher,
            $this->key,
            0,
            $iv
        );

        return $unencrypted;
    }
}
