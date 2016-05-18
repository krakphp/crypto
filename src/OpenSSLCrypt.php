<?php

namespace Krak\Crypto;

class OpenSSLCrypt implements Crypt
{
    private $key;
    private $cipher;
    private $blocksize;
    private $ivsize;
    private $pad;
    private $iv_gen;

    public function __construct($key, Pad $pad = null, $iv_gen = null, $cipher = 'aes-128-cbc', $blocksize = 16) {
        $this->key = $key;
        $this->cipher = $cipher;
        $this->blocksize = $blocksize;
        $this->pad = $pad ?: new Pkcs7Pad();
        $this->iv_gen = $iv_gen ?: php_iv_gen();
        $this->ivsize = openssl_cipher_iv_length($this->cipher);
    }

    public function encrypt($data) {
        $iv = call_user_func($this->iv_gen, $this->ivsize);

        $encrypted = openssl_encrypt(
            $this->pad->pad($data, $this->blocksize),
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            $iv
        );

        return pack_payload($iv, $encrypted);
    }

    public function decrypt($data) {
        $iv = substr($data, 0, $this->ivsize);
        $data = substr($data, $this->ivsize);

        $unencrypted = openssl_decrypt(
            $data,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            $iv
        );

        return $this->pad->strip($unencrypted, $this->blocksize);
    }
}
