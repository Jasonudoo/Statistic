<?php
/**
 * Logger Operation Class
 *
 * @since: May 22, 2015
 * @author: Jason.Weng
 */

namespace Statistic\Service\Helpers;

use Statistic\Service\Exception\RuntimeException;
use Statistic\Service\Helpers\ErrorCode;

class Weblog
{
	private static $log_path = NULL;
	private static $log_file_subfix = NULL;
	private static $log_file = NULL;
	private static $log_file_handle = NULL;

	const WEBLOG_REQUEST = 0;
	const WEBLOG_RESPONE = 1;
	const WEBLOG_ERROR = 2;

	private static $log_action = array(
	    self::WEBLOG_REQUEST => "Request",
	    self::WEBLOG_RESPONE => "Response",
	    self::WEBLOG_ERROR => "Error"
	);

    /**
     * Singleton pattern implementation makes "new" unavailable
     */
    private function __construct(){}

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    private function __clone(){}

    private function __destruct()
    {
    	self::close();
    }

    public static function setLogPath($path)
    {
        if( !file_exists($path) ) {
            $code = ErrorCode::ERROR_LOG_DIRECTORY_NO_EXISTS;
            $message = sprintf(ErrorCode::getMessage($code), $path);
            throw new RuntimeException($message, $code);
        }

        if( !is_dir($path) ) {
            $code = ErrorCode::ERROR_LOG_DIRECTORY_IS_NOT_DIR;
            $message = sprintf(ErrorCode::getMessage($code), $path);
            throw new RuntimeException($message, $code);
        }

        if( !is_writeable($path) ) {
            $code = ErrorCode::ERROR_LOG_DIRECTORY_IS_NOT_WRITABLE;
            $message = sprintf(ErrorCode::getMessage($code), $path);
            throw new RuntimeException($message, $code);
        }

        self::$log_path = $path;
    }

    /**
     * Get the log file name
     * @return boolean
     */
	private static function getLogFileName()
	{
		self::$log_file_subfix = date("Ymd");
		self::$log_file = self::$log_path . DS . "service_" . self::$log_file_subfix . ".log";
		return TRUE;
	}

	//TODO : add the try catch throw error logical
	/**
	 * Write the log message to log file
	 * @param integer $error_type
	 * @param string $message
	 * @throws RuntimeException
	 * @return boolean
	 */
	public static function write($error_type = self::WEBLOG_ERROR, $message)
	{
	    if( !isset(self::$log_action[$error_type]) ) {
	        $code = ErrorCode::ERROR_LOG_NOT_SUPPORT_LOG_ACTION;
	        $message = sprintf(ErrorCode::getMessage($code), $error_type);
	        throw new RuntimeException($message, $code);
	    }

	    $error = self::$log_action[$error_type];

		if( self::getLogFileName() )
		{
			self::open();

			$log_message = "[" . $error . "] [".date('Y-m-d H:i:s'). "] [" . Common::getIpAddress() . "] [" .
							$_SERVER['REQUEST_METHOD'] . $_SERVER['REQUEST_URI'] . "] " .
							$message . "\n";

			fwrite(self::$log_file_handle, $log_message);
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Read a file
	 */
	public static function read()
	{
		if( self::getLogFileName() )
		{
			self::open(TRUE);
			//TODO: Write the read code
			//self::_close();
		}
	}

	/**
	 * Open a file to read or write
	 * @param boolean $readOnly
	 * @return resource handler
	 */
	private static function open($readOnly = FALSE)
	{
		$openMode = "a+";
		if( !isset(self::$log_file_handle) )
		{
			if( $readOnly )
			{
				$openMode = "r";
			}
			self::$log_file_handle = fopen(self::$log_file, $openMode);
		}
		return self::$log_file_handle;
	}

	/**
	 * Close all of files which had been opened
	 * @return boolean
	 */
	private static function close()
	{
        if ( is_resource(self::$log_file_handle) ) {
            fclose(self::$log_file_handle);
        }
		return TRUE;
	}
}
