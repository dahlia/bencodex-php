<?php

namespace Bencodex\Tests\Codec;

use Bencodex\Codec\Decoder;
use Bencodex\Readers\MemoryReader;
use Bencodex\Tests\TestUtils;
use PHPUnit\Framework\TestCase;

class DecoderTest extends TestCase
{
    use TestUtils;

    public function testConstruct()
    {
        $d = new Decoder();
        $this->assertEquals('utf-8', $d->getTextEncoding());
        $this->assertFalse($d->byteOrderMark);

        $d = new Decoder('euc-kr');
        $this->assertEquals('euc-kr', $d->getTextEncoding());
        $this->assertFalse($d->byteOrderMark);

        $d = new Decoder('ascii', true);
        $this->assertEquals('ascii', $d->getTextEncoding());
        $this->assertTrue($d->byteOrderMark);

        $this->assertThrows('Bencodex\Codec\TextEncodingError', function () {
            new Decoder('utf-1');
        });
        $this->assertThrows('TypeError', function () {
            new Decoder(123);
        });
    }

    public function testGetTextEncoding()
    {
        $d = new Decoder();
        $this->assertEquals('utf-8', $d->getTextEncoding());
        $d = new Decoder('euc-kr');
        $this->assertEquals('euc-kr', $d->getTextEncoding());
    }

    public function testSetTextEncoding()
    {
        $d = new Decoder();
        $d->setTextEncoding('euc-kr');
        $this->assertEquals('euc-kr', $d->getTextEncoding());
        $this->assertThrows('TypeError', function () use ($d) {
            $d->setTextEncoding(123);
        });
        $this->assertThrows(
            'Bencodex\Codec\TextEncodingError',
            function () use ($d) {
                $d->setTextEncoding('utf-1');
            }
        );
    }

    public function testDecode()
    {
        $d = new Decoder();
        $dEucKr = new Decoder('euc-kr');
        $dAscii = new Decoder('ascii');
        $dBom = new Decoder('utf-8', true);

        $r = new MemoryReader('n');
        $this->assertNull($d->decode($r));

        $r = new MemoryReader('f');
        $this->assertFalse($d->decode($r));

        $r = new MemoryReader('t');
        $this->assertTrue($d->decode($r));

        $r = new MemoryReader('i123e');
        $this->assertEquals(123, $d->decode($r));

        $r = new MemoryReader('i-456e');
        $this->assertEquals(-456, $d->decode($r));

        $r = new MemoryReader('i9223372036854775808e');
        $this->assertEquals('9223372036854775808', $d->decode($r));

        $r = new MemoryReader('i123');
        $this->assertThrows(
            'Bencodex\Codec\DecodingError',
            function () use ($d, $r) {
                $d->decode($r);
            }
        );

        $r = new MemoryReader('i123z');
        $this->assertThrows(
            'Bencodex\Codec\DecodingError',
            function () use ($d, $r) {
                $d->decode($r);
            }
        );

        $r = new MemoryReader('i12-3e');
        $this->assertThrows(
            'Bencodex\Codec\DecodingError',
            function () use ($d, $r) {
                $d->decode($r);
            }
        );

        $r = new MemoryReader('3:foo');
        $this->assertEquals('foo', $d->decode($r));

        $r = new MemoryReader("2:\xc3\x28");
        $this->assertEquals("\xc3\x28", $d->decode($r));

        $r = new MemoryReader('100:insufficient');
        $this->assertThrows(
            'Bencodex\Codec\DecodingError',
            function () use ($d, $r) {
                $d->decode($r);
            }
        );

        $r = new MemoryReader('u6:단팥');
        $this->assertEquals('단팥', $d->decode($r));

        $r = new MemoryReader('u6:단팥');
        $this->assertEquals("\xb4\xdc\xc6\xcf", $dEucKr->decode($r));

        $r = new MemoryReader('u6:단팥');
        $this->assertEquals("\xef\xbb\xbf단팥", $dBom->decode($r));

        $r = new MemoryReader("u9:\xef\xbb\xbf단팥");
        $this->assertEquals("\xef\xbb\xbf단팥", $d->decode($r));

        $r = new MemoryReader("u9:\xef\xbb\xbf단팥");
        $this->assertEquals("\xef\xbb\xbf단팥", $dBom->decode($r));

        $r = new MemoryReader('u100:insufficient length; 너무 짧다');
        $this->assertThrows(
            'Bencodex\Codec\DecodingError',
            function () use ($d, $r) {
                $d->decode($r);
            }
        );

        $r = new MemoryReader("u4:\xb4\xdc\xc6\xcf");
        $this->assertThrows(
            'Bencodex\Codec\DecodingError',
            function () use ($d, $r) {
                $d->decode($r);
            }
        );

        $r = new MemoryReader("u6:단팥");
        $this->assertThrows(
            'Bencodex\Codec\TextEncodingError',
            function () use ($dAscii, $r) {
                $dAscii->decode($r);
            }
        );

        $r = new MemoryReader("le");
        $this->assertEquals([], $d->decode($r));

        $r = new MemoryReader("lu6:단팥ntfli123eee");
        $this->assertEquals(["단팥", null, true, false, [123]], $d->decode($r));

        $r = new MemoryReader("de");
        $this->assertEquals(new \stdClass(), $d->decode($r));

        $o = new \stdClass();
        $o->{"단팥"} = [123];
        $o2 = new \stdClass();
        $o2->foo = 2;
        $o2->bar = 1;
        $o->{"\xc3\x28"} = $o2;
        $r = new MemoryReader("d2:\xc3\x28d3:bari1e3:fooi2eeu6:단팥li123eee");
        $this->assertEquals($o, $d->decode($r));

        $r = new MemoryReader('');
        $this->assertThrows(
            'Bencodex\Codec\DecodingError',
            function () use ($d, $r) {
                $d->decode($r);
            }
        );

        $r = new MemoryReader('z');
        $this->assertThrows(
            'Bencodex\Codec\DecodingError',
            function () use ($d, $r) {
                $d->decode($r);
            }
        );

        $r = new MemoryReader('22:invalid-trailing-bytestfn');
        $this->assertThrows(
            'Bencodex\Codec\DecodingError',
            function () use ($d, $r) {
                $d->decode($r);
            }
        );
    }
}
