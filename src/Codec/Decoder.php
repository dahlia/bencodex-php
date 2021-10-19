<?php

namespace Bencodex\Codec;

use Bencodex\Readers\Reader;

final class Decoder
{
    /**
     * @var string Determines what text encoding Bencodex text values are
     *             decoded to PHP strings in.
     */
    private $textEncoding;

    /**
     * @var boolean Whether to prepend BOMs (byte order marks).
     *              If turned on, Bencodex text values are decoded to
     *              PHP strings with BOMs whether the original text has BOM
     *              or not.
     *              If turned off (default), BOM is added to the decoded PHP
     *              string if and only if the original Bencodex text has BOM.
     */
    public $byteOrderMark;

    /**
     * @param string $textEncoding Determines what text encoding Bencodex text
     *                             values are decoded to PHP strings in.
     * @param boolean $byteOrderMark Whether to prepend BOMs (byte order marks).
     *                               If turned on, Bencodex text values are
     *                               decoded to PHP strings with BOMs whether
     *                               the original text has BOM or not.
     *                               If turned off (default), BOM is added to
     *                               the decoded PHP string if and only
     *                               if the original Bencodex text has BOM.
     */
    public function __construct(
        $textEncoding = 'utf-8',
        $byteOrderMark = false
    ) {
        $this->setTextEncoding($textEncoding);
        $this->byteOrderMark = $byteOrderMark;
    }

    /**
     * Determines what text encoding Bencodex text values are decoded to PHP
     * strings in.
     * @return string Determines what text encoding Bencodex text values are
     *                decoded to PHP strings in.
     */
    public function getTextEncoding()
    {
        return $this->textEncoding;
    }

    /**
     * Determines what text encoding Bencodex text values are decoded to PHP
     * strings in.
     * @param string $textEncoding The text encoding to be used for representing
     *                             Unicode text in PHP.
     * @return void
     * @throws TextEncodingError Thrown when the given text encoding is invalid
     *                           or unsupported by iconv.
     */
    public function setTextEncoding($textEncoding)
    {
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
        $this->textEncoding = strtolower($textEncoding);
    }

    /**
     * Decodes Bencodex data from the source reader into a PHP value.
     * @param Reader $reader The source to read the Bencodex data to decode.
     * @return mixed A decoded value.
     */
    public function decode(Reader $reader)
    {
        $value = $this->decodeValue($reader);
        $byte = $reader->read(1);
        if ($byte !== '') {
            $error = self::unexpectedByte($reader, $byte);
            $reader->seek(-1);
            throw $error;
        }
        return $value;
    }

    private function decodeValue(Reader $reader)
    {
        $head = $reader->read(1);
        switch ($head) {
            case '':
                throw self::unexpectedTermination($reader);
            case 'n':
                return null;
            case 'f':
                return false;
            case 't':
                return true;
            case 'i':
                return self::decodeInteger($reader);
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
                $reader->seek(-1);
                return self::decodeBinary($reader, $_);
            case 'u':
                return $this->decodeText($reader);
            case 'l':
                return $this->decodeList($reader);
            case 'd':
                return $this->decodeDictionary($reader);
            default:
                throw self::unexpectedByte($reader, $head);
        }
    }

    private function decodeDictionary(Reader $reader)
    {
        // Assumes a byte 'd' was already read.
        $dict = new \stdClass();
        while (true) {
            $peek = $reader->read(1);
            switch ($peek) {
                case '':
                    throw self::unexpectedTermination($reader);
                case 'e':
                    break 2;
                case '0':
                case '1':
                case '2':
                case '3':
                case '4':
                case '5':
                case '6':
                case '7':
                case '8':
                case '9':
                    $reader->seek(-1);
                    $key = self::decodeBinary($reader, $_);
                    break;
                case 'u':
                    $key = $this->decodeText($reader);
                    break;
                default:
                    throw self::unexpectedByte($reader, $peek);
            }
            $value = $this->decodeValue($reader);
            $dict->$key = $value;
        }
        return $dict;
    }

    private function decodeList(Reader $reader)
    {
        // Assumes a byte 'u' was already read.
        $list = [];
        while (true) {
            $peek = $reader->read(1);
            if ($peek == '') {
                throw self::unexpectedTermination($reader);
            }
            if ($peek == 'e') {
                break;
            }
            $reader->seek(-1);
            $element = $this->decodeValue($reader);
            array_push($list, $element);
        }
        return $list;
    }

    private function decodeText(Reader $reader)
    {
        // Assumes a byte 'u' was already read.
        $utf8 = self::decodeBinary($reader, $contentOffset);
        if (self::validateUtf8($utf8)) {
            $bom = "\xef\xbb\xbf";
            if ($this->byteOrderMark && substr($utf8, 0, 3) !== $bom) {
                $utf8 = "$bom$utf8";
            }
            $t = ($this->textEncoding == 'utf-8' ||
                $this->textEncoding == 'utf8')
                ? $utf8
                : @iconv('utf-8', $this->textEncoding, $utf8);
            if ($t === false) {
                $end = $reader->tell();
                throw new TextEncodingError(
                    'Failed to decode a UTF-8 string into the ' .
                    "requested text encoding {$this->textEncoding} " .
                    "between offset $contentOffset and $end; try " .
                    'another text encoding which covers the entire ' .
                    'Unicode character set.'
                );
            }
            return $t;
        }
        $end = $reader->tell();
        throw new DecodingError(
            'Failed to decode a Bencodex data; invalid UTF-8 ' .
            "sequence between offset $contentOffset and $end."
        );
    }

    private static function decodeBinary(Reader $reader, &$contentOffset)
    {
        $length = self::decodeNumeric($reader, ':');
        settype($length, 'integer');
        $contentOffset = $reader->tell();
        $binary = $reader->read($length);
        if (strlen($binary) < $length) {
            throw self::unexpectedTermination($reader);
        }
        return $binary;
    }

    private static function decodeInteger(Reader $reader)
    {
        // Assumes a byte 'i' was already read.
        $digits = self::decodeNumeric($reader, 'e');
        return $digits > PHP_INT_MAX ? $digits : intval($digits);
    }

    private static function decodeNumeric(Reader $reader, $until)
    {
        $digits = '';
        while (true) {
            $digit = $reader->read(1);
            if ($digit == '') {
                throw self::unexpectedTermination($reader);
            }
            if ($digit == '-' && empty($digits)) {
                $digits = '-';
                continue;
            }
            if (ctype_digit($digit)) {
                $digits .= $digit;
                continue;
            }
            if ($digit == $until) {
                break;
            }
            throw self::unexpectedByte($reader, $digit);
        }
        return $digits;
    }

    private static function validateUtf8($bytes)
    {
        if (function_exists('preg_match')) {
            return preg_match('//u', $bytes);
        }
        return mb_check_encoding($bytes, 'utf-8');
    }

    private static function unexpectedByte(Reader $reader, $byte)
    {
        $offset = $reader->tell() - 1;
        $hex = sprintf('0x%02x', $byte);
        return new DecodingError(
            "Failed to decode a Bencodex data at offset $offset; " .
            "an unexpected byte: $hex ('$byte')."
        );
    }

    private static function unexpectedTermination(Reader $reader)
    {
        $offset = $reader->tell();
        return new DecodingError(
            "Failed to decode a Bencodex data at offset $offset; " .
            'unexpected termination.'
        );
    }
}
