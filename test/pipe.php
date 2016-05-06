<?php

use Krak\Crypto;

describe('Pipe', function() {
    describe('#pipe_pad_streams', function() {
        it('pipes a stream to another and pads the src stream', function() {
            $pad = new Crypto\Pkcs7Pad();
            $src = fopen('php://memory', 'w+');
            $dst = fopen('php://memory', 'w+');
            fwrite($src, 'abc');
            rewind($src);
            Crypto\pipe_pad_streams($pad, 4, 11, $src, $dst);

            rewind($dst);
            $contents = stream_get_contents($dst);
            assert($contents === 'abc' . chr(1));
        });
    });
});
