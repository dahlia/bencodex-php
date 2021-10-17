<?php

namespace Bencodex\Test\Codec;

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    public function testValidateTextEncoding()
    {
        function validate($textEncoding)
        {
            return \Bencodex\Codec\validateTextEncoding($textEncoding);
        }

        $this->assertTrue(validate('ascii'));
        $this->assertTrue(validate('utf-8'));
        $this->assertTrue(validate('euc-kr'));
        $this->assertFalse(validate('utf-1'));
        $this->assertFalse(validate('euc-zz'));
    }
}
