<?php
/**
 * Service Interface
 *
 * @since: April 13, 2015
 * @author: Jason.Weng
 */

namespace Statistic\Service;

interface ServiceInterface
{
    /**
     * The function to execute the service
     * @param mixed $value
     * @return boolean
     */
    public function execute($value);
}