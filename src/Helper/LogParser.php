<?php
/**
 * Log Parser Class
 *
 * @since: April 14, 2015
 * @author: Jason.Weng
 */

namespace LogAnalytics\Helpers;

use LogAnalytics\Exception\FormatException;

class LogParser
{
    /**
     * short regular expression map
     * @var Array
     */
    private $patterns = array(
        '%%'  => '(?P<percent>\%)',
        '%a'  => '(?P<remoteIp>[\dA-Za-z\:\.]{3,39})',
        '%A'  => '(?P<localIp>[\dA-Za-z\:\.]{3,39})',
        '%h'  => '(?P<host>[a-zA-Z0-9\-\._:]+)',
        '%l'  => '(?P<logname>(?:-|[\w-]+))',
        '%m'  => '(?P<requestMethod>OPTIONS|GET|HEAD|POST|PUT|DELETE|TRACE|CONNECT)',
        '%p'  => '(?P<port>\d+)',
        '%r'  => '(?P<request>(HTTP/1.(?:0|1))|-|)',
        '%t'  => '\[(?P<time>\d{2}/(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)/\d{4}:\d{2}:\d{2}:\d{2} (?:-|\+)\d{4})\]',
        '%u'  => '(?P<user>(?:-|[\w-]+))',
        '%U'  => '(?P<url>.+?)',
        '%R'  => '(?P<referer>.+?)',
        '%e'  => '(?P<rate>\d+\.\d+)',
        '%v'  => '(?P<serverName>([a-zA-Z0-9]+)([a-z0-9.-]*))',
        '%V'  => '(?P<canonicalServerName>([a-zA-Z0-9]+)([a-z0-9.-]*))',
        '%>s' => '(?P<status>\d{3}|-)',
        '%b'  => '(?P<responseBytes>(\d+|-))',
        '%O'  => '(?P<sentBytes>[0-9]+)',
        '%I'  => '(?P<receivedBytes>[0-9]+)',
        '\%\{(?P<name>[a-zA-Z]+)(?P<name2>[-]?)(?P<name3>[a-zA-Z]+)\}i' => '(?P<Name\\1\\3>.*?)',
    );

    /**
     * Short regular expression and name map
     * @var Array
     */
    private $patternsNameMap = array(
        '%%'  => 'percent',
        '%a'  => 'remoteIp',
        '%A'  => 'localIp',
        '%h'  => 'host',
        '%l'  => 'logname',
        '%m'  => 'requestMethod',
        '%p'  => 'port',
        '%r'  => 'request',
        '%t'  => 'time',
        '%u'  => 'user',
        '%U'  => 'url',
        '%R'  => 'referer',
        '%e'  => 'rate',
        '%v'  => 'serverName',
        '%V'  => 'canonicalServerName',
        '%>s' => 'status',
        '%b'  => 'responseBytes',
        '%O'  => 'sentBytes',
        '%I'  => 'receivedBytes',
    );

    /**
     * The regular expression for log
     * @var string
     */
    private $pcreFormat;

    /**
     * Constructor
     * @param string $format
     */
    public function __construct($format = '%h %l %u %t "%r" %>s %b')
    {
        $this->setFormat($format);
    }

    /**
     * Set the log format
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->pcreFormat = "#^{$format}$#";
        foreach ($this->patterns as $pattern => $replace) {
            $this->pcreFormat = preg_replace("/{$pattern}/", $replace, $this->pcreFormat);
        }
    }

    /**
     * Get the log format
     * @return string
     */
    public function getFormat()
    {
        return (string) $this->pcreFormat;
    }

    /**
     * Parse the log according to log format regular expression
     * @param string $line
     * @throws FormatException
     * @return void|\stdClass
     */
    public function parse($line)
    {
        if( empty($line) ) return;

        $matches = array();

        if (!preg_match($this->pcreFormat, $line, $matches)) {
            throw new FormatException($line);
        }
        $entry = new \stdClass();
        foreach (array_filter(array_keys($matches), 'is_string') as $key) {
            if ('time' === $key && true !== $stamp = strtotime($matches[$key])) {
                $entry->timestamp = $stamp;
            }
            $entry->{$key} = $matches[$key];
        }
        unset($matches);

        return $entry;
    }
}
