<?php

namespace Arturu\Laying;
use PHPUnit\Runner\Exception;
use Symfony\Component\Yaml\Parser;


class Laying
{
    private $conf;
    private $layout;

    /**
     * Laying constructor.
     * @param $pathLayoutConf
     * @param $pathLayout
     */
    public function __construct($pathLayoutConf, $pathLayout)
    {
        if ( !file_exists($pathLayoutConf) || !is_readable($pathLayoutConf) ) {
            throw new Exception($pathLayoutConf . ' is not accessible.');
        }

        if ( !file_exists($pathLayout) || !is_readable($pathLayout) ) {
            throw new Exception($pathLayout . ' is not accessible.');
        }

        $yaml = new Parser();

        $this->conf = $yaml->parse(file_get_contents($pathLayoutConf));
        $this->layout = $yaml->parse(file_get_contents($pathLayout));
    }

    /**
     * @return string
     */
    public function renderLayout()
    {
        $output = $this->layout($this->layout);

        return trim($output);
    }

    /**
     * @param array $items
     * @return string
     */
    private function layout(array $items)
    {
        $output = "";

        foreach ($items as $key => $item){

            // set element type
            if (!isset($item['type']) || $item['type']==null ){
                $elementType = $this->conf['element']['defaultType'];
            }
            else {
                $elementType = $item['type'];
            }

            // open element
            $output .= '<'.$elementType;

            // setting attributes
            if ( isset($item['attributes']) ) {
                $output .= ' ' . Element::attributes($item['attributes']);
            }

            // if implicit
            if ( isset($item['implicit']) && $item['implicit'] ) {
                // out: <tag attr="value" ... />
                return $output . ' />';
            }
            // or not implicit
            else {
                $output .= '>';
            }

            if ( isset($item['items']) && is_array($item['items']) ){
                $output .= $this->layout($item['items']);
            }
            else {
                if ( isset($item['region']) && $item['region'] ) {
                    $output .= $item['region'];
                }
            }

            // close element
            $output .= '</'.$elementType.'>';
        }

        // out: <tag attr="value" ...>...</tag>
        return trim($output);
    }
}