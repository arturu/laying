<?php
/**
 * This file is part of the arturu/Laying package.
 *
 * (c) Pietro Arturo Panetta <arturu@arturu.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arturu\Laying;
use PHPUnit\Framework\TestCase;

class LayingTest extends TestCase
{
    public function testLaying()
    {
        $path = "examples/basic-examples/page-standard.yml";
        $laying = new Laying($path);
        $this->assertTrue( is_string($laying->getLayout()) );
    }
}
