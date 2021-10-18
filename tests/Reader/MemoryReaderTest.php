<?php

namespace Bencodex\Tests\Reader;

use Bencodex\Readers\MemoryReader;
use Bencodex\Tests\TestUtils;

class MemoryReaderTest extends \PHPUnit\Framework\TestCase
{
    use TestUtils;

    public function testConstruct()
    {
        $r = new MemoryReader('foobar');
        $this->assertEquals('foobar', $r->read(7));

        $this->assertThrows('TypeError', function () {
            new MemoryReader(1234);
        });
    }

    public function testRead()
    {
        $r = new MemoryReader("foo\nbar\nbaz\n");
        $this->assertEmpty($r->read(0));
        $this->assertEmpty($r->read(-123));
        $this->assertEquals("foo\n", $r->read(4));
        $this->assertEquals("bar\n", $r->read(4));
        $this->assertEquals("baz\n", $r->read(5));
        $this->assertEquals('', $r->read(5));
    }
}
