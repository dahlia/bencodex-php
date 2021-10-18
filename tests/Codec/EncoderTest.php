<?php

namespace Bencodex\Tests\Codec;

use Bencodex\Codec\Encoder;
use Bencodex\Tests\TestUtils;
use Bencodex\Writers\MemoryWriter;
use PHPUnit\Framework\TestCase;

class EncoderTest extends TestCase
{
    use TestUtils;

    public function testConstruct()
    {
        $e = new Encoder();
        $this->assertEquals('utf-8', $e->getTextEncoding());
        $this->assertEquals('utf-8', $e->getKeyEncoding());

        $e = new Encoder('ascii', 'euc-kr');
        $this->assertEquals('ascii', $e->getTextEncoding());
        $this->assertEquals('euc-kr', $e->getKeyEncoding());

        $e = new Encoder(null, null);
        $this->assertNull($e->getTextEncoding());
        $this->assertNull($e->getKeyEncoding());

        $this->assertThrows('Bencodex\Codec\TextEncodingError', function () {
            new Encoder('utf-1');
        });
        $this->assertThrows('TypeError', function () {
            new Encoder(123);
        });
        $this->assertThrows('Bencodex\Codec\TextEncodingError', function () {
            new Encoder(null, 'euc-zz');
        });
        $this->assertThrows('TypeError', function () {
            new Encoder(null, 123);
        });
    }

    public function testGetTextEncoding()
    {
        $e = new Encoder();
        $this->assertEquals('utf-8', $e->getTextEncoding());
        $e = new Encoder('euc-kr');
        $this->assertEquals('euc-kr', $e->getTextEncoding());
        $e = new Encoder(null);
        $this->assertNull($e->getTextEncoding());
    }

    public function testSetTextEncoding()
    {
        $e = new Encoder();
        $e->setTextEncoding('euc-kr');
        $this->assertEquals('euc-kr', $e->getTextEncoding());
        $e->setTextEncoding(null);
        $this->assertNull($e->getTextEncoding());
        $this->assertThrows('TypeError', function () use ($e) {
            $e->setTextEncoding(123);
        });
        $this->assertThrows(
            'Bencodex\Codec\TextEncodingError',
            function () use ($e) {
                $e->setTextEncoding('utf-1');
            }
        );
    }

    public function testGetKeyEncoding()
    {
        $e = new Encoder();
        $this->assertEquals('utf-8', $e->getKeyEncoding());
        $e = new Encoder(null, 'euc-kr');
        $this->assertEquals('euc-kr', $e->getKeyEncoding());
        $e = new Encoder(null, null);
        $this->assertNull($e->getKeyEncoding());
    }

    public function testSetKeyEncoding()
    {
        $e = new Encoder();
        $e->setKeyEncoding('euc-kr');
        $this->assertEquals('euc-kr', $e->getKeyEncoding());
        $e->setKeyEncoding(null);
        $this->assertNull($e->getKeyEncoding());
        $this->assertThrows('TypeError', function () use ($e) {
            $e->setKeyEncoding(123);
        });
        $this->assertThrows(
            'Bencodex\Codec\TextEncodingError',
            function () use ($e) {
                $e->setKeyEncoding('utf-1');
            }
        );
    }

    public function testEncode()
    {
        $e = new Encoder();
        $eEucKr = new Encoder('euc-kr', 'euc-kr');
        $eBin = new Encoder(null, null);

        $w = new MemoryWriter();
        $e->encode($w, null);
        $this->assertEquals('n', $w->getBuffer());

        $w = new MemoryWriter();
        $e->encode($w, true);
        $this->assertEquals('t', $w->getBuffer());

        $w = new MemoryWriter();
        $e->encode($w, false);
        $this->assertEquals('f', $w->getBuffer());

        $w = new MemoryWriter();
        $e->encode($w, 3);
        $this->assertEquals('i3e', $w->getBuffer());

        $w = new MemoryWriter();
        $e->encode($w, '단팥');
        $this->assertEquals('u6:단팥', $w->getBuffer());

        $w = new MemoryWriter();
        $eEucKr->encode($w, "\xb4\xdc\xc6\xcf");
        $this->assertEquals('u6:단팥', $w->getBuffer());

        $w = new MemoryWriter();
        $eBin->encode($w, '단팥');
        $this->assertEquals('6:단팥', $w->getBuffer());

        foreach ([$e, $eEucKr, $eBin] as $encoder) {
            $w = new MemoryWriter();
            $encoder->encode($w, "\xc3\x28");
            $this->assertEquals("2:\xc3\x28", $w->getBuffer());
        }

        $w = new MemoryWriter();
        $e->encode($w, [true, null, '단팥', [null], ['foo' => 'bar']]);
        $this->assertEquals('ltnu6:단팥lnedu3:foou3:baree', $w->getBuffer());

        $w = new MemoryWriter();
        $eBin->encode($w, [true, null, '단팥', [true], ['foo' => 'bar']]);
        $this->assertEquals('ltn6:단팥lted3:foo3:baree', $w->getBuffer());

        $w = new MemoryWriter();
        $e->encode($w, ['foo' => 'bar', 'baz' => [1, 2, 3], 'qux' => true]);
        $this->assertEquals(
            'du3:bazli1ei2ei3eeu3:foou3:baru3:quxte',
            $w->getBuffer()
        );

        $o = new \stdClass();
        $o->foo = 'bar';
        $o->baz = [1, 2, 3];
        $o->qux = true;
        $w = new MemoryWriter();
        $e->encode($w, $o);
        $this->assertEquals(
            'du3:bazli1ei2ei3eeu3:foou3:baru3:quxte',
            $w->getBuffer()
        );

        $w = new MemoryWriter();
        $res = tmpfile();
        $this->assertThrows('TypeError', function () use ($e, $w, $res) {
            $e->encode($w, $res);
        });
        $this->assertEquals(0, $w->getLength());
    }

    public function testEncodeDictionary()
    {
        $e = new Encoder();
        $eEucKr = new Encoder('euc-kr', 'euc-kr');
        $eBin = new Encoder(null, null);

        foreach ([$e, $eEucKr, $eBin] as $encoder) {
            $w = new MemoryWriter();
            $encoder->encodeDictionary($w, []);
            $this->assertEquals('de', $w->getBuffer());

            $w = new MemoryWriter();
            $encoder->encodeDictionary($w, new \stdClass());
            $this->assertEquals('de', $w->getBuffer());
        }

        $dictArray = ['foo' => 1, 'bar' => 2];
        $dictObject = new \stdClass();
        $dictObject->foo = 1;
        $dictObject->bar = 2;
        foreach ([$e, $eEucKr] as $encoder) {
            $w = new MemoryWriter();
            $encoder->encodeDictionary($w, $dictArray);
            $this->assertEquals('du3:bari2eu3:fooi1ee', $w->getBuffer());

            $w = new MemoryWriter();
            $encoder->encodeDictionary($w, $dictObject);
            $this->assertEquals('du3:bari2eu3:fooi1ee', $w->getBuffer());
        }

        $w = new MemoryWriter();
        $eBin->encodeDictionary($w, $dictArray);
        $this->assertEquals('d3:bari2e3:fooi1ee', $w->getBuffer());

        $w = new MemoryWriter();
        $eBin->encodeDictionary($w, $dictObject);
        $this->assertEquals('d3:bari2e3:fooi1ee', $w->getBuffer());

        $binKeyedArray = ["\xc3\x28" => true];
        $binKeyedObject = new \stdClass();
        $binKeyedObject->{"\xc3\x28"} = true;
        foreach ([$e, $eEucKr, $eBin] as $encoder) {
            $w = new MemoryWriter();
            $encoder->encodeDictionary($w, $binKeyedArray);
            $this->assertEquals("d2:\xc3\x28te", $w->getBuffer());

            $w = new MemoryWriter();
            $encoder->encodeDictionary($w, $binKeyedObject);
            $this->assertEquals("d2:\xc3\x28te", $w->getBuffer());
        }

        $w = new MemoryWriter();
        $eEucKr->encodeDictionary(
            $w,
            ["\xb4\xdc\xc6\xcf" => 'second', "\xc3\x28" => 'first']
        );
        $this->assertEquals(
            "d2:\xc3\x28u5:firstu6:단팥u6:seconde",
            $w->getBuffer()
        );

        $eucKrObject = new \stdClass();
        $eucKrObject->{"\xb4\xdc\xc6\xcf"} = 'second';
        $eucKrObject->{"\xc3\x28"} = 'first';
        $w = new MemoryWriter();
        $eEucKr->encodeDictionary($w, $eucKrObject);
        $this->assertEquals(
            "d2:\xc3\x28u5:firstu6:단팥u6:seconde",
            $w->getBuffer()
        );
    }

    public function testEncodeList()
    {
        $e = new Encoder();

        $w = new MemoryWriter();
        $e->encodeList($w, []);
        $this->assertEquals('le', $w->getBuffer());

        $w = new MemoryWriter();
        $e->encodeList(
            $w,
            [null, true, false, 123, 456.78, 'foo', '甲乙丙', "\xc3\x28", [1, 2]]
        );
        $this->assertEquals(
            "lntfi123ei456eu3:foou9:甲乙丙2:\xc3\x28li1ei2eee",
            $w->getBuffer()
        );

        $w = new MemoryWriter();
        $e->encodeList($w, [[], ['foo' => 1, 'bar' => 2]]);
        $this->assertEquals('ldedu3:bari2eu3:fooi1eee', $w->getBuffer());

        $w = new MemoryWriter();
        $e->encodeList($w, [['foo' => 1], []]);
        $this->assertEquals('ldu3:fooi1eedee', $w->getBuffer());

        $w = new MemoryWriter();
        $e->encodeList($w, [[], ['foo' => 1, 'bar' => 2], [true, false]]);
        $this->assertEquals('lledu3:bari2eu3:fooi1eeltfee', $w->getBuffer());

        $w = new MemoryWriter();
        $e->encodeList($w, [['foo' => 1], [], [true, false]]);
        $this->assertEquals('ldu3:fooi1eeleltfee', $w->getBuffer());
    }

    public function testEncodeNull()
    {
        $e = new Encoder();
        $w = new MemoryWriter();
        $e->encodeNull($w);
        $this->assertEquals('n', $w->getBuffer());
    }

    public function testEncodeBoolean()
    {
        $e = new Encoder();

        $w = new MemoryWriter();
        $e->encodeBoolean($w, true);
        $this->assertEquals('t', $w->getBuffer());

        $w = new MemoryWriter();
        $e->encodeBoolean($w, false);
        $this->assertEquals('f', $w->getBuffer());
    }

    public function testEncodeInteger()
    {
        $e = new Encoder();
        $w = new MemoryWriter();
        $e->encodeInteger($w, 3);
        $this->assertEquals('i3e', $w->getBuffer());
    }

    public function testEncodeString()
    {
        $e = new Encoder();
        $eBom = new Encoder('utf-8', 'utf-8', true);

        $w = new MemoryWriter();
        $e->encodeString($w, 'utf-8', '단팥');
        $this->assertEquals('u6:단팥', $w->getBuffer());

        $w = new MemoryWriter();
        $e->encodeString($w, 'utf-8', "\xc3\x28");
        $this->assertEquals("2:\xc3\x28", $w->getBuffer());

        $w = new MemoryWriter();
        $eBom->encodeString($w, 'utf-8', '단팥');
        $this->assertEquals('6:단팥', $w->getBuffer());

        $w = new MemoryWriter();
        $eBom->encodeString($w, 'utf-8', "\xef\xbb\xbf단팥");
        $this->assertEquals('u6:단팥', $w->getBuffer());

        $w = new MemoryWriter();
        $eBom->encodeString($w, 'utf-8', "\xc3\x28");
        $this->assertEquals("2:\xc3\x28", $w->getBuffer());
    }

    public function testEncodeText()
    {
        $e = new Encoder();
        $w = new MemoryWriter();
        $e->encodeText($w, '단팥');
        $this->assertEquals('u6:단팥', $w->getBuffer());
    }

    public function testEncodeBinary()
    {
        $e = new Encoder();
        $w = new MemoryWriter();
        $e->encodeBinary($w, 'spam');
        $this->assertEquals('4:spam', $w->getBuffer());
    }
}
