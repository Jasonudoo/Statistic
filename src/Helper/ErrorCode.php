<?php
/**
 * Error Code and Message
 *
 * @since: April 13, 2015
 * @author: Jason.Weng
 */

namespace Statistic\Service\Helpers;

class ErrorCode
{
    const ERROR_RUNTIME_LOGFILE_EMPTY = 1001;
    const ERROR_RUNTIME_CONFIG_EMPTY = 1002;
    const ERROR_RUNTIME_FORMAT_EMPTY = 1003;
    const ERROR_FILTER_CHAIN_CALLBACK_INVALIDATED = 1004;
    const ERROR_RUNTIME_FILTER_EMPTY = 1005;
    const ERROR_FILTER_IP_PARAM_EMPTY = 1006;
    const ERROR_FILTER_IP_PARAM_MISSING = 1007;
    const ERROR_FILTER_INTERVAL_PARAM_MISSING = 1008;
    const ERROR_RUNTIME_LOGFILE_INSTANCE = 1009;
    const ERROR_LOG_DIRECTORY_NO_EXISTS = 1013;
    const ERROR_LOG_DIRECTORY_IS_NOT_DIR = 1014;
    const ERROR_LOG_DIRECTORY_IS_NOT_WRITABLE = 1015;
    const ERROR_LOG_NOT_SUPPORT_LOG_ACTION = 1016;
    const ERROR_MONGODB_USERNAME_EMPTY = 1018;
    const ERROR_MONGODB_PASSWORD_EMPTY = 1019;
    const ERROR_MONGODB_PORT_ERROR = 1020;
    const ERROR_MONGODB_DBNAME_EMPTY = 1021;
    const ERROR_MONGODB_INVALID_CONNECT_PARAM = 1022;

    public static $ERROR_MESSAGE = array(
        self::ERROR_RUNTIME_LOGFILE_EMPTY => "The log file dose not exist. Please make the path of the log file is correct!",
        self::ERROR_RUNTIME_CONFIG_EMPTY => "The argument is required and the format should be instance of Config!",
        self::ERROR_RUNTIME_FORMAT_EMPTY => "The argument is requrred and the value should not be empty or NULL!",
        self::ERROR_FILTER_CHAIN_CALLBACK_INVALIDATED => "Expected a valid PHP callback; received \"%s\"",
        self::ERROR_RUNTIME_FILTER_EMPTY => "The argument is required and the value should be array",
        self::ERROR_FILTER_IP_PARAM_EMPTY => "The argument is required and the value should be array",
        self::ERROR_FILTER_IP_PARAM_MISSING => "The number of argument should be two as required",
        self::ERROR_FILTER_INTERVAL_PARAM_MISSING => "The number of argument should be two as required",
        self::ERROR_RUNTIME_LOGFILE_INSTANCE => "The instance is expected to be the class FileInterface",
        self::ERROR_MONGODB_USERNAME_EMPTY => "The mongodb user name is empty",
        self::ERROR_MONGODB_PASSWORD_EMPTY => "The mongodb password is empty",
        self::ERROR_MONGODB_PORT_ERROR => "The mongodb port '%s' should be number",
        self::ERROR_MONGODB_DBNAME_EMPTY => "The mongodb name is empty",
        self::ERROR_MONGODB_INVALID_CONNECT_PARAM => "The mongodb connection parameters is not correct",
        self::ERROR_LOG_DIRECTORY_NO_EXISTS => "The log directory '%s' is not exists.",
        self::ERROR_LOG_DIRECTORY_IS_NOT_DIR => "The log directory '%s' is not directory",
        self::ERROR_LOG_DIRECTORY_IS_NOT_WRITABLE => "The log directory '%s' is not writable",
        self::ERROR_LOG_NOT_SUPPORT_LOG_ACTION => "The log action '%s' is not supported",
    );

    public static function getMessage($msgid)
    {
        if( isset(self::$ERROR_MESSAGE[$msgid]) ) {
            return self::$ERROR_MESSAGE[$msgid];
        }
        return FALSE;
    }
}