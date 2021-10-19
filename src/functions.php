<?php

namespace Bencodex;

use Bencodex\Codec\Decoder;
use Bencodex\Codec\Encoder;
use Bencodex\Codec\TextEncodingError;
use Bencodex\Readers\MemoryReader;
use Bencodex\Writers\MemoryWriter;

/**
 * Encodes the given value into Bencodex data.
 *
 * This is a facade of {@see Encoder} class.
 * @param mixed $value The value to be encoded in Bencodex.
 * @param string|null $textEncoding Determines what text encoding the PHP
 *                                  strings you want to encode are in.
 *                                  If the PHP strings are binary data and
 *                                  not Unicode text, then this should be
 *                                  set to null.  UTF-8 by default.
 * @param string|null $keyEncoding Determines what text encoding the string
 *                                 keys of the PHP arrays you want to encode
 *                                 are in. If the PHP string keys are binary
 *                                 keys and not Unicode keys, they should be
 *                                 set to null.  UTF-8 by default.
 * @param boolean $byteOrderMark Whether to respect BOMs (byte order marks).
 *                               If turned on, strings without BOMs are
 *                               considered as binary and BOMs are stripped
 *                               after encoding.
 *                               If turned off (default), there's no special
 *                               treatment for BOM.
 * @return string The encoded Bencodex data.
 * @throws TextEncodingError Thrown when the given text encoding is invalid
 *                           or unsupported by iconv.
 */
function encode(
    $value,
    $textEncoding = 'utf-8',
    $keyEncoding = 'utf-8',
    $byteOrderMark = false
) {
    $encoder = new Encoder($textEncoding, $keyEncoding, $byteOrderMark);
    $buffer = new MemoryWriter();
    $encoder->encode($buffer, $value);
    return $buffer->getBuffer();
}

/**
 * Decodes the given Bencodex data into a PHP value.
 *
 * This is a facade of {@see Decoder} class.
 * @param string $bencodex The Bencodex data to decode.
 * @param string $textEncoding Determines what text encoding Bencodex text
 *                             values are decoded to PHP strings in.
 * @param boolean $byteOrderMark Whether to prepend BOMs (byte order marks).
 *                               If turned on, Bencodex text values are
 *                               decoded to PHP strings with BOMs whether
 *                               the original text has BOM or not.
 *                               If turned off (default), BOM is added to
 *                               the decoded PHP string if and only
 *                               if the original Bencodex text has BOM.
 * @return mixed A decoded value.
 */
function decode($bencodex, $textEncoding = 'utf-8', $byteOrderMark = false)
{
    $decoder = new Decoder($textEncoding, $byteOrderMark);
    $buffer = new MemoryReader($bencodex);
    return $decoder->decode($buffer);
}
