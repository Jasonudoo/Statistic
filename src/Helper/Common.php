<?php
/**
 * The common function
 *
 * @since: May 22, 2015
 * @author: Jason.Weng
 */
namespace Statistic\Service\Helpers;

class Common
{
    /**
     * Singleton pattern implementation makes "new" unavailable
     */
    private function __construct()
    {}

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    private function __clone()
    {}

    private function __destruct()
    {}

    public static function getIpAddress()
    {
        $var = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        foreach ($var as $key) {
            if (array_key_exists($key, $_SERVER)) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if ((bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    public static function serializeObject($data)
    {
		try {
			$serialized = json_encode($data);
		} catch(\Exception $e) {
		    //necessary because $object may have binary data that json encode can't understand
			$serialized = serialize($data);
		}
		return $serialized;
    }
}