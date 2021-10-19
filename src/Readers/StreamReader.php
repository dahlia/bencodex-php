<?php

namespace Bencodex\Readers;

/**
 * A stream reader, which usually refers to a file.
 */
class StreamReader implements Reader
{
    private $handle;

    /**
     * Creates a new {@see StreamReader} instance with the given handle.
     * @param resource $handle The pointer resource which refers a readable
     *                         stream, usually a file.
     * @return void
     * @throws \TypeError Thrown when the given handle is not a resource.
     */
    public function __construct($handle)
    {
        if (!is_resource($handle)) {
            throw new \TypeError('Required a file system pointer resource.');
        }

        $this->handle = $handle;
    }

    /**
     * @inheritDoc
     */
    public function read($size)
    {
        if (!is_int($size)) {
            throw new \TypeError(
                'Expected an integer, not ' . gettype($size) . '.'
            );
        }
        if ($size < 1) {
            return '';
        }
        $read = fread($this->handle, $size);
        return is_string($read) ? $read : '';
    }

    /**
     * @inheritDoc
     */
    public function tell()
    {
        return ftell($this->handle);
    }

    /**
     * @inheritDoc
     */
    public function seek($offset)
    {
        if (!is_int($offset)) {
            throw new \TypeError(
                'Expected an integer, not ' . gettype($offset) . '.'
            );
        }
        if ($offset > 0) {
            fread($this->handle, $offset);
            return;
        }
        if ($offset < 0) {
            fseek($this->handle, $offset, SEEK_CUR);
        }
    }
}
