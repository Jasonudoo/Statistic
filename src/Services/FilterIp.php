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

class FilterIp implements FilterInterface
{
    private $data;

    public function __construct($options)
    {

    }

    /**
     *
     * @param unknown $ip
     * @param unknown $tm
     */
    public function setIpAddress($ip, $tm)
    {
        $ipValue = 0;
        if( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ) {
            $ipValue = sprintf('%u', ip2long($ip));
        }
        $this->data[$ipValue][] = $tm;
    }

    /**
     * Filte the ip address
     * @see \LogAnalytics\FilterInterface::filter()
     */
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

        if( isset($value[0]) && isset($value[1]) ) {
            $ipValue1 = sprintf('%u', ip2long($value[0]) );
            $ipValue2 = sprintf('%u', ip2long($value[1]) );

            if( $ipValue1 === $ipValue2 ) {
                return TRUE;
            }
        }

        return FALSE;
    }

}