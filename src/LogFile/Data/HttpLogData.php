<?php
/**
 * Nginx Http Log Data Schema
 *
 * @since: April 13, 2015
 * @author: Jason.Weng
 */

namespace LogAnalytics\Files\Data;

use LogAnalytics\Helpers\Config;

class HttpLogData
{
    /**
     * IP Address
     * @var string
     */
    private $ipaddress;

    /**
     * Date time
     * @var string
     */
    private $datetime;

    /**
     * Default DateTime Format
     * @var unknown
     */
    private $defaultDateFormat;

    /**
     * Http Request Method
     * @var string
     */
    private $requestMethod;

    /**
     * HTTP URI
     * @var string
     */
    private $uri;

    /**
     * HTTP Status Code
     * @var string
     */
    private $statusCode;

    /**
     * HTTP Version
     * @var string
     */
    private $httpVersion;

    /**
     * Http Request Referer
     * @var string
     */
    private $httpReferer;

    /**
     * Http Request User Agent
     * @var string
     */
    private $userAgent;

    /**
     * Http Method Allowed
     * @var unknown
     */
    private static $HTTP_METHOD = array(
        'GET',
        'POST',
        'OPTIONS',
        'HEAD',
        'PUT',
        'DELETE',
    );

    /**
     * Constructor
     * @param mixed $option
     */
    public function __construct($option = array())
    {
        $defaultFormat = "d/M/Y:H:i:s O";
        if( $option instanceof Config) {
            $this->defaultDateFormat = $option->get("DateFormat", $defaultFormat);
        } else {
            if( isset($option['DateFormat']) ) {
                $this->defaultDateFormat = $option['DateFormat'];
            } else {
                $this->defaultDateFormat = $defaultFormat;
            }
        }
    }

    /**
     * set the ip address
     * @param string $ipaddress
     * @return \LogAnalytics\Files\Data\HttpLogData
     */
    public function setIpAddress($ipaddress)
    {
        $this->ipaddress = NULL;
        if( filter_var($ipaddress, FILTER_VALIDATE_IP) ) {
            $this->ipaddress = $ipaddress;
        }

        return $this;

    }

    /**
     * get the ip address
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipaddress;
    }

    /**
     * Set the date time
     * @param string $datetime
     * @return \LogAnalytics\Files\Data\HttpLogData
     */
    public function setDateTime($datetime)
    {
        //$this->datetime = NULL;
        //if( \DateTime::createFromFormat('m/d/Y', $datetime) !== false ) {
        //    $this->datetime = $datetime;
        //}
        $this->datetime = $datetime;
        return $this;
    }

    /**
     * Get the date time
     * @return string
     */
    public function getDateTime()
    {
        return $this->datetime;
    }

    /**
     * Get the unix Timestamp
     * @return number
     */
    public function getTimestamp()
    {
        return strtotime($this->getDateTime());
    }

    /**
     * Set the HTTP Request Method
     * @param string $method
     * @return \LogAnalytics\Files\Data\HttpLogData
     */
    public function setRequestMethod($method)
    {
        $method = strtoupper(trim($method));

        $this->requestMethod = NULL;
        if( in_array($method, self::$HTTP_METHOD) ) {
            $this->requestMethod = $method;
        }
        return $this;
    }

    /**
     * Get the HTTP Request Method
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * Set the URI
     * @param string $uri
     * @return \LogAnalytics\Files\Data\HttpLogData
     */
    public function setUri($uri)
    {
        $this->uri = trim($uri);
        return $this;
    }

    /**
     * Get the URI
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set the Status Code
     * @param string $code
     * @return \LogAnalytics\Files\Data\HttpLogData
     */
    public function setStatusCode($code)
    {
        $this->statusCode = NULL;
        if( preg_match("/^[1-5]{1}[0-9]{2}$/", $code) ) {
            $this->statusCode = $code;
        }

        return $this;
    }

    /**
     * Get the Status Code
     * @return string
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Set Http Version
     * @param string $httpversion
     * @return \LogAnalytics\Files\Data\HttpLogData
     */
    public function setHttpVersion($httpversion)
    {
        $this->httpVersion = NULL;
        if( preg_match("/^HTTP/i", $httpversion) ) {
            $this->httpVersion = strtoupper(trim($httpversion));
        }

        return $this;
    }

    /**
     * Get the Http Version
     * @return string
     */
    public function getHttpVersion()
    {
        return $this->httpVersion;
    }

    /**
     * Set the HTTP Referer
     * @param string $referer
     * @return \LogAnalytics\Files\Data\HttpLogData
     */
    public function setHttpReferer($referer)
    {
        $this->httpReferer = NULL;
        if( filter_var($referer, FILTER_VALIDATE_URL) ) {
            $this->httpReferer = trim($referer);
        }

        return $this;

    }

    /**
     * Get the HTTP Referer
     * @return string
     */
    public function getHttpReferer()
    {
        return $this->httpReferer;
    }

    /**
     * Set the HTTP User Agent
     * @param string $useragent
     * @return \LogAnalytics\Files\Data\HttpLogData
     */
    public function setUserAgent($useragent)
    {
        $this->userAgent = $useragent;
        return $this;
    }

    /**
     * Get the HTTP User Agent
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    public function __toString()
    {
        return sprintf("%s - [%s] %s %s \"%s\" \"%s\" \"%s\"",
            $this->getIpAddress(),
            $this->getDateTime(),
            $this->getRequestMethod(),
            $this->getHttpReferer(),
            $this->getStatusCode(),
            $this->getUri(),
            $this->getUserAgent());
    }
}