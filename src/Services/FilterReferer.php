<?php
/**
 * Filte the referer
 * @date: April 13, 2015
 * @author: jason.weng
 */

namespace LogAnalytics\Filters;

use LogAnalytics\FilterInterface;

class FilterReferer implements FilterInterface
{
    /*
     * Filte the http referer, if the http referer is empty, return TRUE
     * otherwise return FALSE
     * @see \LogAnalytics\FilterInterface::filter()
     */
    public function filter($value)
    {
        if( empty($value) || is_null($value) ) {
            return TRUE;
        }

        return FALSE;
    }

}