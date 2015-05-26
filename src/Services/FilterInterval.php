<?php
/**
 * Filte the ip address which repeated in a given time escaped.
 * @date: April 13, 2015
 * @author: jason.weng
 */

namespace LogAnalytics\Filters;

use LogAnalytics\FilterInterface;
use LogAnalytics\Exception\InvalidArgumentException;
use LogAnalytics\Helpers\ErrorCode;

class FilterInterval implements FilterInterface
{
    /**
     * The interval time escape
     * @var integer
     */
    private $interval;

    public function __construct()
    {

    }

    public function setInterval($escape)
    {
        $this->interval = $escape;
    }

    public function filter($value)
    {
        if( FALSE === is_array($value) ) {
            $code = ErrorCode::ERROR_FILTER_IP_PARAM_EMPTY;
            $message = ErrorCode::getMessage($code);
            throw new InvalidArgumentException($message, $code);
        }

        if( sizeof($value) !== 2 ) {
            $code = ErrorCode::ERROR_FILTER_IP_PARAM_MISSING;
            $message = ErrorCode::getMessage($code);
            throw new InvalidArgumentException($message, $code);
        }

        $tm1 = isset($value[0]) ? $value[0] : 0;
        $tm2 = isset($value[1]) ? $value[1] : time();
        if( ($tm2 - $tm1) < $this->interval ) {
            return TRUE;
        }

        return FALSE;
    }
}