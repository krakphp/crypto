<?php

use Krak\Crypto,
    org\bovigo\vfs\vfsStream;

describe('Crypto', function() {
    describe('Pkcs7Pad', function() {
        describe('->pad', function() {
            it('pads a string', function() {
                $pad = new Crypto\Pkcs7Pad();
                $val = 'a';

                $padded_val = $pad->pad($val, 1);
                assert($padded_val == 'a' . chr(1));
            });
        });
        describe('->strip', function() {
            it('strips the pad off the string', function() {
                $pad = new Crypto\Pkcs7Pad();
                $val = 'a' . chr(1);

                assert('a' == $pad->strip($val, 1));
            });
        });
    });
    describe('NullBytePad', function() {
        describe('->pad', function() {
            it('pads a string', function() {
                $pad = new Crypto\NullBytePad();
                $val = 'a';

                $padded_val = $pad->pad($val, 2);
                assert($padded_val == "a\0");
            });
        });
        describe('->strip', function() {
            it('strips the pad off the string', function() {
                $pad = new Crypto\NullBytePad();
                $val = "a\0";

                assert('a' == $pad->strip($val, 2));
            });
        });
    });

    $test_crypt = function(Crypto\Crypt $crypt, $name) {
        describe($name, function() use ($crypt) {
            it('can encrypt and decrypt a string', function() use ($crypt) {
                $val = str_repeat('a', 16);
                assert($val == $crypt->decrypt($crypt->encrypt($val)));
            });
        });
    };
    $key = random_bytes(16);
    $test_crypt(new Crypto\McryptCrypt($key, new Crypto\NoPad()), 'McryptCrypt');
    $test_crypt(new Crypto\OpenSSLCrypt($key), 'OpenSSLCrypt');

    describe('McryptStreamCrypt', function() {
        beforeEach(function() {
            $this->root = vfsStream::setup();
        });

        it('it can encrypt and decrypt a file', function() {
            $path = $this->root->url() . '/enc';
            $src = fopen('php://memory', 'w');
            $dst = fopen($path, 'w');
            $val = str_repeat('a', 15);
            fwrite($src, $val);
            rewind($src);

            $key = str_repeat('c', 16);
            $crypt = new Crypto\McryptStreamCrypt($key, new Crypto\Pkcs7Pad());
            $crypt->streamEncrypt($src, $dst);

            fclose($src);
            fclose($dst);

            $src = fopen($path, 'r');
            $dst = fopen('php://memory', 'w');
            $crypt->streamDecrypt($src, $dst);

            rewind($dst);
            $contents = stream_get_contents($dst);

            fclose($src);
            fclose($dst);

            assert($contents == $val);
        });
    });
});
