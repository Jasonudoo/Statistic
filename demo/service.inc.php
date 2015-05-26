<?php
/**
 * Service Config File
 * It is to define the Service Action
 */

return array(
    "Services" => array(
        "ImageCounter" => array(
            "name" => "ImageCounter",
            "active" => "on",
            "options" => array(
                "MQLabel" => "Image"
            ),
        ),
        "CategoryCounter" => array(
            "name" => "CategoryCounter",
            "active" => "on",
            "options" => array(
                "MQLabel" => "Category",
            ),
        ),
    )
);