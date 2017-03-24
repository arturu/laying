<?php

namespace Arturu\Laying;

class LayingTest extends \PHPUnit_Framework_TestCase
{
    public function testLaying()
    {
        $laying = new Laying();
        $this->assertTrue($laying->laying());
    }
}
