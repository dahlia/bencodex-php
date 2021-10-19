<?php

namespace Bencodex\Codec;

use Bencodex\Writers\MemoryWriter;
use Bencodex\Writers\Writer;

/**
 * Encodes PHP values into corresponding Bencodex data.
 */
final class Encoder
{
    /**
     * @var string|null Determines what text encoding the PHP strings you want
     *                  to encode are in.  If the PHP strings are binary data
     *                  and not Unicode text, then this should be set to null.
     *                  Note that it can be treated as binary data if a PHP
     *                  string is not a valid sequence of the specified text
     *                  encoding.
     */
    private $textEncoding;

    /**
     * @var string|null Determines what text encoding the string keys of the PHP
     *                  arrays you want to encode are in.  If the PHP string
     *                  keys are binary keys and not Unicode keys, they should
     *                  be set to null.
     *                  Note that it can be treated as binary data if a PHP
     *                  string is not a valid sequence of the specified text
     *                  encoding.
     */
    private $keyEncoding;

    /**
     * @var bool  Whether to respect BOMs (byte order marks).
     *            If turned on, strings without BOMs are considered as binary
     *            and BOMs are stripped after encoding.
     *            If turned off (default), there's no special treatment for BOM.
     */
    public $byteOrderMark = false;

    /**
     * Creates a new {@see Encoder} instance.
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
     * @return void
     * @throws TextEncodingError Thrown when the given text encoding is invalid
     *                           or unsupported by iconv.
     */
    public function __construct(
        $textEncoding = 'utf-8',
        $keyEncoding = 'utf-8',
        $byteOrderMark = false
    ) {
        $this->setTextEncoding($textEncoding);
        $this->setKeyEncoding($keyEncoding);
        $this->byteOrderMark = $byteOrderMark;
    }

    /**
     * Determines what text encoding the PHP strings you want to encode are in.
     * @return string|null Determines what text encoding the PHP strings you
     *                     want to encode are in.
     */
    public function getTextEncoding()
    {
        return $this->textEncoding;
    }

    /**
     * Determines what text encoding the PHP strings you want to encode are in.
     * @param string|null $textEncoding Determines what text encoding the PHP
     *                                  strings you want to encode are in.
     *                                  If the PHP strings are binary data and
     *                                  not Unicode text, then this should be
     *                                  set to null.
     *                                  Note that it can be treated as binary
     *                                  data if a PHP string is not a valid
     *                                  sequence of the specified text encoding.
     * @return void
     * @throws TextEncodingError Thrown when the given text encoding is invalid
     *                           or unsupported by iconv.
     */
    public function setTextEncoding($textEncoding)
    {
        if (!is_null($textEncoding)) {
            if (!is_string($textEncoding)) {
                throw new \TypeError(
                    'The text encoding must be a string or null, not ' .
                    gettype($textEncoding) . '.'
                );
            }
            if (!validateTextEncoding($textEncoding)) {
                throw new TextEncodingError(
                    "Invalid or unsupported encoding: $textEncoding."
                );
            }
            $textEncoding = strtolower($textEncoding);
        }
        $this->textEncoding = $textEncoding;
    }

    /**
     * Determines what text encoding the string keys of the PHP arrays you want
     * to encode are in.
     * @return string|null Determines what text encoding the string keys of
     *                     the PHP arrays you want to encode are in.
     */
    public function getKeyEncoding()
    {
        return $this->keyEncoding;
    }

    /**
     * Determines what text encoding the string keys of the PHP arrays you want
     * to encode are in.
     * @param string|null $keyEncoding Determines what text encoding the string
     *                                 keys of the PHP arrays you want to encode
     *                                 are in. If the PHP string keys are binary
     *                                 keys and not Unicode keys, they should be
     *                                 set to null.
     *                                 Note that it can be treated as binary
     *                                 data if a PHP string is not a valid
     *                                 sequence of the specified text encoding.
     * @return void
     * @throws TextEncodingError Thrown when the given text encoding is invalid
     *                           or unsupported by iconv.
     */
    public function setKeyEncoding($keyEncoding)
    {
        if (!is_null($keyEncoding)) {
            if (!is_string($keyEncoding)) {
                throw new \TypeError(
                    'The key encoding must be a string or null, not ' .
                    gettype($keyEncoding) . '.'
                );
            }
            if (!validateTextEncoding($keyEncoding)) {
                throw new TextEncodingError(
                    "Invalid or unsupported encoding: $keyEncoding."
                );
            }
            $keyEncoding = strtolower($keyEncoding);
        }
        $this->keyEncoding = $keyEncoding;
    }

    /**
     * Encodes the given value into Bencodex data.
     * @param Writer $writer The writer to which the Bencodex data will be
     *                       written.  In order to get an in-memory string,
     *                       use {@see MemoryWriter}.
     * @param mixed $value The value to be encoded in Bencodex.
     * @return void
     */
    public function encode(Writer $writer, $value)
    {
        $type = gettype($value);
        switch ($type) {
            case 'NULL':
                $this->encodeNull($writer);
                break;
            case 'boolean':
                $this->encodeBoolean($writer, $value);
                break;
            case 'integer':
            case 'double':
                $this->encodeInteger($writer, $value);
                break;
            case 'string':
                $this->encodeString($writer, $this->textEncoding, $value);
                break;
            case 'array':
                if (array_is_list($value)) {
                    $this->encodeList($writer, $value);
                } else {
                    $this->encodeDictionary($writer, $value);
                }
                break;
            case 'object':
                $this->encodeDictionary($writer, $value);
                break;
            default:
                throw new \TypeError(
                    "The type cannot be serialized into Bencodex: $type."
                );
        }
    }

    public function encodeDictionary(Writer $writer, $dictionary)
    {
        settype($dictionary, 'array');
        $writer->write('d');
        $binKeys = [];
        $unicodeKeys = [];
        foreach ($dictionary as $key => $value) {
            settype($key, 'string');
            if ($this->shouldBeText($this->keyEncoding, $key, $utf8Key)) {
                $unicodeKeys[$utf8Key] = $value;
            } else {
                $binKeys[$key] = $value;
            }
        }
        ksort($binKeys, SORT_STRING);
        foreach ($binKeys as $key => $value) {
            $this->encodeBinary($writer, $key);
            $this->encode($writer, $value);
        }
        ksort($unicodeKeys, SORT_STRING);
        foreach ($unicodeKeys as $utf8Key => $value) {
            $this->encodeText($writer, $utf8Key);
            $this->encode($writer, $value);
        }
        $writer->write('e');
    }

    public function encodeList(Writer $writer, $list)
    {
        $writer->write('l');
        // Determine empty arrays' type
        $emptyArrayAsDict = false;
        foreach ($list as $value) {
            if (is_array($value) && !empty($value)) {
                if (array_is_list($value)) {
                    $emptyArrayAsDict = false;
                    break;
                } else {
                    $emptyArrayAsDict = true;
                }
            }
        }
        foreach ($list as $value) {
            if (is_array($value) && empty($value) && $emptyArrayAsDict) {
                $this->encodeDictionary($writer, $value);
            } else {
                $this->encode($writer, $value);
            }
        }
        $writer->write('e');
    }

    public function encodeNull(Writer $writer)
    {
        $writer->write(b'n');
    }

    public function encodeBoolean(Writer $writer, $boolean)
    {
        $writer->write($boolean ? 't' : 'f');
    }

    public function encodeInteger(Writer $writer, $integer)
    {
        settype($integer, 'int');
        $writer->write("i${integer}e");
    }

    public function encodeString(Writer $writer, $encoding, $string)
    {
        if ($this->shouldBeText($encoding, $string, $utf8)) {
            $this->encodeText($writer, $utf8);
            return;
        }
        $this->encodeBinary($writer, $string);
    }

    public function encodeText(Writer $writer, $utf8)
    {
        $writer->write('u');
        if (substr($utf8, 0, 3) === "\xef\xbb\xbf") {
            $utf8 = strlen($utf8) == 3 ? '' : substr($utf8, 3);
        }
        $this->encodeBinary($writer, $utf8);
    }

    public function encodeBinary(Writer $writer, $binary)
    {
        $length = strlen($binary);
        $writer->write("$length:");
        $writer->write($binary);
    }

    private function shouldBeText($encoding, $string, &$utf8)
    {
        if (!is_null($encoding)) {
            $utf8 = @iconv($encoding, 'utf-8', $string);
            if ($utf8 !== false) {
                return !$this->byteOrderMark ||
                    substr($utf8, 0, 3) == "\xef\xbb\xbf";
            }
        }
        $utf8 = null;
        return false;
    }
}
