#!/usr/bin/env php
<?php

use Krak\Crypto;

require_once __DIR__ . '/../vendor/autoload.php';

function stream_encrypt(Crypto\Crypt $crypt, $src, $dst) {
    $stream = new Crypto\StreamPipe(Crypto\read_stream($src, 1024));

    $stream->pipe(Crypto\encrypt_stream($crypt, 1024))
        ->pipe(Crypto\base64_encode_stream(1024))
        ->pipe(Crypto\write_stream($dst));
}

function stream_decrypt(Crypto\Crypt $crypt, $src, $dst) {
    $stream = new Crypto\StreamPipe(Crypto\read_stream($src, 1024));

    $stream->pipe(Crypto\base64_decode_stream(1024))
        ->pipe(Crypto\decrypt_stream($crypt, 1024, 32 * 2))
        ->pipe(Crypto\write_stream($dst));
}

function encrypt(Crypto\Crypt $crypt, $src, $dst) {
    $crypt = new Crypto\Base64Crypt($crypt);

    $contents = stream_get_contents($src);
    fwrite($dst, $crypt->encrypt($contents));
}

function decrypt(Crypto\Crypt $crypt, $src, $dst) {
    $crypt = new Crypto\Base64Crypt($crypt);

    $contents = stream_get_contents($src);
    fwrite($dst, $crypt->decrypt($contents));
}

if (count($argv) < 2) {
    return;
}

$src = fopen('php://stdin', 'r');
$dst = fopen('out.txt', 'w');
$crypt = new Crypto\OpenSSLCrypt(str_repeat('a', 16));
$crypt = new Crypto\HmacCrypt($crypt, 'key');

$ts = microtime(true);

if ($argv[1] == 'se') {
    stream_encrypt($crypt, $src, $dst);
} else if ($argv[1] == 'e') {
    encrypt($crypt, $src, $dst);
} else if ($argv[1] == 'sd') {
    stream_decrypt($crypt, $src, $dst);
} else if ($argv[1] == 'd') {
    decrypt($crypt, $src, $dst);
}

$tf = microtime(true);

printf("time: %.4fs\n", $tf - $ts);
printf("memory: %.2fMB\n", memory_get_peak_usage() / 1024 / 1024);
