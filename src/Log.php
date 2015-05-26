<?php
/**
 * Log Analytics
 *
 * @since: April 13, 2015
 * @author: Jason.Weng
 */

namespace LogAnalytics;

use LogAnalytics\Helpers\Config;
use LogAnalytics\Exception\InvalidArgumentException;
use LogAnalytics\Helpers\ErrorCode;
use LogAnalytics\Files\AppLogFile;
use LogAnalytics\Files\HttpLogFile;
use LogAnalytics\Files\AbstractLogFile;
use LogAnalytics\Filters\FilterChain;

class Log
{
    /**
     * App Log instance to handle write the application log to file
     * @var \LogAnalytics\Files\AppLogFile
     */
    private $appLog;

    /**
     * Http Log instance to handle the parse the nginx log
     * @var \LogAnalytics\Files\HttpLogFile
     */
    private $nginxLog;

    /**
     * Config instance to render the config array as object
     * @var \LogAnalytics\Helpers\Config
     */
    private $config;

    /**
     * FilterChain instance to hold the filters
     * @var \LogAnalytics\Filters\FilterChain
     */
    private $filterChain;

    /**
     * Constructor
     * @param \LogAnalytics\Helpers\Config $config
     * @throws InvalidArgumentException
     */
    public function __construct($config)
    {
        //create the instance of Config class
        if( !($config instanceof Config) ) {
            $code = ErrorCode::ERROR_RUNTIME_CONFIG_EMPTY;
            $message = ErrorCode::getMessage($code);
            throw new InvalidArgumentException($message, $code);
        }
        $this->config = $config;
    }

    /**
     * Set the instance of the Class AppLog
     * @param FileInterface $obj
     * @return \LogAnalytics\Log
     */
    public function setAppLogInstance(FileInterface $obj)
    {
        if( $this->checkLogInstance($obj)) {
            $this->appLog = $obj;
        }
        return $this;
    }

    /**
     * Set the instance of the Class HttpLogFile
     * @param FileInterface $obj
     * @return \LogAnalytics\Log
     */
    public function setHttpLogInstance(FileInterface $obj)
    {
        if( $this->checkLogInstance($obj) ) {
            $this->nginxLog = $obj;
        }
        return $this;
    }

    /**
     * Get the instance of the Class AppLogFile
     * @return \LogAnalytics\Files\AppLogFile
     */
    public function getAppLogInstance()
    {
        return $this->appLog;
    }

    /**
     * Get the instance of the Class HttpLogFile
     * @return \LogAnalytics\Files\HttpLogFile
     */
    public function getHttpLogInstance()
    {
        return $this->nginxLog;
    }

    /**
     * Check the Log Instance
     * @param mixed $obj
     * @throws InvalidArgumentException
     * @return boolean
     */
    private function checkLogInstance($obj)
    {
        if( !($obj instanceof FileInterface) ){
            $code = ErrorCode::ERROR_RUNTIME_LOGFILE_INSTANCE;
            $message = ErrorCode::getMessage($code);
            throw new InvalidArgumentException($message, $code);
        }

        return TRUE;
    }


    /**
     * Set the Filters to FilterChain
     * @param array $filters
     * @throws InvalidArgumentException
     * @return boolean|\LogAnalytics\Log
     */
    public function setFilters($filters)
    {
        if( FALSE === is_array($filters) ) {
            $code = ErrorCode::ERROR_RUNTIME_FILTER_EMPTY;
            $message = ErrorCode::getMessage($code);
            throw new InvalidArgumentException($message, $code);
        }

        if( is_null($this->filterChain) ) {
            $this->filterChain = new FilterChain();
        }

        try {
            for($i = 0; $i < sizeof($filters); $i++) {
                $filter = $filters[$i];
                if( !isset($filter['filter_name']) ) continue;

                $filterName = $filter['filter_name'];
                $param = isset($filter['filter_data']) ? $filter['filter_data'] : NULL;
                $priority = isset($filter['filter_priority']) ? (int)$filter['filter_priority'] : FilterChain::DEFAULT_PRIORITY;
                $option = isset($filter['filter_option']) ? $filter['filter_option'] : NULL;

                $this->getFilters()->attachByName($filterName, $param, $option, $priority);
            }

        } catch (\Exception $e) {
            echo $e->getMessage();
            return FALSE;
        }
        return $this;
    }

    public function getFilters()
    {
        return $this->filterChain;
    }

    public function analytics()
    {
        $this->getHttpLogInstance()->read();
        $data = $this->getHttpLogInstance()->getLogData();
        $this->getFilters()->filter($data);
    }
}