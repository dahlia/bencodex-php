<?php

namespace Bencodex\Tests\Readers;

use Bencodex\Readers\StreamReader;
use Bencodex\Tests\TestUtils;
use PHPUnit\Framework\TestCase;

class StreamReaderTest extends TestCase
{
    use TestUtils;

    public function testConstruct()
    {
        $this->assertThrows('TypeError', function () {
            new StreamReader(1234);
        });
    }

    public function testRead()
    {
        $f = $this->makeFile("foo\nbar\nbaz\n");
        $r = new StreamReader($f);
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
        $f = $this->makeFile("foo\nbar\nbaz\n");
        $r = new StreamReader($f);
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

    private $tempFiles = [];

    private function makeFile($content)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'bencodex-php-tests');
        file_put_contents($tempFile, $content);
        $fd = fopen($tempFile, 'rb');
        $this->tempFiles[$tempFile] = $fd;
        return $fd;
    }

    public function __destruct()
    {
        foreach ($this->tempFiles as $tempFile => $fd) {
            @fclose($fd);
            @unlink($tempFile);
        }
    }
}
