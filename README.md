# Crypto

A well designed cryptographic library for php.

## Install

```
composer require krak/crypto
```

## Design

The Crypto Library has two main interfaces: `Crypt` and `Pad`.

A Crypt is what does the encryption and decryption.

A Pad is what does the padding and stripping.

Currently all encryption is unauthenticated.

## Usage

```php
<?php

use Krak\Crypto;

$key = openssl_random_pseudo_bytes(32);
$crypt = new Crypto\McryptCrypt($key);

$encrypted = $crypt->encrypt('data');
echo $crypt->decrypt($encrypted);
// outputs: data
```

All Crypts implement the interface `Krak\Crypto\Crypt`

You can also use any of the `Krak\Crypto\Pad` classes

```php
<?php

use Krak\Crypto;

$pad = new Crypto\Pkcs7Pad();
$padded = $pad->pad('abc');
echo $pad->strip($padded);
// outputs: abc
```

### Crypt Types

- **Krak\Crypto\McryptCrypt** - handles encryption/decryption via the php mcrypt extension
- **Krak\Crypto\OpenSSLCrypt** - handles encrypt/decryption via the php openssl extension

You can look in the source code for argument signatures of what else you can pass in in order to configure the encryption.

**Note:** Please be knowledgeable of the keys you pass in. The key size depends on the algorithm and typically ranges from 8, 16, 24, or 32 bytes.

Each crypt uses the `Krak\Crypto\pack_payload` method to pack the iv and cipher text. It base64 encodes the concatenated iv and cipher text.

### Pad Types

- **Krak\Crypto\Pkcs7Pad** - pads via the pkcs7 algorithm
- **Krak\Crypto\NullBytePad** - pads by appending null bytes.
- **Krak\Crypto\NoPad** - doesn't apply any padding, just returns the string as is.

### Iv Gen

The crypts take in a parameter for iv generation. There are three types:

- **Krak\Crypto\mcrypt_iv_gen()** - creates a mcrypt iv generator which uses `mcrypt_create_iv`
- **Krak\Crypto\php_iv_gen()** - creates an iv gen that uses `random_bytes`. We use the `paragonie/random_compat` library to handle non php7 users
- **Krak\Crypto\static_iv_gen($iv)** - creates an iv gen that takes an iv and always returns that iv for generation.

## StreamCrypt

StreamCrypt's are for encrypting and decrypting streams of data.

```php
<?php

use Krak\Crypto;

$crypt = new Crypto\McryptStreamCrypt($key);
$src = fopen('in-file', 'r');
$dst = fopen('out-file', 'w');

// reads content from source and encrypts into dst
$crypt->streamEncrypt($src, $dst);

fclose($src);
// this needs to happen in order for the $dst buf to flush all contents
fclose($dst);


$src = fopen('out-file', 'r');
$dst = fopen('in-file', 'w');

// reads encrypted content from source and decrypts it into dst
$crypt->streamDecrypt($src, $dst);

// needed for same reason above
fclose($src);
fclose($dst);
```

**Note:** there is a bug in the php mcrypt stream filter that prevents `fflush` from writing all of the data for a stream. The stream won't write all of its contents *until* the stream has been **closed**

## API

```
interface Crypt {
    public function encrypt($data);
    public function decrypt($data);
}

class McryptCrypt implements Crypt {
    public function __construct($key, Pad $pad = null, $ivgen = null, $cipher = MCRYPT_RIJNDAEL_128, $mode = MCRYPT_MODE_CBC);
}

class OpenSSLCrypt implements Crypt {
    public function __construct($key, $iv_gen = null, $cipher = 'aes-256-cbc');
}

interface StreamCrypt {
    /** takes a plain text source and writes to dest as encrypted */
    public function streamEncrypt($src, $dst);
    /** takes a cipher text src and writes to dst plaintext */
    public function streamDecrypt($src, $dst);
}

class McryptStreamCrypt implements StreamCrypt {
    /**
     * @param $chunksize    The size of the chunks for copying the src to dst streams
     *                      defaults to 32KB
     */
    public function __construct($key, Pad $pad = null, $iv_gen = null, $chunksize = null, $cipher = MCRYPT_RIJNDAEL_128, $mode = MCRYPT_MODE_CBC);
}

interface Pad
{
    public function pad($val, $blocksize);
    public function strip($val, $blocksize);
}

class NoPad {}
class NullBytePad {}
class Pkcs7Pad {}
```


## Test

Run tests with peridot via

```
make test
```
