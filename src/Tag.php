<?php


namespace Arturu\Laying;


/**
 * Class Tag
 * @package Arturu\Laying
 */
class Tag
{
    /**
     * @param $values
     * @return string
     */
    public static function value($values) {
        $output = '';

        if ( is_array($values) ) {
            foreach ($values as $value) {
                $output .= ' ' . trim($value);
            }
        }
        else {
            $output .= trim($values);
        }

        // out: value value value
        return $output;
    }
}