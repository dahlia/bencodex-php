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
        $this->assertEquals(0, $r->tell());
        $this->assertEmpty($r->read(-123));
        $this->assertEquals(0, $r->tell());
        $this->assertEquals("foo\n", $r->read(4));
        $this->assertEquals(4, $r->tell());
        $this->assertEquals("bar\n", $r->read(4));
        $this->assertEquals(8, $r->tell());
        $this->assertEquals("baz\n", $r->read(5));
        $this->assertEquals(12, $r->tell());
        $this->assertEquals('', $r->read(5));
        $this->assertEquals(12, $r->tell());
    }

    public function testSeek()
    {
        $r = new MemoryReader("foo\nbar\nbaz\n");
        $r->seek(0);
        $this->assertEquals(0, $r->tell());
        $r->seek(-123);
        $this->assertEquals(0, $r->tell());
        $r->seek(3);
        $this->assertEquals(3, $r->tell());
        $this->assertEquals("\nbar", $r->read(4));
        $this->assertEquals(7, $r->tell());
        $r->seek(100);
        $this->assertEquals(12, $r->tell());
        $this->assertEquals('', $r->read(5));
        $this->assertEquals(12, $r->tell());
        $r->seek(-2);
        $this->assertEquals("z\n", $r->read(5));
    }
}
