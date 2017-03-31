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
     * @param $pathLayout
     */
    public function __construct($pathLayout)
    {

        if ( !file_exists($pathLayout) || !is_readable($pathLayout) ) {
            throw new Exception($pathLayout . ' is not accessible.');
        }

        $yaml = new Parser();
        $layout = $yaml->parse(file_get_contents($pathLayout));

        // save conf
        $this->conf = $layout['conf'];

        // clear conf e save layout
        unset($layout['conf']);
        $this->layout = $layout;
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

            $output .= ' ' . $this->attributes($key,$item);

            // if implicit
            if ( isset($item['implicit']) && $item['implicit'] ) {
                // out: <tag attr="value" ... />
                return $output . ' />';
            }
            else {
                $output .= '>';
            }

            $output .= $this->elementContent($key,$item);

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
    private function regions($key, $item)
    {
        $output = '';
        $countRegion = 0;

        foreach ($item['regions'] as $region) {

            ++$countRegion;

            $element = array(
                'type'=> 'div',
                'attributes'=> array(
                    'class'=>$key.'-region-'.$countRegion.' region',
                ),
                'content' => $region,
            );

            $output .= Element::element($element);
        }

        return trim($output);
    }

    /**
     * @param $key
     * @param $item
     * @return string
     */
    private function elementContent($key, $item)
    {
        $output = '';

        // render region before children element
        if ( (isset($item['regions']) && $item['regions']) && $this->conf['element']['renderRegionFirst'] ) {
            $output .= $this->regions($key,$item);
        }

        // rendering children element
        if ( isset($item['items']) && is_array($item['items']) ) {
            $output .= $this->layout($item['items']);
        }

        // render region after children element
        if ( (isset($item['regions']) && $item['regions']) && !$this->conf['element']['renderRegionFirst']) {
            $output .= $this->regions($key,$item);
        }

        return trim($output);
    }

    /**
     * @param $key
     * @param $item
     * @return string
     */
    private function attributes($key, $item)
    {

        // idAuto
        if ( isset($this->conf['element']['idAuto']) && !isset($item['attributes']['id']) ) {
            $item['attributes']['id'] = $key;
        }

        // classAuto
        if ( $this->conf['element']['classAuto'] ) {
            if ( isset($item['attributes']['class']) ) {
                $item['attributes']['class'] .= ' ' . $key .'-'.$this->conf['element']['classAutoPrefix'];
            }
            else {
                $item['attributes']['class'] = $key .'-'.$this->conf['element']['classAutoPrefix'];
            }
        }

        // rendering attributes
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
            throw new Exception('Duplicate elementID in YAML configuration: "' . $key .'".');
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