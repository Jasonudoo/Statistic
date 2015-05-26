<?php
/**
 * The entrance of the service
 * To create a service according to the input parameter
 */

namespace Statistic\Service\Demo;

date_default_timezone_set("Asia/Shanghai");
error_reporting(E_ALL);
define('DS', DIRECTORY_SEPARATOR);
$ROOT_PATH = dirname(__DIR__);
include $ROOT_PATH . DS . "vendor" . DS . "autoload.php";

$globalConfig = __DIR__ . DS . "config.inc.php";
$serviceConfig = __DIR__ . DS . "service.inc.php";

if( $argc < 1 ) {
    die("Please specify the service start up parameter. Such as Service.php -N ImageCounter. It will create an image
     counter service");
}







