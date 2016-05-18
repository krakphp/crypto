<?php

namespace Krak\Crypto;

require_once __DIR__ . '/iv.php';
require_once __DIR__ . '/stream.php';

/** pack a payload */
function pack_payload($iv, $data) {
    return $iv . $data;
}
