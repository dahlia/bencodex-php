<?php

namespace Bencodex\Writers;

/**
 * A writer that put the given byte string into a stream, which usually refers
 * to a file.
 */
class StreamWriter implements Writer
{
    private $handle;

    /**
     * Creates a new {@see StreamWriter} instance with the given handle.
     * @param resource $handle The pointer resource which refers a writable
     *                         stream, usually a file.
     */
    public function __construct($handle)
    {
        if (!is_resource($handle)) {
            throw new \TypeError('Required a file system pointer resource.');
        }

        $this->handle = $handle;
    }

    /**
     * Gets the pointer resource of the stream handle, which usually refers
     * a file, to write bytes into.
     * @return resource The handle resource which was configured using
     *                  the constructor.
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @inheritDoc
     */
    public function write($bytes)
    {
        if (!is_string($bytes)) {
            throw new \TypeError('Required a byte string.');
        }
        if (!empty($bytes)) {
            fwrite($this->handle, $bytes);
        }
    }
}
