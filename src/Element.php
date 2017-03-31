<?php


namespace Arturu\Laying;

/**
 * Class Element
 * @package Arturu\Laying
 */
class Element
{


    /**
     * @param array $element
     *  array(
     *    'type'=> 'tag',
     *    'attributes'=> array, // see self::attributes
     *    'implicit'=> bool, // true for implicit
     *    'content' => string, // tag content
     *  )
     * @return string
     */
    public static function element(array $element)
    {
        $elementType = $element['type'];

        // open element
        $output = '<'.$elementType;

        // setting attributes
        if ( isset($element['attributes']) ) {
            $output .= ' ' . self::attributes($element['attributes']);
        }

        // if implicit
        if ( isset($element['implicit']) && $element['implicit'] ) {
            // out: <tag attr="value" ... />
            return $output . ' />';
        }
        // or not implicit
        else {
            $output .= '>';
        }

        // put the content into element
        if ( isset($element['content']) && $element['content'] ) {
            $output .= $element['content'];
        }

        // close element
        $output .= '</'.$elementType.'>';

        // out: <tag attr="value" ...>...</tag>
        return trim($output);
    }

    /**
     * @param array $attributes
     *   array(
     *     'key'=>'value',
     *     'key'=> array("value","value","value",...),
     *     ...
     *   )
     * @return string
     */
    public static function attributes(array $attributes)
    {
        $output = '';

        foreach ($attributes as $key => $values){
            $output .= ' ' . self::attribute($key, $values);
        }

        // out: attr1="value" attr2="value value" attr3="val val" etc...
        return trim($output);
    }


    /**
     * @param string $key
     * @param $values
     * @return string
     */
    public static function attribute(string $key, $values)
    {
        $output = '';

        if (is_array($values)) {
            //out: key="value value value value"
            $output .= $key . '="' . self::values($values) . '"';
        }
        else {
            //out: key="value"
            $output .= $key . '="' . self::value($values) . '"';
        }

        return trim($output);
    }

    /**
     * @param array $values
     * @return string
     */
    public static function values(array $values)
    {
        $output = '';

        foreach ($values as $value) {
            $output .= ' ' . self::value($value);
        }

        // out: value value value
        return trim($output);
    }

    /**
     * @param string $value
     * @return string
     */
    public static function value(string $value)
    {
        //out: value
        return trim($value);
    }
}