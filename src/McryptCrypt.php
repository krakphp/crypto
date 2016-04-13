<?php

namespace Krak\Crypto;

class McryptCrypt implements Crypt
{
    private $key;
    private $cipher;
    private $mode;
    private $pad;

    private $ivsize;
    private $blocksize;

    public function __construct($key, Pad $pad = null, $cipher = MCRYPT_RIJNDAEL_128, $mode =  MCRYPT_MODE_CBC) {
        $this->key = $key;
        $this->cipher = $cipher;
        $this->mode = $mode;

        $this->ivsize = mcrypt_get_iv_size($cipher, $mode);
        $this->blocksize = mcrypt_get_block_size($cipher, $mode);
        $this->pad = $pad ?: new Pkcs7Pad();
    }

    public function encrypt($data) {
        $iv = mcrypt_create_iv($this->ivsize);

        $encrypted = mcrypt_encrypt(
            $this->cipher,
            $this->key,
            $this->pad->pad($data, $this->blocksize),
            $this->mode,
            $iv
        );
        $encrypted = $iv . $encrypted;

        return base64_encode($encrypted);
    }

    public function decrypt($data) {
        $data = base64_decode($data);

        $iv = substr($data, 0, $this->ivsize);
        $data = substr($data, $this->ivsize);
        $unencrypted = mcrypt_decrypt(
            $this->cipher,
            $this->key,
            $data,
            $this->mode,
            $iv
        );

        return $this->pad->strip($unencrypted, $this->blocksize);
    }
}
