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
     * Absolute path of file
     * @var string
     */
    private $pathLayoutFile;

    /**
     * Absolute path of folder file
     * @var string
     */
    private $pathLayout;

    /**
     * File name
     * @var string
     */
    private $fileName;

    /**
     * Laying constructor.
     * @param $pathLayoutFile
     */
    public function __construct($pathLayoutFile)
    {
        // load default settings
        $defaultSettings = $this->loadFile(__DIR__ . '/conf/settings.yml','yml');

        // load layout page
        $this->pathLayoutFile = $pathLayoutFile;
        $pathExplode = explode('/',$pathLayoutFile);
        $fileName = end($pathExplode);

        $this->fileName = $fileName;
        $this->pathLayout = str_replace('/'.$fileName,'',$pathLayoutFile);

        $layout = $this->loadFile($pathLayoutFile,'yml');

        // save end clear conf from layout page
        if ( isset($layout['conf']) ) {
            $this->conf = array_replace_recursive($defaultSettings['conf'], $layout['conf']);
            unset( $layout['conf'] );
        }
        else {
            $this->conf = $defaultSettings['conf'];
        }

        // save page
        $this->layout = $layout;
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
     * @param $keyList
     */
    public function setKeyList($keyList)
    {
        $this->keyList = $keyList;
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
            if ( (isset($item['regionsContent']) && $item['regionsContent']) && $this->conf['renderRegionFirst'] ) {
                $content .= $this->renderRegions($key,$item);
            }

            // rendering children element
            if ( isset($item['items']) && is_array($item['items']) ) {
                $content .= $this->renderLayout($item['items']);
            }

            // render region after children element
            if ( (isset($item['regionsContent']) && $item['regionsContent']) && !$this->conf['renderRegionFirst']) {
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

        foreach ($item['regionsContent'] as $region) {

            ++$countRegion;

            $content = $this->loadRegionContent($region);
            $regionKey = $key.'-'.$this->conf['regionClass'].'-'.$countRegion;

            $output .= $this->renderElementContainer($regionKey,$content,'region', $item);

            $output .= $this->debugBlock($key,$item,$countRegion);
        }

        return trim($output);
    }

    /**
     * This method rendering the container "wrapper", "element", "inner" and "region"
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
                "implicit" => isset($item['implicit']) ? $item['implicit'] : false,
                "injectTag" => $this->setInjectTag($container,$item),
                "attributes" => $this->setAttributes($key,$container,$item),
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
            // new laying object
            $laying = new Laying($this->pathLayout . '/' . $region['file']);

            // pass keyList
            $laying->setKeyList( $this->keyList );

            $output = $laying->getLayout();
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
        $prefix = $this->conf[$container.'Prefix'];
        $class = $this->conf[$container.'Class'];
        $suffix = $this->conf[$container.'Suffix'];

        // reset attributes
        if (!$this->useItemToAttribute($container)) {
            $item = array( "attributes" => array("class"=>'') );
        }

        // idAuto
        if ( isset($this->conf['idAuto']) && !isset($item['attributes']['id']) ) {
            $item['attributes']['id'] = $prefix.$key.$suffix;
        }

        // id off for this element
        if ( isset($item['attributes']['id']) && $item['attributes']['id']==null ){
            unset($item['attributes']['id']);
        }

        // classAuto
        if ( $this->conf['classAuto'] ) {
            if ( isset($item['attributes']['class']) ) {
                $item['attributes']['class'] .= ' '.$prefix.$key.$suffix. ' '.$class;
            }
            else {
                $item['attributes']['class'] = $prefix.$key.$suffix . ' '.$class;
            }
        }

        // class off for this element
        if ( isset($item['attributes']['class']) && $item['attributes']['class']==null ){
            unset($item['attributes']['class']);
        }

        // clear double space e trim
        $item['attributes']['class'] = str_replace('  ', ' ', $item['attributes']['class']);
        $item['attributes']['class'] = trim ($item['attributes']['class']);
        $item['attributes']['id'] = trim ($item['attributes']['id']);

        return $item['attributes'];
    }

    /**
     * @param $container
     * @param $item
     * @return bool|mixed
     */
    private function setInjectTag($container, $item)
    {
        // reset injectTag
        if (!$this->useItemToAttribute($container)) {
            $item = array( "injectTag" => false );
        }

        if ( isset($item['injectTag']) && $item['injectTag']!==false ){
            return $item['injectTag'];
        }
        else {
            return false;
        }
    }

    /**
     * @param $containerType - wrapper, inner, element, region, debugBlock
     * @return bool
     */
    private function useContainer($containerType)
    {
        if ( isset($this->conf[$containerType]) && $this->conf[$containerType] ) {
            return true;
        }
        else {
            return false;
        }
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
        // if type is setting
        if ( isset($item['type']) ){

            if ( $item['type']==null ) {
                return '';
            }
            // Set $item['type'] to wrapper
            elseif ( $this->conf['wrapper'] && $container == 'wrapper' ) {
                return $item['type'];
            }
            // Set $item['type'] to element
            elseif ( $this->conf['element'] && $container == 'element' && !$this->conf['wrapper'] ) {
                return $item['type'];
            }
            // Set $item['type'] to inner
            elseif ( $this->conf['inner'] && $container == 'inner' && !$this->conf['wrapper'] && !$this->conf['element'] ) {
                return $item['type'];
            }
            // Set $item['type'] to region
            elseif ( $this->conf['region'] && $container == 'region' && !$this->conf['wrapper'] && !$this->conf['element'] && !$this->conf['inner']) {
                return $item['type'];
            }
            else {
                return $this->conf['defaultType'];
            }
        }
        else {
            return $this->conf['defaultType'];
        }
    }

    /**
     * @param $container
     * @return bool
     */
    private function useItemToAttribute($container){
        if ( $this->conf['element'] && $container=='element' ){
            return true;
        }
        elseif ( !$this->conf['element'] && !$this->conf['region'] && !$this->conf['inner'] ) {
            if ( $this->conf['wrapper'] && $container=='wrapper' ) {
                return true;
            }
            else {
                return false;
            }
        }
        elseif ( !$this->conf['element'] && !$this->conf['region'] ) {
            if ( $this->conf['inner'] && $container=='inner' ) {
                return true;
            }
            else {
                return false;
            }
        }
        elseif ( !$this->conf['element'] ) {
            if ( $this->conf['region'] && $container=='region' ) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    /**
     * @param $key
     * @param $item
     * @param $countRegion
     * @return string
     */
    private function debugBlock($key, $item, $countRegion)
    {
        $output = "";

        if ($this->conf['debugBlock']){

            $debugKey = $key.'-'.$this->conf['regionClass'].'-'.$countRegion;
            $content = "Debug Block ".$debugKey;

            $output .= $this->renderElementContainer($debugKey,$content,'debugBlock',$item);
        }

        return trim($output);
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
            throw new Exception('Duplicate elementID ("'.$key .'"") in '.$this->fileName.'.');
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
}