<?php

namespace Krak\Crypto;

/** Encrypts a stream of contents. This uses null padding internally */
class McryptStreamCrypt implements StreamCrypt
{
    private $key;
    private $cipher;
    private $mode;

    private $pad;
    private $iv_gen;
    private $blocksize;
    private $ivsize;

    public function __construct($key, Pad $pad = null, $iv_gen = null, $chunksize = null, $cipher = MCRYPT_RIJNDAEL_128, $mode = MCRYPT_MODE_CBC) {
        $this->key = $key;
        $this->cipher = $cipher;
        $this->mode = $mode;
        $this->pad = $pad ?: new Pkcs7Pad();
        $this->iv_gen = $iv_gen ?: mcrypt_iv_gen();
        $this->chunksize = $chunksize ?: 1024 * 32;

        $this->blocksize = mcrypt_get_block_size($cipher, $mode);
        $this->ivsize = mcrypt_get_iv_size($cipher, $mode);
    }

    public function streamEncrypt($src, $dst) {
        $iv = call_user_func($this->iv_gen, $this->ivsize);

        fwrite($dst, $iv);
        stream_filter_append($dst, 'mcrypt.'.$this->cipher, STREAM_FILTER_WRITE, [
            'key' => $this->key,
            'iv' => $iv,
            'mode' => $this->mode,
        ]);

        pipe_pad_streams($this->pad, $this->blocksize, $this->chunksize, $src, $dst);
    }

    public function streamDecrypt($src, $dst) {
        $iv = fread($src, $this->ivsize);

        stream_filter_append($src, 'mdecrypt.'.$this->cipher, STREAM_FILTER_READ, [
            'key' => $this->key,
            'iv' => $iv,
            'mode' => $this->mode,
        ]);

        pipe_strip_streams($this->pad, $this->blocksize, $this->chunksize, $src, $dst);
    }
}
