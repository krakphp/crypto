<?php

namespace Krak\Crypto;

use iter;

/** Create a stream from a php resource stream

    ```php
    $fp = fopen('php://stdin', 'r');
    $stream = read_stream($fp, 1024);
    // convert the stream to a string
    $contents = stream_to_str($stream);
    ```

    @param resource $stream the stream to read from
    @param int $chunksize size of the chunks to read
    @return \Generator yields chunks of $chunksize until stream is empty
*/
function read_stream($stream, $chunksize) {
    while (!feof($stream)) {
        $contents = fread($stream, $chunksize);

        if (strlen($contents)) {
            yield $contents;
        }
    }
}

/** Create a stream from a string

    ```php
    $stream = str_stream('abcd', 1);
    $chunks = iterator_to_array($stream);
    assert($chunks[0] == 'a' && $chunks[3] == 'd');
    ```

    If you leave `$chunksize` null, then it'll just yield the entire string as
    one whole chunk.

    @param string $str The string to chunk
    @param int|null $chunksize The size of the chunks to take from the string or null
    @return \Generator yields chunks of $chunksize or the entire string
*/
function str_stream($str, $chunksize = null) {
    if ($chunksize === null) {
        yield $str;
        return;
    }

    $len = strlen($str);
    for ($i = 0; $i < (int) ($len / $chunksize); $i++) {
        yield substr($str, $i * $chunksize, $chunksize);
    }

    if ($len % $chunksize) {
        yield substr($str, $i * $chunksize);
    }
}

/** Mapping primitive for streams

    Maps chunks from one value to another.

    ```php
    $stream = str_stream('abcd');
    $map = map_stream(function($chunk) { return substr($chunk, 1); });
    $contents = stream_to_str($map($stream));
    // bcd
    ```

    @param callable $predicate the function to map each chunk. Must return the new chunk.
    @return \Closure yields each new mapped chunk.
*/
function map_stream($predicate) {
    return function($chunks) use ($predicate) {
        return iter\map($predicate, $chunks);
    };
}

/** Filter primitive for streams

    ```php
    stream = str_stream('abc', 1);
    $filter = filter_stream(function($chunk) { return $chunk != 'b'; });
    $contents = stream_to_str($filter($stream));
    // ac
    ```

    @param callable $predicate the function to filter each chunk. it returns a boolean
    @return \Closure yields only the chunks that returned true from the filter
*/
function filter_stream($predicate) {
    return function($chunks) use ($predicate) {
        return iter\filter($predicate, $chunks);
    };
}

/** Pipes streams from one to the next

    ```php
    $stream = str_stream('12ab34CD', 1);
    $pipe = pipe_stream([filter_stream('ctype_alpha'), filter_stream('ctype_upper')]);
    assert('CD' == stream_to_str($pipe($stream)));
    ```

    @param array $streams an array of streams to pipe into one another
    @return \Closure yields the chunks by piping them from one stream to the next
*/
function pipe_stream($streams) {
    return function($chunks) use ($streams) {
        return iter\reduce(function($acc, $stream) {
            return $stream($acc);
        }, $streams, $chunks);
    };
}

/** Converts a stream into a string

    ```php
    $stream = read_stream(fopen('php://stdin', 'r'), 1024);
    $contents = stream_to_str($stream);
    // $contents is on string full of stdin
    ```

    @param array|\Iterator the stream of chunks
    @return string the chunks mapped into one string
*/
function stream_to_str($chunks) {
    return iter\reduce(function($acc, $chunk) {
        return $acc . $chunk;
    }, $chunks, '');
}

/** Write a stream into a php stream resource

    ```php
    $dst = fopen('php://stdout', 'w');

    $stream = str_stream('abcd');
    $write = write_stream($dst);
    $write($stream);

    fclose($dst);
    ```

    <b>Note:</b> This is a final stream that consumes the actual stream. No more
    streams can be used after it.

    @param resource $dst the resource to write to
    @return \Closure a function that writes a stream into the php stream
*/
function write_stream($dst) {
    return function($chunks) use ($dst) {
        foreach ($chunks as $chunk) {
            fwrite($dst, $chunk);
        }
    };
}

/** Buffers a stream into equal sized chunks

    ```php
    $stream = ['a', 'bc', 'def', 'ghij', 'k'];
    $chunk = chunk_stream(5);
    $chunks = iterator_to_array($chunk($stream));
    assert($chunks[0] == 'abcde' && $chunks[1] == 'fghij' && $chunks[2] == 'k');
    ```

    @param int $size the size of the chunks to yield
    @return \Closure a function that buffers chunks into equal sized chunks
*/
function chunk_stream($size) {
    return function($chunks) use ($size) {
        $buf = '';

        foreach ($chunks as $chunk) {
            $buf .= $chunk;

            while (strlen($buf) > $size) {
                $chunk = substr($buf, 0, $size);
                yield $chunk;
                $buf = substr($buf, $size);
            }
        }

        if ($buf) {
            // flush any left over
            yield $buf;
        }
    };
}

/** Takes a number and moves it to the nearest multiple of the `$multiple` param */
function _to_nearest_multiple($size, $multiple) {
    if ($size < $multiple) {
        return $multiple;
    }

    return $size - ($size % $multiple);
}

/** Base64 encode a stream

    ```php
    $stream = str_stream('abcd');
    $base64 = base64_encode_stream();
    $encoded = stream_to_str($base64($stream));
    // $encoded == base64_encode('abcd')
    ```

    Buffers the stream into chunks of `$size` then yields encoded chunks.

    <b>Note:</b> The `$size` given isn't always the size of the chunks because
    it needs to be a multiple of 3 in order to encode properly

    @param integer $size the size of the chunks to encode
    @return \Closure a function that yields encoded chunks.
*/
function base64_encode_stream($size = 1024) {
    return function($chunks) use ($size) {
        $chunker = chunk_stream(_to_nearest_multiple($size, 3));

        foreach ($chunker($chunks) as $chunk) {
            yield base64_encode($chunk);
        }
    };
}

/** Base64 decodes a stream

    @see base64_encode_stream
    @param int $size
    @return \Closure a function that yields the decoded chunks
*/
function base64_decode_stream($size = 1024) {
    return function($chunks) use ($size) {
        $chunker = chunk_stream(_to_nearest_multiple($size, 4));

        foreach ($chunker($chunks) as $chunk) {
            yield base64_decode($chunk);
        }
    };
}

/** Encrypts a stream by chunk

    ```php
    $stream = read_stream(fopen('php://stdin', 'r'), 1024);
    $crypt = new OpenSSLCrypt(random_bytes(16));
    $encrypt = encrypt_stream($crypt, 1024);
    $base64 = base64_encode_stream(1024);
    $contents = stream_to_str($base64($encrypt($stream)));
    // $contents = base64 encoded chunks at 1k per chunk (before base64 encoded)
    ```

    <b>Note:</b> The encrypted stream is not compatible with using `$crypt->encrypt()`
    because the stream encryption will encrypt chunks of size `$chunk_size` whereas
    `$crypt->encrypt()` will encrypt the whole thing at once

    <b>Note:</b> The `$crypt` and `$chunk_size` parameter is very important. If you ever plan
    on decrypting, you'll need to make sure both paramters in
    `decrypt_stream` match.

    @param Crypt $crypt The crypt implementation to perfrom the encryption
    @param int $chunk_size The size of the chunks to encrypt
    @return \Closure yields encrypted chunks
*/
function encrypt_stream(Crypt $crypt, $chunk_size, $header = false) {
    return function($chunks) use ($crypt, $chunk_size, $header) {
        $chunker = chunk_stream($chunk_size);
        $chunks = iter\map(function($chunk) use ($crypt, $header) {
            return $crypt->encrypt($chunk);
        }, $chunker($chunks));

        if ($header) {
            $chunks = add_chunk_header_stream($chunks);
        }

        return $chunks;
    };
}

function add_chunk_header_stream($chunks) {
    foreach ($chunks as $chunk) {
        $chunk_size = strlen($chunk); // size in bytes of string
        yield pack('V', $chunk_size) . $chunk;
    }
}

/** Decrypts a stream by chunk

    ```php
    $stream = read_stream(fopen('php://stdin', 'r'), 1024);

    $crypt = new HmacCrypt(OpenSSLCrypt(random_bytes(16)), random_bytes(16));
    $encrypt = encrypt_stream($crypt, 1024);
    // 16 bytes for iv that is prepended
    // 16 bytes for pkcs7 padding (because 1024 is a multiple of 16,
    // if it wasn't then the padding val would be different)
    // 32 bytes for hmac signature (for sha256)
    $decrypt = decrypt_stream($crypt, 1024, 16 + 16 + 32);

    $contents = stream_to_str($decrypt($encrypt($stream)));
    // $contents would be just plain text because it was decrypted
    ```

    <b>Note:</b> Take heed on the `$padding` variable. If you are getting padding
    exceptions when trying to `decrypt_stream`, it's probably because the `$padding`
    size is incorrect.

    @see encrypt_stream
    @param Crypt $crypt The crypt to do the decryption
    @param int $chunk_size The size of the chunks to decrypt
    @param int $padding The extra padding to account for when decrypting
    @return \Closure yields decrypted chunks
*/
function decrypt_stream(Crypt $crypt, $chunk_size, $padding = 32) {
    if ($chunk_size) {
        $chunk_size += $padding;
        return function($chunks) use ($crypt, $chunk_size) {
            $chunker = chunk_stream($chunk_size);
            return iter\map(function($chunk) use ($crypt) {
                return $crypt->decrypt($chunk);
            }, $chunker($chunks));
        };
    }

    return function($chunks) use ($crypt) {
        $chunks = header_chunk_stream($chunks);
        foreach ($chunks as $chunk) {
            yield $crypt->decrypt($chunk);
        }
    };
}

function header_chunk_stream($chunks) {
    $buf = '';
    $size = null;
    foreach ($chunks as $chunk) {
        $buf .= $chunk;
        if ($size === null) {
            list($size, $buf) = _unpack_header($buf);
        }

        while ($size !== null && strlen($buf) >= $size) {
            yield substr($buf, 0, $size);
            $buf = substr($buf, $size);
            list($size, $buf) = _unpack_header($buf);
        }
    }

    if ($buf) {
        throw new \RuntimeException("Invalid headers were found in the chunked data.");
    }
}

function _unpack_header($chunk) {
    if (strlen($chunk) < 4) {
        return [null, $chunk];
    }
    $data = unpack('Vsize', $chunk);
    $size = $data['size'];
    return [$size, substr($chunk, 4)];
}
