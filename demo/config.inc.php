<?php
/**
 * Log Analytics Config file
 *
 * @since: April 13, 2015
 * @author: Jason.Weng
 */
return array(
    "DateFormat" => 'd/M/Y:H:i:s O',
    // "LogFormat" => '%a - %O %t %m %U %r "%>s" %I "%{Referer}i" "%{User-Agent}i" "-""%e"',
    "LogFormat" => '%a - %O %t %m %U %r "%>s" %I "%{Referer}i" "%{User-Agent}i" %{Other}i',
    "AppLogFile" => 'log/loganalytics.log',
    "NginxLogFile" => 'data/nginx.log',
    "Filters" => array(
        array(
            "filter_name" => "Filter404",
            "filter_priority" => "1",
            "filter_option" => array(
                "data" => "status_code",
                "callback" => true
            )
        ),
        array(
            "filter_name" => "FilterReferer",
            "filter_priority" => "2",
            "filter_option" => array(
                "data" => "http_referer",
                "callback" => false
            )
        ),
        array(
            "filter_name" => "FilterIp",
            "filter_priority" => "3",
            "filter_option" => array(
                "data" => "ip_address",
                "callback" => "FilterInterval"
            ),
            "Filters" => array(
                "filter_name" => "FilterInterval",
                "filter_priority" => "4",
                "filter_option" => array(
                    "interval" => "30",
                    "data" => "timestamp",
                    "callback" => true
                )
            )
        )
    )
);
