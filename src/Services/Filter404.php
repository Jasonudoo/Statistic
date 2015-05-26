<?php
/**
 * Filte the 404 request
 * @date: April 13, 2015
 * @author: jason.weng
 */

namespace LogAnalytics\Filters;

use LogAnalytics\FilterInterface;

class Filter404 implements FilterInterface
{
    /*
     * Filte the http status, if the http status is 404, return TRUE
     * otherwise return FALSE
     * @see \LogAnalytics\FilterInterface::filter()
     */
    public function filter($value)
    {
        $value = $value - 0;
        if( 404 == $value ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function callback()
    {

    }
}