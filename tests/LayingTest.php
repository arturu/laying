<?php

namespace Arturu\Laying;
use PHPUnit\Framework\TestCase;

class LayingTest extends TestCase
{
    public function testLaying()
    {
        $laying = new Laying();
        $this->assertTrue($laying->laying());
    }
}
