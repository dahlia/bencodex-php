<?php

namespace Bencodex\Tests\Writers;

use Bencodex\Tests\TestUtils;
use Bencodex\Writers\StreamWriter;
use PHPUnit\Framework\TestCase;

class StreamWriterTest extends TestCase
{
    use TestUtils;

    public function testConstruct()
    {
        $this->assertThrows('TypeError', function () {
            new StreamWriter('not a resource');
        });
    }

    public function testWrite()
    {
        $file = tmpfile();
        $this->assertNotFalse($file);
        $w = new StreamWriter($file);
        $w->write('');
        fflush($file);
        $this->assertEquals(0, ftell($file));
        fseek($file, 0);
        $this->assertEmpty(fread($file, 1));
        fseek($file, 0, SEEK_END);
        $w->write('foo');
        fflush($file);
        $this->assertEquals(3, ftell($file));
        fseek($file, 0);
        $this->assertEquals('foo', fread($file, 4));
        fseek($file, 0, SEEK_END);
        $w->write('bar');
        fflush($file);
        $this->assertEquals(6, ftell($file));
        fseek($file, 0);
        $this->assertEquals('foobar', fread($file, 7));
        fseek($file, 0, SEEK_END);
        $w->write('....');
        fflush($file);
        $this->assertEquals(10, ftell($file));
        fseek($file, 0);
        $this->assertEquals('foobar....', fread($file, 11));
        fseek($file, 0, SEEK_END);
        $this->assertThrows('TypeError', function () use ($w) {
            $w->write(1234);
        });
    }

    public function testGetHandle()
    {
        $file = tmpfile();
        $this->assertNotFalse($file);
        $w = new StreamWriter($file);
        $this->assertSame($file, $w->getHandle());
    }
}
