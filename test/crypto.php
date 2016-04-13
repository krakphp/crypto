<?php

use Krak\Crypto;

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
    describe('McryptCrypt', function() {
        it('can encrypt and decrypt a string', function() {
            $key = openssl_random_pseudo_bytes(32);
            $crypt = new Crypto\McryptCrypt($key);

            $val = 'a';
            assert($val == $crypt->decrypt($crypt->encrypt($val)));
        });
    });
});
