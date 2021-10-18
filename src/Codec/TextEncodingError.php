<?php

namespace Bencodex\Codec;

if (PHP_MAJOR_VERSION < 8) {
    require_once __DIR__ . '/TextEncodingError.php73.php';
} else {
    require_once __DIR__ . '/TextEncodingError.php80.php';
}
