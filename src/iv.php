<?php

namespace Krak\Crypto;

function php_iv_gen() {
    return 'random_bytes';
}

function mcrypt_iv_gen() {
    return 'mcrypt_create_iv';
}

function static_iv_gen($iv) {
    return function($len) use ($iv) {
        return $iv;
    };
}
