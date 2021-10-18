<?php

namespace Bencodex\Writers;

/**
 * A writer that copies the given byte string into an in-memory buffer and
 * allows to get the written byte string from the buffer.
 */
class MemoryWriter implements Writer
{
    /**
     * @var array The in-memory buffer.
     */
    private $buffer;

    /**
     * @var int The total number of written bytes.
     */
    private $length;

    /**
     * Creates a new {@see MemoryWriter} instance with an empty buffer.
     */
    public function __construct()
    {
        $this->buffer = [];
        $this->length = 0;
    }

    /**
     * @inheritDoc
     */
    public function write($bytes)
    {
        if (!is_string($bytes)) {
            throw new \TypeError(
                'Required a byte string, not ' . gettype($bytes) . '.'
            );
        }
        if (!empty($bytes)) {
            array_push($this->buffer, $bytes);
            $this->length += strlen($bytes);
        }
    }

    /**
     * Gets the written contents from the buffer.
     * @return string The byte string written into the buffer.
     */
    public function getBuffer()
    {
        if ($this->length < 1) {
            return '';
        }
        $joined = join('', $this->buffer);
        $this->buffer = [$joined];
        return $joined;
    }

    /**
     * Gets the total number of written bytes.
     * @return int The total number of written bytes.
     */
    public function getLength()
    {
        return $this->length;
    }

    public function __toString()
    {
        return $this->getBuffer();
    }
}
