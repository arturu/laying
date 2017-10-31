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
    public function renderLayout()
    {
        $output = $this->layout($this->layout);

        return trim($output);
    }

    /**
     * This method and renderContent() are recursive
     * @param array $items
     * @return string
     */
    private function layout(array $items)
    {
        $output = "";

        foreach ($items as $key => $item) {

            $this->checkDuplicateID($key);

            // content
            $content = $this->renderContent($key,$item);

            // inner container
            $inner = $this->renderContainer($key, $content, 'inner', $item);

            // element container
            $element = $this->renderContainer($key,$inner,'element', $item);

            // wrapper container
            $output .= $this->renderContainer($key,$element,'wrapper', $item);
        }

        return trim($output);
    }

    /**
     * This method manage the content rendering of region
     * @param $key
     * @param $item
     * @return string
     */
    private function renderContent($key, $item)
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
     * This method rendering the container "element", "inner" and "wrapper"
     * @param $key
     * @param $content
     * @param $container
     * @param null $item
     * @return string
     */
    private function renderContainer($key, $content, $container, $item)
    {
        $output = "";

        // setting containerType
        $elementType = $this->elementType($item,$container);

        // setting item
        if ($container != 'element') {
            $item = array("attributes" => array( "class"=>$container ));
        }

        // open container
        if ( $this->useContainer($container) ) {
            $output .= '<'.$elementType;

            $output .= ' '.
                $this->attributes(
                    $key,
                    $item,
                    $this->conf['element'][$container.'Prefix'],
                    $this->conf['element'][$container.'Suffix']
                );

            // if implicit
            if ( isset($item['implicit']) && $item['implicit'] ){
                // out: <tag attr="value" ... />
                return $output . ' />';
            }
            else {
                $output .= '>';
            }
        }

        // writing content
        $output .= $content;

        // close container
        if ( $this->useContainer($container) ) {
            $output .= '</'.$elementType.'>';
        }

        // out: <tag attr="value" ...>...</tag>
        return trim($output);
    }

    /**
     * Rendering specific region
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

            $regionContent = $this->regionContent($region);

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
     * Rendering content of region
     * @param $region
     * @return string
     */
    private function regionContent($region)
    {
        if (is_array($region)){
            $output = $this->loadFile( $this->pathLayout . '/' . $region['file'], $region['parseMode'] );
        }
        else {
            $output = $region;
        }

        return trim($output);
    }

    /**
     * Rendering attributes
     * @param $key
     * @param $item
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    private function attributes($key, $item, $prefix='', $suffix='')
    {
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

        // rendering attributes
        $output = Element::attributes($item['attributes']);

        return trim($output);
    }

    /**
     * Setting element type
     * @param $item
     * @param $container
     * @return mixed
     * @internal param $containerType
     */
    private function elementType($item,$container)
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
        $this->checkFile($path);

        if ($parseMode=='yml') {
            $yaml = new Parser();
            return $yaml->parse(file_get_contents($path));
        }
        elseif ($parseMode=='raw') {
            return trim(file_get_contents($path));
        }
        else {
            throw new Exception('File '.$path.' not loaded: parse mode not specified');
        }
    }

    /**
     * @param $file
     */
    private function checkFile($file)
    {
        if ( !file_exists($file) || !is_readable($file) ) {
            throw new Exception($file . ' is not accessible.');
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