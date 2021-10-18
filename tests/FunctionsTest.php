<?php

namespace Bencodex\Tests;

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    public function testEncode()
    {
        $value = ['utf-8' => '단팥', 'euc-kr' => "\xb4\xdc\xc6\xcf"];
        $this->assertEquals(
            "du6:euc-kr4:\xb4\xdc\xc6\xcfu5:utf-8u6:단팥e",
            \Bencodex\encode($value)
        );
        $this->assertEquals(
            "du6:euc-kru6:단팥u5:utf-86:단팥e",
            \Bencodex\encode($value, 'euc-kr')
        );
        $this->assertEquals(
            "d6:euc-kr4:\xb4\xdc\xc6\xcf5:utf-86:단팥e",
            \Bencodex\encode($value, null, null)
        );
    }
}
