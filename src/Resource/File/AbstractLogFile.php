<?php
/**
 * Abstract class of Log File
 *
 * @since: April 13, 2015
 * @author: Jason.Weng
 */

namespace Statistic\Service\Resource\Files;

use Statistic\Service\Resource\Files\FileInterface;
use Statistic\Service\Exception\RuntimeException;
use Statistic\Service\Helpers\ErrorCode;

abstract class AbstractLogFile implements FileInterface
{
    const MODE_FILE_READ_ONLY = "r";
    const MODE_FILE_WRITE_ONLY_APPENDED = "a";

    /**
     * The File Resource Handler
     * @var resource
     */
    private $resourceHandle;

    /**
     * Log File including the full path
     * @var string
     */
    private $logFile;

    /**
     * Allowed File Read/Write Mode
     * @var array
     */
    private static $MODE_ALLOWED = array(
        self::MODE_FILE_READ_ONLY,
        self::MODE_FILE_WRITE_ONLY_APPENDED,
    );

    /**
     * Set the Log File
     * @param string $file
     * @return \Statistic\Service\Resource\Files\AbstractLogFile
     */
    public function setLogFile($file)
    {
        $this->logFile = NULL;
        if( is_file($file) ) {
            $this->logFile = $file;
        }

        return $this;
    }

    /**
     * Get the Log File;
     * @return Ambigous <NULL, string>
     */
    public function getLogFile()
    {
        return $this->logFile;
    }

    /**
     * Open the log file
     * @param string $mode
     * @return boolean
     * @see \Statistic\Service\Resource\File\FileInterface::open()
     */
    public function open($mode = self::MODE_FILE_READ_ONLY)
    {
        if( in_array($mode, self::$MODE_ALLOWED) === FALSE ) {
            return FALSE;
        }

        $fhandle = fopen( $this->getLogFile(), $mode);
        if( $fhandle ) {
            $this->setResourceHandle($fhandle);
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Set the Resource Handler
     * @param resource $resource
     * @return \Statistic\Service\Resource\Files\AbstractLogFile
     */
    protected function setResourceHandle($resource)
    {
        $this->resourceHandle = NULL;
        if( is_resource($resource) ) {
            $this->resourceHandle = $resource;
        }

        return $this;
    }

    /**
     * Return the resource handler
     * @return Ambigous <NULL, resource>
     */
    public function getResourceHandle()
    {
        return $this->resourceHandle;
    }

    /**
     * check the file exists or not
     * @throws RuntimeException
     * @return boolean
     */
    protected function checkFile()
    {
        $logFile = $this->getLogFile();
        if( is_null($logFile) || !is_file($logFile) ) {
            $code = ErrorCode::ERROR_RUNTIME_LOGFILE_EMPTY;
            $message = ErrorCode::getMessage($code);
            throw new RuntimeException($message, $code);
        }

        return TRUE;
    }
}