<?php

namespace Arturu\Laying;
use PHPUnit\Runner\Exception;
use Symfony\Component\Yaml\Parser;


class Laying
{
    private $conf;
    private $layout;
    private $keyList;

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

        foreach ($items as $key => $item) {

            $this->checkDuplicateID($key);

            $elementType = $this->elementType($item);

            // open element
            $output .= '<'.$elementType;

            // setting attributes
            $output .= ' ' . $this->attributes($key,$item);

            // if implicit
            if ( isset($item['implicit']) && $item['implicit'] ) {
                // out: <tag attr="value" ... />
                return $output . ' />';
            }
            else {
                $output .= '>';
            }

            // recursive function
            if ( isset($item['items']) && is_array($item['items']) ) {
                // children element
                $output .= $this->layout($item['items']);
            }
            else {
                if ( isset($item['region']) && $item['region'] ) {
                    // render region
                    $output .= $item['region'];
                }
            }

            // close element
            $output .= '</'.$elementType.'>';
        }

        // out: <tag attr="value" ...>...</tag>
        return trim($output);
    }


    /**
     * @param $key
     * @param $item
     * @return string
     */
    private function attributes($key, $item)
    {

        if ( isset($this->conf['element']['idAuto']) && !isset($item['attributes']['id']) ) {
            $item['attributes']['id'] = $key;
        }

        if ( $this->conf['element']['classAuto'] ) {
            if ( isset($item['attributes']['class']) ) {
                $item['attributes']['class'] .= ' ' . $key .'-'.$this->conf['element']['classAutoPrefix'];
            }
            else {
                $item['attributes']['class'] = $key .'-'.$this->conf['element']['classAutoPrefix'];
            }
        }

        $output = Element::attributes($item['attributes']);

        return trim($output);
    }


    /**
     * @param $key
     */
    private function checkDuplicateID($key)
    {
        // search duplicate elementID
        if ( !isset($this->keyList[$key]) ) {
            $this->keyList[$key] = true;
        }
        else {
            throw new Exception($key.' duplicate elementID');
        }
    }


    /**
     * @param $item
     * @return mixed
     */
    private function elementType($item)
    {
        if (!isset($item['type']) || $item['type']==null ){
            return $this->conf['element']['defaultType'];
        }
        else {
            return $item['type'];
        }
    }
}