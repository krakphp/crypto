<?php

namespace Krak\Crypto;

require_once __DIR__ . '/iv.php';
require_once __DIR__ . '/pipe.php';

/** pack a payload */
function pack_payload($iv, $data) {
    return base64_encode($iv . $data);
}
