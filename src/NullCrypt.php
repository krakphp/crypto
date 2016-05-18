<?php

namespace Krak\Crypto;

/** A Crypt that just returns the data returned

    Very useful for debugging or mocking
*/
class NullCrypt implements Crypt
{
    public function encrypt($data) {
        return $data;
    }

    public function decrypt($data) {
        return $data;
    }
}
