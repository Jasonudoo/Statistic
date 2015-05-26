<?php
/**
 * Application Log File, write the message to the application log file
 *
 * @since: April 13, 2015
 * @author: Jason.Weng
 */

namespace Statistic\Service\Resource\Files;

class ServiceLogFile extends AbstractLogFile
{
    /**
     * The file resource handler to write
     * @var resource
     */
    private static $writeHandler;

    /**
     * Contructor
     * @param string $file
     */
    public function __construct($file = NULL)
    {
        if( !empty($file) || !is_null($file) ) {
            $this->setLogFile($file);
        }
    }

    /**
     * read the content of file
     * @return string
     * @see \Statistic\Service\Resource\Files\FileInterface::read()
     */
    public function read()
    {
        try {
            $logFile = $this->getLogFile();
            $this->checkFile();
            $this->open();
            $content = fread($this->getResourceHandle(), filesize($logFile) );
            fclose($this->getResourceHandle());

            return $content;
        } catch (\Exception $e) {
            return FALSE;
        }

    }

    /**
     * Write the content to the file
     * @param string $content
     * @return boolean
     * @see \Statistic\Service\Resource\Files\FileInterface::write()
     */
    public function write($content)
    {
        try {
            $this->checkFile();
            if( !is_resource(self::$writeHandler) ) {
                $this->open(parent::MODE_FILE_WRITE_ONLY_APPENDED);
                self::$writeHandler = $this->getResourceHandle();
            }

            return fwrite(self::$writeHandler, $content);
        } catch (\Exception $e) {
            return FALSE;
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if( is_resource(self::$writeHandler) ) {
            fclose(self::$writeHandler);
        }
    }
}