<?php

use Krak\Crypto;

describe('Stream', function() {
    describe('#stream_to_str', function() {
        it('Takes a stream and buffers it into a string', function() {
            $str = Crypto\stream_to_str(['a', 'b']);
            assert($str == 'ab');
        });
    });
    describe('#read_stream', function() {
        it('takes a php stream and turns into a stream of chunks', function() {
            $stream = fopen('php://memory', 'rw');
            fwrite($stream, 'ab0cd');
            rewind($stream);

            assert('ab0cd' == Crypto\stream_to_str(Crypto\read_stream($stream, 1)));
        });
    });
    describe('#str_stream', function() {
        it('converts a string into a stream', function() {
            $chunks = iter\toArray(Crypto\str_stream('abcde', 2));
            assert(
                $chunks[0] == 'ab' &&
                $chunks[1] == 'cd' &&
                $chunks[2] == 'e'
            );
        });
    });
    describe('#map_stream', function() {
        it('maps chunks in a stream', function() {
            $stream = Crypto\str_stream('abcd');
            $map = Crypto\map_stream('strtoupper');
            assert('ABCD' == Crypto\stream_to_str($map($stream)));
        });
    });
    describe('#pipe_stream', function() {
        it('pipes streams into one another', function() {
            $stream = Crypto\str_stream('12ab34CD', 1);
            $pipe = Crypto\pipe_stream([
                Crypto\filter_stream('ctype_alpha'),
                Crypto\filter_stream('ctype_upper')
            ]);
            assert('CD' == Crypto\stream_to_str($pipe($stream)));
        });
    });
    describe('Pipe', function() {
        it('can wrap a stream and pipe it', function() {
            $stream = new Crypto\StreamPipe(Crypto\str_stream('abcd', 2));
            $stream = $stream->pipe(Crypto\map_stream('strtoupper'))
                ->pipe(Crypto\map_stream('str_rot13'));

            assert(str_rot13('ABCD') == Crypto\stream_to_str($stream));
        });
    });
    describe('#chunk_stream', function() {
        it('takes a stream and generates equal sized chunks', function() {
            $stream = Crypto\chunk_stream(2);
            $chunks = iter\toArray($stream(['a', 'ab', 'abc', 'a']));

            assert(
                count($chunks) === 4 &&
                $chunks[0] == 'aa' &&
                $chunks[1] == 'ba' &&
                $chunks[2] == 'bc' &&
                $chunks[3] == 'a'
            );
        });
    });
    describe('#base64_encode_stream', function() {
        it('base64 encodes a stream', function() {
            $str = 'abcdefghij';
            $stream = Crypto\base64_encode_stream(1);
            $encoded = Crypto\stream_to_str($stream(Crypto\str_stream($str, 32)));

            assert(base64_encode($str) === $encoded);
        });
    });
    describe('#base64_decode_stream', function() {
        it('base64 decodes a stream', function() {
            $str = base64_encode('abcdefghij');
            $stream = Crypto\base64_decode_stream(1);
            $encoded = Crypto\stream_to_str($stream(Crypto\str_stream($str, 32)));

            assert(base64_decode($str) === $encoded);
        });
        it('can decode an encoded stream', function() {
            $stream = new Crypto\StreamPipe(Crypto\str_stream('abcd'));
            $stream = $stream->pipe(Crypto\base64_encode_stream(1))
                ->pipe(Crypto\base64_decode_stream(1));

            assert('abcd' == Crypto\stream_to_str($stream));
        });
    });
    describe('#write_stream', function() {
        it('writes a stream into a php stream', function() {
            $fp = fopen('php://memory', 'rw');
            $write = Crypto\write_stream($fp);
            $write(Crypto\str_stream('abcd', 8));

            rewind($fp);
            assert('abcd' == stream_get_contents($fp));
        });
    });
    describe('#encrypt_stream', function() {
        it('encrypts a stream', function() {
            $iv_gen = Crypto\static_iv_gen(random_bytes(16));
            $key = random_bytes(16);
            $crypt = new Crypto\OpenSSLCrypt($key, null, $iv_gen);
            $stream = new Crypto\StreamPipe(Crypto\str_stream('abc'));
            $stream = $stream->pipe(Crypto\encrypt_stream($crypt, 16));

            assert($crypt->encrypt('abc') == Crypto\stream_to_str($stream));
        });
    });
    describe('#decrypt_stream', function() {
        it('decrypts an encrypted stream', function() {
            $crypt = new Crypto\OpenSSLCrypt(random_bytes(16));

            $input = str_repeat('a', 32);
            $stream = new Crypto\StreamPipe(Crypto\str_stream($input));
            $stream = $stream->pipe(Crypto\encrypt_stream($crypt, 14))
                ->pipe(Crypto\decrypt_stream($crypt, 14, 16 + 2));

            assert($input == Crypto\stream_to_str($stream));
        });
        it('decrypts from headers', function() {
            $crypt = new Crypto\OpenSSLCrypt(random_bytes(16));

            $input = str_repeat('a', 32);
            $stream = new Crypto\StreamPipe(crypto\str_stream($input));
            $stream = $stream->pipe(Crypto\encrypt_stream($crypt, 8, true))
                ->pipe(Crypto\decrypt_stream($crypt, null));

            assert($input == Crypto\stream_to_str($stream));
        });
    });
    describe('#header_chunk_stream', function() {
        it('properly chunks based off of headers', function() {
            $chunks = Crypto\add_chunk_header_stream(['a', 'bb', 'ccc', 'dddd', 'eeeee']);
            $str = Crypto\stream_to_str($chunks);
            $chunk_stream = Crypto\chunk_stream(4);
            $chunks = [$str];
            $chunks = $chunk_stream($chunks);
            $chunks = Crypto\header_chunk_stream($chunks);
            $chunks = iterator_to_array($chunks);
            assert(implode('', $chunks) === 'abbcccddddeeeee');
        });
        it('throws an exception if headers are invalid or not found.', function() {
            $chunks = Crypto\header_chunk_stream(['a', 'b']);
            try {
                iterator_to_array($chunks);
                assert(false);
            } catch (RuntimeException $e) {
                assert(true);
            }
        });
    });
});
