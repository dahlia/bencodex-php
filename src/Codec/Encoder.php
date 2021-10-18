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
     */
    public function __construct($textEncoding = 'utf-8', $keyEncoding = 'utf-8')
    {
        $this->setTextEncoding($textEncoding);
        $this->setKeyEncoding($keyEncoding);
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
            default:
                $typeName = $type === 'object' ? get_class($value) : $type;
                throw new \TypeError(
                    "The type cannot be serialized into Bencodex: $typeName."
                );
        }
    }

    public function encodeDictionary(Writer $writer, $dictionary)
    {
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
        // TODO: Heuristics to determine if elements are dictionaries or lists
        // need to be added.  For example, [['foo' => 1], [], ['bar' => 2]]
        // should be considered as three dictionaries rather than two
        //dictionaries and an empty list.
        foreach ($list as $value) {
            $this->encode($writer, $value);
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
                return true;
            }
        }
        $utf8 = null;
        return false;
    }
}
