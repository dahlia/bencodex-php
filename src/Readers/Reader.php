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
     *                  reads nothing and an empty string is returned.
     * @return string The read bytes.  It may be shorter than the requested
     *                size if there is not enough bytes to read.
     * @throws \TypeError Thrown when the requested size is not an integer.
     */
    public function read($size);

    /**
     * Gets the current offset in the buffer.
     * @return int The current offset.
     */
    public function tell();

    /**
     * Fast-forwards or rewinds the offset.
     * @param int $size The number of bytes to move the offset pointer.
     *                  Negative integers rewind and positive integers
     *                  fast-forward.  (Zero is no-op.)
     *                  If the destination is less than zero or greater than
     *                  the entire length, the offset is set to zero or
     *                  the end of the entire buffer.
     * @return void
     * @throws \TypeError Thrown when the size is not an integer.
     */
    public function seek($size);
}
