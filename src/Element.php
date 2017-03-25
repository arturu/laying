<?php


namespace Arturu\Laying;

/**
 * Class Element
 * @package Arturu\Laying
 */
class Element
{

    /**
     * @param array $attributes
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