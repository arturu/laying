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
use PHPUnit\Runner\Exception;
use PHPUnit\Framework\TestCase;

class LayingTest extends TestCase
{
    public function testLaying()
    {
        $files = array(
            'basic/basic',
            'basic/basic-inject',
            'basic/page-minimal',
            'basic/page-standard',
            'basic/page-with-wrapper-inner-debugBlock',
            'modular/page-minimal',
            'modular/page-minimal-multiRecursive'
        );

        foreach ($files as $key) {
            $pathFileTest = "examples/" . $key . ".yml";
            $laying = new Laying($pathFileTest);

            $actualOutput = $laying->getLayout();
            $this->assertTrue(is_string($actualOutput));

            $expectedOutput = $this->loadFile('tests/output/' . $key . '.html');
            $this->assertEquals($expectedOutput, $actualOutput);
        }
    }

    /**
     * @param $path
     * @return mixed|string
     */
    private function loadFile($path)
    {
        if ( !file_exists($path) || !is_readable($path) ) {
            throw new Exception($path . ' is not accessible.');
        }
        else {
            return trim(file_get_contents($path));
        }
    }
}
