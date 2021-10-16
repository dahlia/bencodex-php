<?php

namespace Bencodex\Tests;

use Bencodex\MemoryWriter;
use Bencodex\Test\TestUtils;
use PHPUnit\Framework\TestCase;

class MemoryWriterTest extends TestCase
{
    use TestUtils;

    public function testConstruct()
    {
        $w = new MemoryWriter();
        $this->assertEmpty($w->getBuffer());
        $this->assertEquals(0, $w->getLength());
    }

    public function testWrite()
    {
        $w = new MemoryWriter();
        $this->assertEmpty($w->getBuffer());
        $this->assertEquals(0, $w->getLength());
        $w->write('');
        $this->assertEmpty($w->getBuffer());
        $this->assertEquals(0, $w->getLength());
        $w->write('foo');
        $this->assertEquals('foo', $w->getBuffer());
        $this->assertEquals(3, $w->getLength());
        $w->write('bar');
        $this->assertEquals('foobar', $w->getBuffer());
        $this->assertEquals(6, $w->getLength());
        $w->write('....');
        $this->assertEquals('foobar....', $w->getBuffer());
        $this->assertEquals(10, $w->getLength());
        $this->assertThrows('TypeError', function () use ($w) {
            $w->write(1234);
        });
    }

    public function testGetLength()
    {
        $w = new MemoryWriter();
        $this->assertEquals(0, $w->getLength());
        $w->write('');
        $this->assertEquals(0, $w->getLength());
        $w->write('foo');
        $this->assertEquals(3, $w->getLength());
        $w->write('bar');
        $this->assertEquals(6, $w->getLength());
        $w->write('....');
        $this->assertEquals(10, $w->getLength());
    }

    public function testGetBuffer()
    {
        $w = new MemoryWriter();
        $this->assertEmpty($w->getBuffer());
        $w->write('');
        $this->assertEmpty($w->getBuffer());
        $w->write('foo');
        $this->assertEquals('foo', $w->getBuffer());
        $w->write('bar');
        $this->assertEquals('foobar', $w->getBuffer());
        $w->write('...');
        $this->assertEquals('foobar...', $w->getBuffer());
    }

    public function testToString()
    {
        $w = new MemoryWriter();
        $w->write('foobar');
        $this->assertEquals('foobar', (string)$w);
    }
}
