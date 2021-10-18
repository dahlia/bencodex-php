<?php

namespace Bencodex\Codec;

/**
 * The exception thrown when the configured text encoding is invalid or
 * unsupported by iconv.
 */
class TextEncodingError extends \ValueError
{
}
