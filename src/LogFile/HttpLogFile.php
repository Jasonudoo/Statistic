<?php
/**
 * Http Log File, Parse the http log file
 *
 * @since: April 13, 2015
 * @author: Jason.Weng
 */

namespace LogAnalytics\Files;

use LogAnalytics\Helpers\LogParser;
use LogAnalytics\Helpers\ErrorCode;
use LogAnalytics\Exception\InvalidArgumentException;
use LogAnalytics\Files\Data\HttpLogData;

class HttpLogFile extends AbstractLogFile
{
    private $data = array();

    /**
     * Log Parser
     * @var \LogAnalytics\Helpers\LogParser
     */
    private $parser = NULL;

    public function __construct($file = NULL)
    {
        if( !empty($file) || !is_null($file) ) {
            $this->setLogFile($file);
        }
    }

    /**
     * Read the log
     * @see \LogAnalytics\FileInterface::read()
     */
    public function read()
    {
        try {
            //check file has been set or not
            $this->checkFile();
            //check the file parser exists or not
            $this->checkParser();
            //open the file
            $this->open();
            $handle = $this->getResourceHandle();
            //start to read
            while( !feof($handle) ) {
                $buffer = fgets($handle, 4096);
                //parse the log data
                $this->parse($buffer);
            }
            fclose($handle);

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
            return FALSE;
        }

    }

    /**
     * Write the content to the file
     * @see \LogAnalytics\FileInterface::write()
     */
    public function write($content)
    {

    }

    /**
     * Set the Log regular shorten expression to the log parser
     * @param string $format
     * @throws InvalidArgumentException
     * @return \LogAnalytics\Files\HttpLogFile
     */
    public function setFormat($format)
    {
        if( empty($format) || is_null($format) ) {
            $code = ErrorCode::ERROR_RUNTIME_FORMAT_EMPTY;
            $message = ErrorCode::getMessage($code);
            throw new InvalidArgumentException($message, $code);
        }

        if( is_null($this->parser) || !($this->parser instanceof LogParser) ) {
            $this->parser = new LogParser();
        }
        $this->getParser()->setFormat($format);

        return $this;
    }

    /**
     * Get the instance of the Class LogParser
     * @return \LogAnalytics\Helpers\LogParser
     */
    protected function getParser()
    {
        return $this->parser;
    }

    /**
     * Set the log data
     * @param \stdClass $logdata
     */
    protected function setLogData($logdata)
    {
        if( is_null($logdata) ) return;

        $data = new HttpLogData();
        $data->setIpAddress($logdata->remoteIp)
            ->setDateTime($logdata->time)
            ->setRequestMethod($logdata->requestMethod)
            ->setUri($logdata->url)
            ->setHttpVersion($logdata->request)
            ->setStatusCode($logdata->status)
            ->setHttpReferer($logdata->NameReferer)
            ->setUserAgent($logdata->NameUserAgent);

        array_push($this->data, $data);
    }

    /**
     * Get the Log Data
     * @return multitype:
     */
    public function getLogData()
    {
        return $this->data;
    }

    /**
     * Parse the log
     * @param string $data
     */
    private function parse($data)
    {
        $result = $this->getParser()->parse($data);
        $this->setLogData($result);
    }

    /**
     * Check the parser
     * @throws InvalidArgumentException
     */
    private function checkParser()
    {
        if( is_null($this->parser) ) {
            $code = ErrorCode::ERROR_RUNTIME_FORMAT_EMPTY;
            $message = ErrorCode::getMessage($code);
            throw new InvalidArgumentException($message, $code);
        }
    }
}