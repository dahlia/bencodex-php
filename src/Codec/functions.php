<?php

namespace Bencodex\Codec;

/**
 * Validates if the given text encoding is valid and supported by iconv.
 * @param string $textEncoding A text encoding name to check.
 * @return bool True if the given text encoding is valid.
 */
function validateTextEncoding($textEncoding)
{
    if (!is_string($textEncoding)) {
        throw new \TypeError(
            'A text encoding must be a string, not ' . gettype($textEncoding) .
            '.'
        );
    }

    $s = @iconv($textEncoding, 'utf-8//IGNORE', '');
    return $s !== false;
}

if (!function_exists('array_is_list')) {
    function array_is_list(array $array)
    {
        $expectedKey = 0;
        foreach ($array as $key => $_) {
            if ($key !== $expectedKey) {
                return false;
            }
            $expectedKey++;
        }

        return true;
    }
}
