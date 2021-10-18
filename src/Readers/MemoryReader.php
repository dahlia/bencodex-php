<?php

namespace Bencodex\Readers;

use Bencodex\Writers\MemoryWriter;

/**
 * A reader that holds an in-memory buffer.
 */
class MemoryReader implements Reader
{
    /**
     * @var string The in-memory buffer, which contains the read part as well as
     *             the unread part.
     */
    private $buffer;

    /**
     * @var int The internal pointer to refers to the total number of the read
     *          bytes.
     */
    private $offset;

    /**
     * Creates a {@see MemoryWriter} instance.
     * @param string $buffer The input bytes to be consumed later.
     * @throws \TypeError Thrown when the buffer is not a string.
     */
    public function __construct($buffer)
    {
        if (!is_string($buffer)) {
            throw new \TypeError(
                'Expected a string, not ' . gettype($buffer) . '.'
            );
        }
        $this->buffer = $buffer;
        $this->offset = 0;
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
        if ($size < 1 || $this->offset >= strlen($this->buffer)) {
            return '';
        }
        $read = substr($this->buffer, $this->offset, $size);
        $this->offset += strlen($read);
        return $read;
    }

    /**
     * @inheritDoc
     */
    public function tell()
    {
        return $this->offset;
    }

    /**
     * @inheritDoc
     */
    public function seek($size)
    {
        if (!is_int($size)) {
            throw new \TypeError(
                'Expected an integer, not ' . gettype($size) . '.'
            );
        }
        if ($size < 0 && $this->offset < -$size) {
            $this->offset = 0;
        } else {
            $this->offset += $size;
            if ($this->offset > strlen($this->buffer)) {
                $this->offset = strlen($this->buffer);
            }
        }
    }
}
