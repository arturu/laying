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
use Arturu\XMLTag\Element;
use PHPUnit\Runner\Exception;
use Symfony\Component\Yaml\Parser;


class Laying
{
    /**
     * Page configuration
     * @array
     */
    private $conf;

    /**
     * Layout structure
     * @array
     */
    private $layout;

    /**
     * Used keys in structure
     * @array
     */
    private $keyList;

    /**
     * Absolute path of layout file
     * @var string
     */
    private $pathLayout;

    /**
     * Laying constructor.
     * @param $pathLayoutFile
     */
    public function __construct($pathLayoutFile)
    {
        $pathExplode = explode('/',$pathLayoutFile);
        $fileName = end($pathExplode);
        $this->pathLayout = str_replace('/'.$fileName,'',$pathLayoutFile);

        $page = $this->loadFile($pathLayoutFile,'yml');

        // save conf
        $this->conf = $page['conf'];

        // clear conf e save page
        unset($page['conf']);
        $this->layout = $page;
    }

    /**
     * Return page rendered
     * @return string
     */
    public function getLayout()
    {
        $output = $this->renderLayout($this->layout);

        return trim($output);
    }

    /**
     * This method are recursive
     * @param array $items
     * @return string
     */
    private function renderLayout(array $items)
    {
        $output = "";

        foreach ($items as $key => $item) {

            $this->checkDuplicateID($key);

            $content = '';

            // render region before children element
            if ( (isset($item['regions']) && $item['regions']) && $this->conf['element']['renderRegionFirst'] ) {
                $content .= $this->renderRegions($key,$item);
            }

            // rendering children element
            if ( isset($item['items']) && is_array($item['items']) ) {
                $content .= $this->renderLayout($item['items']);
            }

            // render region after children element
            if ( (isset($item['regions']) && $item['regions']) && !$this->conf['element']['renderRegionFirst']) {
                $content .= $this->renderRegions($key,$item);
            }

            // inner container
            $inner = $this->renderElementContainer($key, $content, 'inner', $item);

            // element container
            $element = $this->renderElementContainer($key,$inner,'element', $item);

            // wrapper container
            $output .= $this->renderElementContainer($key,$element,'wrapper', $item);
        }

        return trim($output);
    }

    /**
     * Rendering specific region
     * @param $key
     * @param $item
     * @return string
     */
    private function renderRegions($key, $item)
    {
        $output = '';
        $countRegion = 0;

        foreach ($item['regions'] as $region) {

            ++$countRegion;

            $regionContent = $this->loadRegionContent($region);

            $element = array(
                'type'=> $this->conf['element']['defaultType'],
                'attributes'=> array(
                    'class'=>$key.$this->conf['element']['regionSuffix'].$countRegion.' '.$this->conf['element']['regionClass'],
                ),
                'content' => $regionContent,
            );

            $output .= Element::render($element);

            if ($this->conf['debugBlock']['active']){
                $debugElement = array(
                    'type'=> $this->conf['element']['defaultType'],
                    'attributes'=> array(
                        'class'=>$key.$this->conf['element']['regionSuffix'].$countRegion.$this->conf['debugBlock']['class'],
                    ),
                    'content' => "Debug Block ".$key.$this->conf['element']['regionSuffix'].$countRegion,
                );

                $output .= Element::render($debugElement);
            }
        }

        return trim($output);
    }

    /**
     * This method rendering the container "element", "inner" and "wrapper"
     * @param $key
     * @param $content
     * @param $container
     * @param $item
     * @return string
     */
    private function renderElementContainer($key, $content, $container, $item)
    {
        $output = "";

        if ( $this->useContainer($container) ) {
            $element = array(
                "type" => $this->setElementType($container,$item),
                "attributes" => $this->setAttributes($key,$container,$item),
                "implicit" => isset($item['implicit'])?$item['implicit']:false,
                "content" => $content
            );

            $output .= Element::render($element);
        }
        else {
            $output .= $content;
        }

        return trim($output);
    }

    /**
     * Load content of region, possible restart recursion
     * @param $region
     * @return string
     */
    private function loadRegionContent($region)
    {
        if ( is_array($region) && $region['parseMode']=='raw' ){
            $output = $this->loadFile( $this->pathLayout . '/' . $region['file'], 'raw' );
        }
        // restart recursion
        elseif ( is_array($region) && $region['parseMode']=='yml' ){
            $items = $this->loadFile( $this->pathLayout . '/' . $region['file'], 'yml' );
            $output = $this->renderLayout( $items );
        }
        else {
            $output = $region;
        }

        return trim($output);
    }

    /**
     * Setting attributes
     * @param $key
     * @param $item
     * @param $container
     * @return array
     */
    private function setAttributes($key, $container, $item)
    {
        $prefix = $this->conf['element'][$container.'Prefix'];
        $suffix = $this->conf['element'][$container.'Suffix'];

        // setting item for container != element
        if ($container != 'element') {
            $item = array("attributes" => array( "class"=>$container ));
        }

        // idAuto
        if ( isset($this->conf['element']['idAuto']) && !isset($item['attributes']['id']) ) {
            $item['attributes']['id'] = $prefix.$key.$suffix;
        }

        // id off for this element
        if ( isset($item['attributes']['id']) && $item['attributes']['id']==null ){
            unset($item['attributes']['id']);
        }

        // classAuto
        if ( $this->conf['element']['classAuto'] ) {
            if ( isset($item['attributes']['class']) ) {
                $item['attributes']['class'] .= ' '.$prefix.$key.$suffix;
            }
            else {
                $item['attributes']['class'] = $prefix.$key.$suffix;
            }
        }

        // class off for this element
        if ( isset($item['attributes']['class']) && $item['attributes']['class']==null ){
            unset($item['attributes']['class']);
        }

        return $item['attributes'];
    }

    /**
     * Setting element type
     * @param $item
     * @param $container
     * @return mixed
     * @internal param $containerType
     */
    private function setElementType($container,$item)
    {
        // if no type
        if ( isset($item['type']) && $item['type']==null ){
            return '';
        }
        // setting type if no wrapper
        elseif ( $container == 'element' && isset($item['type']) && $this->conf['element']['wrapper']==false ) {
            return $item['type'];
        }
        // setting type to wrapper
        elseif ( $container == 'wrapper' && isset($item['type']) && $this->conf['element']['wrapper'] ) {
            return $item['type'];
        }
        else {
            return $this->conf['element']['defaultType'];
        }
    }

    /**
     * Find duplicate id
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
     * @param $path
     * @param string $parseMode
     * @return mixed|string
     */
    private function loadFile($path, $parseMode='raw')
    {
        if ( !file_exists($path) || !is_readable($path) ) {
            throw new Exception($path . ' is not accessible.');
        }
        elseif ( $parseMode=='yml' ) {
            $parser = new Parser();
            return $parser->parse(file_get_contents($path));
        }
        elseif ( $parseMode=='raw' ) {
            return trim(file_get_contents($path));
        }
        else {
            throw new Exception('File '.$path.' not loaded: parse mode not specified');
        }
    }

    /**
     * @param $containerType - inner or wrapper
     * @return bool
     */
    private function useContainer($containerType)
    {
        if ( isset($this->conf['element'][$containerType]) && $this->conf['element'][$containerType] ){
            return true;
        }
        else {
            return false;
        }
    }
}