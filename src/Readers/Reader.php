<?php

namespace Bencodex\Readers;

/**
 * An interface to read a byte string (which can be quite long) in separated
 * multiple chunks.
 */
interface Reader
{
    /**
     * Reads a chunk of bytes.
     * @param int $size The size of bytes to request.  Zero or negative integers
     *                  consume nothing and an empty string is returned.
     * @return string The read bytes.  It may be shorter than the requested
     *                size if the internal buffer is almost consumed.
     * @throws \TypeError Thrown when the requested size is not an integer.
     */
    public function read($size);
}
