<?php
/**
 * Log Analytics Main File
 *
 * @since: April 13, 2015
 * @author: Jason.Weng
 */

namespace LogAnalytics;

use LogAnalytics\Helpers\Config;
use LogAnalytics\Files\HttpLogFile;
use LogAnalytics\Files\AppLogFile;

date_default_timezone_set("Asia/Shanghai");
error_reporting(E_ALL);

define('DS', DIRECTORY_SEPARATOR);

$ROOT_PATH = dirname(__DIR__);
include $ROOT_PATH . DS . "vendor" . DS . "autoload.php";
$configFile = __DIR__ . DS . "config.inc.php";

//initialize
$config = new Config(include $configFile);
$log = new Log($config);
$httpLog = new HttpLogFile();
$appLog = new AppLogFile();

//initialize the httplogfile instance
$httpLog->setFormat($config->get("LogFormat"))
        ->setLogFile($config->get("NginxLogFile"));

//initialize the applogfile instance
$appLog->setLogFile($config->get("AppLogFile"));

//initialize the log instance
$log->setAppLogInstance($appLog)
    ->setHttpLogInstance($httpLog)
    ->setFilters($config->get("Filters")->toArray())
    ->analytics();