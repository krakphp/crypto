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

### Pad Types

- **Krak\Crypto\Pkcs7Pad** - pads via the pkcs7 algorithm

## Test

Run tests with peridot via

```
make test
```
