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

## Usage

```php
<?php

use Krak\Crypto;

$key = random_bytes(16);
$hmac_key = random_bytes(16);

$crypt = new Crypto\OpenSSLCrypt($key);
$crypt = new Crypto\Base64Crypt(new Crypto\HmacCrypt($crypt, $hmac_key));

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

### Crypt

The Crypt libraries are responsible for encrypting the data. There are crypt implementations that do encryption and others that are just decorators.

**McryptCrypt** and **OpenSSLCrypt** handle encryption. Each crypt uses the `Krak\Crypto\pack_payload` method to prepend the iv to the cipher text.

**Note:** Please be knowledgeable of the keys you pass in. The key size depends on the algorithm and typically ranges from 8, 16, 24, or 32 bytes.

**Base64Crypt**, **HmacCrypt**, and are decorators for providing base64 encoding and hmac signing/authentication for your messages.

**GnuPGCliCrypt** handles encrypting via the `gpg` cli utility.

```php
<?php

$crypt = new Krak\Crypt\GnuPGCliCrypt('User Name', $passphrase = 'secret', $gpg_executable_path = 'gpg');
```

It will encrypt/decrypt data with the public and private keys for the given `$username`. **Important:** you need to make sure the keys are properly imported into your gpg cli tool. We use the `--always-trust` flag for encrypting, so make sure the keys you add are properly trusted.

This crypt also requires the `symfony/process` component to be installed.

**NullCrypt** is used more for testing or mocking. It just returns the data passed to it.

### Pad Types

- **Krak\Crypto\Pkcs7Pad** - pads via the pkcs7 algorithm
- **Krak\Crypto\NullBytePad** - pads by appending null bytes.
- **Krak\Crypto\NoPad** - doesn't apply any padding, just returns the string as is.

### Iv Gen

The crypts take in a parameter for iv generation. There are three types:

- **Krak\Crypto\mcrypt_iv_gen()** - creates a mcrypt iv generator which uses `mcrypt_create_iv`
- **Krak\Crypto\php_iv_gen()** - creates an iv gen that uses `random_bytes`. We use the `paragonie/random_compat` library to handle non php7 users
- **Krak\Crypto\static_iv_gen($iv)** - creates an iv gen that takes an iv and always returns that iv for generation.

## Streams

The crypt library has also created a concept called a Stream. Crypto streams works very similar to nodejs streams, where they are stream of buffers/content. Streams are very handy for encrypting large amounts of data because of how they efficiently pipe their information along. Here's an example of using streams to upper case content, encrypt, and then encode.

```php
<?php

use Krak\Crypto;

$stream = Crypto\str_stream('this is some data'); // create a stream from raw string.
$stream = new Crypto\StreamPipe($stream);

$crypt_stream = new Crypto\Stream\CryptStream(new Crypto\OpenSSLCrypt($key), 16); // encrypt/decrypt 16 byte chunks at a time
$base64_stream = new Crypto\Stream\Base64Stream(64); // encode/decode 64 byte chunks at a time

$key = random_bytes(16);
$dst = fopen('php://stdout', 'w');
$stream->pipe(Crypto\map_stream('strtoupper'))
    ->pipe($crypt_stream->encrypt())
    ->pipe($base64_stream->encode())
    ->pipe(Crypt\write_stream($dst));
// at this point, stdout will have encrypted uppercased info.
```

Look at the API to see all of the different streams and how to use them.

## API

Run `make api` to create the api documentation. Then open up `docs/api/index.html` to view the API docs.

## Test

Run tests with peridot via

```
make test
```
