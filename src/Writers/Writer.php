<?php

namespace Bencodex\Writers;

/**
 * An interface to write a byte string (which can be quite long) in
 * separated multiple chunks.
 */
interface Writer
{
    /**
     * Writes the given chunk of bytes.
     * @param string $bytes The byte string to write.  This is usually a chunk
     *                      of a longer byte string.
     * @return void
     * @throws \TypeError Thrown when the given bytes is not a string value.
     */
    public function write($bytes);
}
